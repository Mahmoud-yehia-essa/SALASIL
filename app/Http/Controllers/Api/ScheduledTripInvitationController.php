<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScheduledTripDriverInvitation;
use App\Models\ScheduledTrip;
use App\Models\ScheduledTripOrder;
use App\Models\User;
use App\Notifications\ScheduledTripDriverInvitationResponded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;

class ScheduledTripInvitationController extends Controller
{
    /**
     * List all scheduled trip driver invitations with filters.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = ScheduledTripDriverInvitation::with([
            'scheduledTripOrder.customer:id,fname,lname,phone,email',
            'scheduledTripOrder.pickupCity',
            'scheduledTripOrder.dropoffCity',
            'scheduledTripOrder.truckType',
            'scheduledTrip.route.originCountry',
            'scheduledTrip.route.originCity',
            'scheduledTrip.route.destinationCountry',
            'scheduledTrip.route.destinationCity',
            'scheduledTrip.truckType',
            'scheduledTrip.truckSubType',
            'driver:id,fname,lname,phone,email,client_code,photo',
            'driverTruck.truckType',
            'driverTruck.truckBrand',
            'driverTruck.truckModel'
        ])->latest();

        if ($user && $user->role === 'driver' && !$request->filled('driver_id')) {
            $query->where('driver_id', $user->id);
        } elseif ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('scheduled_trip_order_id')) {
            $query->where('scheduled_trip_order_id', $request->scheduled_trip_order_id);
        }

        if ($request->filled('scheduled_trip_id')) {
            $query->where('scheduled_trip_id', $request->scheduled_trip_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        $invitations = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'status'  => true,
            'message' => 'Scheduled trip invitations retrieved successfully',
            'data'    => $invitations
        ], 200);
    }

    /**
     * Get invitations sent to currently authenticated driver.
     */
    public function myInvitations(Request $request)
    {
        $user = $request->user();

        $query = ScheduledTripDriverInvitation::with([
            'scheduledTripOrder.customer:id,fname,lname,phone,email',
            'scheduledTripOrder.pickupCity',
            'scheduledTripOrder.dropoffCity',
            'scheduledTripOrder.truckType',
            'scheduledTrip.route.originCountry',
            'scheduledTrip.route.originCity',
            'scheduledTrip.route.destinationCountry',
            'scheduledTrip.route.destinationCity',
            'scheduledTrip.truckType',
            'scheduledTrip.truckSubType',
            'driverTruck.truckType',
            'driverTruck.truckBrand',
            'driverTruck.truckModel'
        ])->where('driver_id', $user->id)->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invitations = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'status'  => true,
            'message' => 'My scheduled trip invitations retrieved successfully',
            'data'    => $invitations
        ], 200);
    }

    /**
     * Show single scheduled trip invitation details.
     */
    public function show($id)
    {
        $invitation = ScheduledTripDriverInvitation::with([
            'scheduledTripOrder.customer:id,fname,lname,phone,email',
            'scheduledTripOrder.pickupCity',
            'scheduledTripOrder.pickupCountry',
            'scheduledTripOrder.dropoffCity',
            'scheduledTripOrder.dropoffCountry',
            'scheduledTripOrder.truckType',
            'scheduledTripOrder.truckSubType',
            'scheduledTripOrder.shipmentType',
            'scheduledTripOrder.shipmentNature',
            'scheduledTrip.route.originCountry',
            'scheduledTrip.route.originCity',
            'scheduledTrip.route.destinationCountry',
            'scheduledTrip.route.destinationCity',
            'scheduledTrip.truckType',
            'scheduledTrip.truckSubType',
            'scheduledTrip.shipmentType',
            'scheduledTrip.shipmentNature',
            'driver:id,fname,lname,phone,email,client_code,photo',
            'driverTruck.truckType',
            'driverTruck.truckBrand',
            'driverTruck.truckModel'
        ])->find($id);

        if (!$invitation) {
            return response()->json([
                'status'  => false,
                'message' => 'Scheduled trip driver invitation not found'
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Scheduled trip driver invitation retrieved successfully',
            'data'    => $invitation
        ], 200);
    }

    /**
     * Create and send a new scheduled trip driver invitation.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'driver_id'               => 'required|exists:users,id',
            'driver_truck_id'         => 'nullable|exists:driver_trucks,id',
            'scheduled_trip_order_id' => 'required_without:scheduled_trip_id|nullable|exists:scheduled_trip_orders,id',
            'scheduled_trip_id'       => 'required_without:scheduled_trip_order_id|nullable|exists:scheduled_trips,id',
            'offered_price'           => 'required|numeric|min:0',
            'channel'                 => 'required|in:in_app,whatsapp,sms',
        ], [
            'driver_id.required'         => 'Please select a driver.',
            'offered_price.required'     => 'Please specify the offered price for the driver.',
            'channel.required'           => 'Please choose an invitation channel.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $orderId = $request->scheduled_trip_order_id;
        $tripId  = $request->scheduled_trip_id;

        if ($orderId) {
            $order = ScheduledTripOrder::find($orderId);
            if ($order && !$tripId) {
                $tripId = $order->scheduled_trip_id;
            }
        }

        $invitation = ScheduledTripDriverInvitation::create([
            'driver_id'               => $request->driver_id,
            'driver_truck_id'         => $request->driver_truck_id ?: null,
            'scheduled_trip_order_id' => $orderId ?: null,
            'scheduled_trip_id'       => $tripId,
            'offered_price'           => $request->offered_price,
            'channel'                 => $request->channel,
            'status'                  => 'pending',
        ]);

        $invitation->load([
            'scheduledTripOrder.customer:id,fname,lname,phone',
            'scheduledTrip.route.originCity',
            'scheduledTrip.route.destinationCity',
            'driver:id,fname,lname,phone,email',
            'driverTruck.truckType'
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Invitation sent to driver successfully for scheduled trip order',
            'data'    => $invitation
        ], 201);
    }

    /**
     * Update invitation details.
     */
    public function update(Request $request, $id)
    {
        $invitation = ScheduledTripDriverInvitation::find($id);
        if (!$invitation) {
            return response()->json([
                'status'  => false,
                'message' => 'Scheduled trip driver invitation not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'driver_id'               => 'required|exists:users,id',
            'driver_truck_id'         => 'nullable|exists:driver_trucks,id',
            'scheduled_trip_order_id' => 'nullable|exists:scheduled_trip_orders,id',
            'scheduled_trip_id'       => 'nullable|exists:scheduled_trips,id',
            'offered_price'           => 'required|numeric|min:0',
            'channel'                 => 'required|in:in_app,whatsapp,sms',
            'status'                  => 'required|in:pending,accepted,rejected,canceled',
            'rejection_reason'        => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $orderId = $request->scheduled_trip_order_id ?: $invitation->scheduled_trip_order_id;
        $tripId  = $request->scheduled_trip_id ?: $invitation->scheduled_trip_id;

        if ($orderId) {
            $order = ScheduledTripOrder::find($orderId);
            if ($order && !$tripId) {
                $tripId = $order->scheduled_trip_id;
            }
        }

        $invitation->update([
            'driver_id'               => $request->driver_id,
            'driver_truck_id'         => $request->driver_truck_id ?: null,
            'scheduled_trip_order_id' => $orderId,
            'scheduled_trip_id'       => $tripId,
            'offered_price'           => $request->offered_price,
            'channel'                 => $request->channel,
            'status'                  => $request->status,
            'rejection_reason'        => $request->status === 'rejected' ? $request->rejection_reason : $invitation->rejection_reason,
        ]);

        if ($request->status === 'accepted') {
            if ($orderId) {
                $order = ScheduledTripOrder::find($orderId);
                if ($order && !$order->driver_id) {
                    $order->driver_id = $request->driver_id;
                    $order->save();
                }
            }
            if ($tripId) {
                $trip = ScheduledTrip::find($tripId);
                if ($trip && !$trip->driver_id) {
                    $trip->driver_id = $request->driver_id;
                    $trip->save();
                }
            }
        }

        return response()->json([
            'status'  => true,
            'message' => 'Scheduled trip invitation updated successfully',
            'data'    => $invitation
        ], 200);
    }

    /**
     * Respond to invitation (Driver Accepts or Declines).
     */
    public function respond(Request $request, $id)
    {
        $invitation = ScheduledTripDriverInvitation::with(['scheduledTrip', 'scheduledTripOrder'])->find($id);
        if (!$invitation) {
            return response()->json([
                'status'  => false,
                'message' => 'Scheduled trip driver invitation not found'
            ], 404);
        }

        $user = $request->user();
        if ($user && $user->role === 'driver' && $invitation->driver_id !== $user->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'action'           => 'required|in:accept,reject',
            'rejection_reason' => 'required_if:action,reject|nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        if ($request->action === 'accept') {
            $invitation->status = 'accepted';
            $invitation->rejection_reason = null;

            // Assign driver to order if unassigned
            if ($invitation->scheduledTripOrder && !$invitation->scheduledTripOrder->driver_id) {
                $invitation->scheduledTripOrder->driver_id = $invitation->driver_id;
                $invitation->scheduledTripOrder->save();
            }

            // Assign driver to scheduled trip if unassigned
            if ($invitation->scheduledTrip && !$invitation->scheduledTrip->driver_id) {
                $invitation->scheduledTrip->driver_id = $invitation->driver_id;
                $invitation->scheduledTrip->save();
            }
        } else {
            $invitation->status = 'rejected';
            $invitation->rejection_reason = $request->rejection_reason ?: 'Driver declined invitation.';
        }

        $invitation->save();

        // Notify admins
        try {
            $admins = User::where('role', 'admin')->get();
            if ($admins->count() > 0) {
                Notification::send($admins, new ScheduledTripDriverInvitationResponded($invitation));
            }
        } catch (\Exception $e) {
            // Silently log
        }

        return response()->json([
            'status'  => true,
            'message' => 'Scheduled trip invitation response recorded successfully',
            'data'    => $invitation
        ], 200);
    }

    /**
     * Delete invitation.
     */
    public function destroy($id)
    {
        $invitation = ScheduledTripDriverInvitation::find($id);
        if (!$invitation) {
            return response()->json([
                'status'  => false,
                'message' => 'Scheduled trip driver invitation not found'
            ], 404);
        }

        $invitation->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Scheduled trip driver invitation deleted successfully'
        ], 200);
    }
}
