<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\ShipmentTracking;
use App\Models\ShipmentTrackingLog;
use App\Events\ShipmentLocationUpdated;
use App\Events\ShipmentStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ShipmentTrackingController extends Controller
{
    /**
     * List all shipments for live tracking overview.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Shipment::with([
            'customer:id,fname,lname,phone',
            'driver:id,fname,lname,phone',
            'truckType',
            'pickupCity',
            'dropoffCity',
            'latestTracking',
            'latestTrackingLog'
        ])->latest();

        if ($user && in_array($user->role, ['individual_customer', 'company_customer'])) {
            $query->where('customer_id', $user->id);
        } elseif ($user && $user->role === 'driver') {
            $query->where('driver_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('shipment_name', 'like', "%{$search}%")
                  ->orWhere('pickup_address', 'like', "%{$search}%")
                  ->orWhere('dropoff_address', 'like', "%{$search}%");
            });
        }

        $shipments = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'status'  => true,
            'message' => 'Tracking shipments retrieved successfully',
            'data'    => $shipments
        ], 200);
    }

    /**
     * Get live tracking details for a specific shipment.
     */
    public function show($id)
    {
        $shipment = Shipment::with([
            'customer:id,fname,lname,phone,email,photo',
            'driver:id,fname,lname,phone,photo',
            'truckType',
            'truckSubType',
            'pickupCountry',
            'pickupCity',
            'dropoffCountry',
            'dropoffCity',
            'trackings',
            'trackingLogs' => function ($q) {
                $q->orderBy('created_at', 'asc');
            },
        ])->find($id);

        if (!$shipment) {
            return response()->json([
                'status'  => false,
                'message' => 'Shipment not found'
            ], 404);
        }

        $pickupLat  = (float) ($shipment->pickup_lat ?: 24.7136);
        $pickupLng  = (float) ($shipment->pickup_lng ?: 46.6753);
        $dropoffLat = (float) ($shipment->dropoff_lat ?: 21.5433);
        $dropoffLng = (float) ($shipment->dropoff_lng ?: 39.1728);

        $latestLog = $shipment->trackingLogs->last();
        $latestTracking = $shipment->trackings->first();

        $currentLat = $latestLog ? (float)$latestLog->latitude : ($latestTracking && $latestTracking->lat ? (float)$latestTracking->lat : $pickupLat);
        $currentLng = $latestLog ? (float)$latestLog->longitude : ($latestTracking && $latestTracking->lng ? (float)$latestTracking->lng : $pickupLng);

        return response()->json([
            'status'  => true,
            'message' => 'Shipment tracking data retrieved successfully',
            'data'    => [
                'shipment'        => $shipment,
                'current_location'=> [
                    'lat'                  => $currentLat,
                    'lng'                  => $currentLng,
                    'speed'                => $latestLog ? (float)$latestLog->speed : 0,
                    'heading'              => $latestLog ? (int)$latestLog->heading : 0,
                    'location_description' => $latestTracking->location_description ?? 'Live GPS Location',
                    'status'               => $shipment->status,
                    'last_updated'         => $latestLog ? $latestLog->created_at->toIso8601String() : now()->toIso8601String(),
                ],
                'pickup_location' => [
                    'lat'     => $pickupLat,
                    'lng'     => $pickupLng,
                    'address' => $shipment->pickup_address,
                    'city'    => $shipment->pickupCity->name_en ?? null,
                ],
                'dropoff_location'=> [
                    'lat'     => $dropoffLat,
                    'lng'     => $dropoffLng,
                    'address' => $shipment->dropoff_address,
                    'city'    => $shipment->dropoffCity->name_en ?? null,
                ],
                'milestones'      => $shipment->trackings,
                'trajectory_logs' => $shipment->trackingLogs,
            ]
        ], 200);
    }

    /**
     * Store live GPS coordinate and broadcast via Reverb.
     */
    public function updateLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shipment_id'          => 'required|exists:shipments,id',
            'latitude'             => 'required|numeric|between:-90,90',
            'longitude'            => 'required|numeric|between:-180,180',
            'speed'                => 'nullable|numeric|min:0',
            'heading'              => 'nullable|integer|between:0,360',
            'is_stop_point'        => 'nullable|boolean',
            'location_description' => 'nullable|string|max:255',
            'status'               => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $shipmentId   = $request->shipment_id;
        $lat          = (float) $request->latitude;
        $lng          = (float) $request->longitude;
        $speed        = (float) ($request->speed ?? 0);
        $heading      = (int) ($request->heading ?? 0);
        $isStopPoint  = (bool) $request->is_stop_point;
        $locationDesc = $request->location_description ?? 'Live GPS Location Update';
        $status       = $request->status;

        // 1. Record log in tracking logs
        $log = ShipmentTrackingLog::create([
            'shipment_id'   => $shipmentId,
            'latitude'      => $lat,
            'longitude'     => $lng,
            'is_stop_point' => $isStopPoint,
            'speed'         => $speed,
            'heading'       => $heading,
            'created_at'    => now(),
        ]);

        // 2. Record or update latest milestone tracking
        $tracking = ShipmentTracking::create([
            'shipment_id'          => $shipmentId,
            'status'               => $status ?? 'In Transit',
            'location_description' => $locationDesc,
            'lat'                  => $lat,
            'lng'                  => $lng,
        ]);

        // 3. Broadcast real-time location event
        try {
            broadcast(new ShipmentLocationUpdated(
                $shipmentId,
                $lat,
                $lng,
                $speed,
                $heading,
                $isStopPoint,
                $locationDesc,
                $status
            ))->toOthers();
        } catch (\Throwable $e) {
            Log::warning('Shipment location broadcast skipped: ' . $e->getMessage());
        }

        return response()->json([
            'status'   => true,
            'message'  => 'Shipment live GPS location recorded and broadcasted successfully',
            'log'      => $log,
            'tracking' => $tracking,
        ], 200);
    }

    /**
     * Update tracking milestone status and broadcast event.
     */
    public function updateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shipment_id'          => 'required|exists:shipments,id',
            'status'               => 'required|string|max:255',
            'location_description' => 'nullable|string|max:255',
            'lat'                  => 'nullable|numeric',
            'lng'                  => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $shipmentId = $request->shipment_id;
        $status     = $request->status;
        $desc       = $request->location_description;
        $lat        = $request->lat ? (float) $request->lat : null;
        $lng        = $request->lng ? (float) $request->lng : null;

        $tracking = ShipmentTracking::create([
            'shipment_id'          => $shipmentId,
            'status'               => $status,
            'location_description' => $desc,
            'lat'                  => $lat,
            'lng'                  => $lng,
        ]);

        $shipment = Shipment::find($shipmentId);
        if ($shipment) {
            if (in_array(strtolower($status), ['delivered', 'تم التسليم'])) {
                $shipment->status = 'delivered';
                $shipment->save();
            } elseif (in_array(strtolower($status), ['in_transit', 'في الطريق', 'picked_up'])) {
                $shipment->status = 'approved';
                $shipment->save();
            }
        }

        try {
            broadcast(new ShipmentStatusUpdated(
                $shipmentId,
                $status,
                $desc,
                $lat,
                $lng
            ))->toOthers();
        } catch (\Throwable $e) {
            Log::warning('Shipment status broadcast skipped: ' . $e->getMessage());
        }

        return response()->json([
            'status'   => true,
            'message'  => 'Shipment tracking status updated successfully',
            'tracking' => $tracking,
        ], 200);
    }

    /**
     * Fetch trajectory logs and status milestones.
     */
    public function getLogs($id)
    {
        $shipment = Shipment::find($id);
        if (!$shipment) {
            return response()->json([
                'status'  => false,
                'message' => 'Shipment not found'
            ], 404);
        }

        $logs = ShipmentTrackingLog::where('shipment_id', $id)
            ->orderBy('created_at', 'asc')
            ->get(['id', 'latitude', 'longitude', 'speed', 'heading', 'is_stop_point', 'created_at']);

        $trackings = ShipmentTracking::where('shipment_id', $id)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'status', 'location_description', 'lat', 'lng', 'created_at']);

        return response()->json([
            'status'    => true,
            'message'   => 'Tracking logs retrieved successfully',
            'data'      => [
                'logs'      => $logs,
                'trackings' => $trackings,
            ]
        ], 200);
    }

    /**
     * Clear tracking logs and reset route trajectory.
     */
    public function clearLogs($id)
    {
        $shipment = Shipment::find($id);
        if (!$shipment) {
            return response()->json([
                'status'  => false,
                'message' => 'Shipment not found'
            ], 404);
        }

        ShipmentTrackingLog::where('shipment_id', $id)->delete();
        ShipmentTracking::where('shipment_id', $id)->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Shipment tracking history logs reset successfully',
        ], 200);
    }
}
