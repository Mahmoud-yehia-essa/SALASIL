<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\ShipmentTracking;
use App\Models\ShipmentTrackingLog;
use App\Events\ShipmentLocationUpdated;
use App\Events\ShipmentStatusUpdated;
use Illuminate\Http\Request;

class ShipmentTrackingController extends Controller
{
    /**
     * Show the Shipment Selection page for live tracking.
     */
    public function index(Request $request)
    {
        $query = Shipment::with([
            'customer.companyProfile',
            'driver',
            'truckType',
            'truckSubType',
            'pickupCountry',
            'pickupCity',
            'dropoffCountry',
            'dropoffCity',
            'latestTracking',
            'latestTrackingLog',
        ])->latest();

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by Search Query
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('shipment_name', 'like', "%{$search}%")
                  ->orWhere('pickup_address', 'like', "%{$search}%")
                  ->orWhere('dropoff_address', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('fname', 'like', "%{$search}%")
                         ->orWhere('lname', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $shipments = $query->get();

        // Calculate Overview KPI Stats
        $stats = [
            'total'      => Shipment::count(),
            'in_transit' => Shipment::whereIn('status', ['approved', 'in_transit', 'picked_up', 'out_for_delivery'])->count(),
            'delivered'  => Shipment::whereIn('status', ['delivered', 'completed'])->count(),
            'pending'    => Shipment::whereIn('status', ['new', 'under_review', 'pending_approval'])->count(),
        ];

        return view('admin.backend.shipment.track_select', compact('shipments', 'stats'));
    }

    /**
     * Display live tracking map interface for a specific shipment.
     */
    public function show($id)
    {
        $shipment = Shipment::with([
            'customer.companyProfile',
            'driver',
            'truckType',
            'truckSubType',
            'shipmentType',
            'shipmentNature',
            'pickupCountry',
            'pickupCity',
            'dropoffCountry',
            'dropoffCity',
            'trackings',
            'trackingLogs' => function($q) {
                $q->orderBy('created_at', 'asc');
            },
        ])->findOrFail($id);

        // Coordinates resolution with intelligent default fallbacks (Riyadh -> Jeddah default line)
        $pickupLat = $shipment->pickup_lat ?: 24.7136;
        $pickupLng = $shipment->pickup_lng ?: 46.6753;

        $dropoffLat = $shipment->dropoff_lat ?: 21.5433;
        $dropoffLng = $shipment->dropoff_lng ?: 39.1728;

        // Current truck location resolution
        $latestLog = $shipment->trackingLogs->last();
        $latestTracking = $shipment->trackings->first();

        $currentLat = $latestLog->latitude ?? ($latestTracking->lat ?? $pickupLat);
        $currentLng = $latestLog->longitude ?? ($latestTracking->lng ?? $pickupLng);

        return view('admin.backend.shipment.track_map', compact(
            'shipment',
            'pickupLat',
            'pickupLng',
            'dropoffLat',
            'dropoffLng',
            'currentLat',
            'currentLng'
        ));
    }

    /**
     * AJAX/API: Store new live location update & broadcast via Reverb.
     */
    public function updateLocation(Request $request)
    {
        $request->validate([
            'shipment_id'          => 'required|exists:shipments,id',
            'latitude'             => 'required|numeric|between:-90,90',
            'longitude'            => 'required|numeric|between:-180,180',
            'speed'                => 'nullable|numeric|min:0',
            'heading'              => 'nullable|integer|between:0,360',
            'is_stop_point'        => 'nullable|boolean',
            'location_description' => 'nullable|string|max:255',
            'status'               => 'nullable|string|max:255',
        ]);

        $shipmentId  = $request->shipment_id;
        $lat         = (float) $request->latitude;
        $lng         = (float) $request->longitude;
        $speed       = (float) ($request->speed ?? 0);
        $heading     = (int) ($request->heading ?? 0);
        $isStopPoint = (bool) $request->is_stop_point;
        $locationDesc= $request->location_description ?? 'Live GPS Location Update';
        $status      = $request->status;

        // 1. Record log entry in shipment_tracking_logs
        $log = ShipmentTrackingLog::create([
            'shipment_id'   => $shipmentId,
            'latitude'      => $lat,
            'longitude'     => $lng,
            'is_stop_point' => $isStopPoint,
            'speed'         => $speed,
            'heading'       => $heading,
            'created_at'    => now(),
        ]);

        // 2. Record or update current state in shipment_trackings table
        $tracking = ShipmentTracking::create([
            'shipment_id'          => $shipmentId,
            'status'               => $status ?? 'In Transit',
            'location_description' => $locationDesc,
            'lat'                  => $lat,
            'lng'                  => $lng,
        ]);

        // 3. Broadcast real-time event via Reverb
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

        return response()->json([
            'status'   => 'success',
            'message'  => 'Shipment live GPS location recorded and broadcasted successfully!',
            'log'      => $log,
            'tracking' => $tracking,
        ]);
    }

    /**
     * AJAX/API: Update tracking status & broadcast event via Reverb.
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'shipment_id'          => 'required|exists:shipments,id',
            'status'               => 'required|string|max:255',
            'location_description' => 'nullable|string|max:255',
            'lat'                  => 'nullable|numeric',
            'lng'                  => 'nullable|numeric',
        ]);

        $shipmentId = $request->shipment_id;
        $status     = $request->status;
        $desc       = $request->location_description;
        $lat        = $request->lat ? (float) $request->lat : null;
        $lng        = $request->lng ? (float) $request->lng : null;

        // Save tracking record
        $tracking = ShipmentTracking::create([
            'shipment_id'          => $shipmentId,
            'status'               => $status,
            'location_description' => $desc,
            'lat'                  => $lat,
            'lng'                  => $lng,
        ]);

        // Update shipment table status if matching key status
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

        // Broadcast event
        broadcast(new ShipmentStatusUpdated(
            $shipmentId,
            $status,
            $desc,
            $lat,
            $lng
        ))->toOthers();

        return response()->json([
            'status'   => 'success',
            'message'  => 'Shipment tracking status updated successfully!',
            'tracking' => $tracking,
        ]);
    }

    /**
     * AJAX: Fetch all trajectory logs & tracking milestones history.
     */
    public function getLogsAjax($id)
    {
        $shipment = Shipment::findOrFail($id);

        $logs = ShipmentTrackingLog::where('shipment_id', $id)
            ->orderBy('created_at', 'asc')
            ->get(['id', 'latitude', 'longitude', 'speed', 'heading', 'is_stop_point', 'created_at']);

        $trackings = ShipmentTracking::where('shipment_id', $id)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'status', 'location_description', 'lat', 'lng', 'created_at']);

        return response()->json([
            'status'    => 'success',
            'logs'      => $logs,
            'trackings' => $trackings,
        ]);
    }

    /**
     * AJAX/API: Clear tracking logs and reset route trajectory for a shipment.
     */
    public function clearLogs($id)
    {
        $shipment = Shipment::findOrFail($id);

        // Delete all tracking logs and status trackings
        ShipmentTrackingLog::where('shipment_id', $id)->delete();
        ShipmentTracking::where('shipment_id', $id)->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Shipment tracking history logs reset successfully!',
        ]);
    }
}
