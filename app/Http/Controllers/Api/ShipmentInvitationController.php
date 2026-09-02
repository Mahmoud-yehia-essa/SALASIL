<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShipmentDriverInvitation;
use App\Models\Shipment;
use App\Models\User;
use App\Notifications\DriverInvitationResponded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;

class ShipmentInvitationController extends Controller
{
    /**
     * List all driver invitations with filters.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = ShipmentDriverInvitation::with([
            'shipment.pickupCity',
            'shipment.dropoffCity',
            'shipment.customer',
            'shipment.truckType',
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

        if ($request->filled('shipment_id')) {
            $query->where('shipment_id', $request->shipment_id);
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
            'message' => 'Driver invitations retrieved successfully',
            'data'    => $invitations
        ], 200);
    }

    /**
     * Get invitations sent to currently authenticated driver.
     */
    public function myInvitations(Request $request)
    {
        $user = $request->user();

        $query = ShipmentDriverInvitation::with([
            'shipment.pickupCountry',
            'shipment.pickupCity',
            'shipment.dropoffCountry',
            'shipment.dropoffCity',
            'shipment.customer:id,fname,lname,phone,email',
            'shipment.truckType',
            'shipment.truckSubType',
            'shipment.shipmentType',
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
            'message' => 'My invitations retrieved successfully',
            'data'    => $invitations
        ], 200);
    }

    /**
     * Show single invitation details.
     */
    public function show($id)
    {
        $invitation = ShipmentDriverInvitation::with([
            'shipment.pickupCountry',
            'shipment.pickupCity',
            'shipment.dropoffCountry',
            'shipment.dropoffCity',
            'shipment.customer:id,fname,lname,phone,email',
            'shipment.truckType',
            'shipment.truckSubType',
            'shipment.shipmentType',
            'driver:id,fname,lname,phone,email,client_code,photo',
            'driverTruck.truckType',
            'driverTruck.truckBrand',
            'driverTruck.truckModel'
        ])->find($id);

        if (!$invitation) {
            return response()->json([
                'status'  => false,
                'message' => 'Driver invitation not found'
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Driver invitation retrieved successfully',
            'data'    => $invitation
        ], 200);
    }

    /**
     * Create and send a new driver invitation.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'driver_id'       => 'required|exists:users,id',
            'driver_truck_id' => 'nullable|exists:driver_trucks,id',
            'shipment_id'     => 'required|exists:shipments,id',
            'offered_price'   => 'required|numeric|min:0',
            'channel'         => 'required|in:in_app,whatsapp,sms',
        ], [
            'driver_id.required'     => 'Please select a driver.',
            'shipment_id.required'   => 'Please select a shipment order.',
            'offered_price.required' => 'Please specify the offered price for the driver.',
            'channel.required'       => 'Please choose an invitation channel.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $invitation = ShipmentDriverInvitation::create([
            'driver_id'       => $request->driver_id,
            'driver_truck_id' => $request->driver_truck_id ?: null,
            'shipment_id'     => $request->shipment_id,
            'offered_price'   => $request->offered_price,
            'channel'         => $request->channel,
            'status'          => 'pending',
        ]);

        $invitation->load([
            'shipment',
            'driver:id,fname,lname,phone,email',
            'driverTruck.truckType'
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Invitation sent to driver successfully',
            'data'    => $invitation
        ], 201);
    }

    /**
     * Update invitation details.
     */
    public function update(Request $request, $id)
    {
        $invitation = ShipmentDriverInvitation::find($id);
        if (!$invitation) {
            return response()->json([
                'status'  => false,
                'message' => 'Driver invitation not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'driver_id'        => 'required|exists:users,id',
            'driver_truck_id'  => 'nullable|exists:driver_trucks,id',
            'shipment_id'      => 'required|exists:shipments,id',
            'offered_price'    => 'required|numeric|min:0',
            'channel'          => 'required|in:in_app,whatsapp,sms',
            'status'           => 'required|in:pending,accepted,rejected,canceled',
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $invitation->update([
            'driver_id'        => $request->driver_id,
            'driver_truck_id'  => $request->driver_truck_id ?: null,
            'shipment_id'      => $request->shipment_id,
            'offered_price'    => $request->offered_price,
            'channel'          => $request->channel,
            'status'           => $request->status,
            'rejection_reason' => $request->status === 'rejected' ? $request->rejection_reason : $invitation->rejection_reason,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Invitation updated successfully',
            'data'    => $invitation
        ], 200);
    }

    /**
     * Respond to invitation (Driver Accepts or Declines).
     */
    public function respond(Request $request, $id)
    {
        $invitation = ShipmentDriverInvitation::with('shipment')->find($id);
        if (!$invitation) {
            return response()->json([
                'status'  => false,
                'message' => 'Driver invitation not found'
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

            // Assign driver to shipment and set status to approved
            if ($invitation->shipment) {
                $invitation->shipment->driver_id = $invitation->driver_id;
                $invitation->shipment->status = 'approved';
                $invitation->shipment->save();
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
                Notification::send($admins, new DriverInvitationResponded($invitation));
            }
        } catch (\Exception $e) {
            // Silently log
        }

        return response()->json([
            'status'  => true,
            'message' => 'Invitation response recorded successfully',
            'data'    => $invitation
        ], 200);
    }

    /**
     * Delete invitation.
     */
    public function destroy($id)
    {
        $invitation = ShipmentDriverInvitation::find($id);
        if (!$invitation) {
            return response()->json([
                'status'  => false,
                'message' => 'Driver invitation not found'
            ], 404);
        }

        $invitation->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Driver invitation deleted successfully'
        ], 200);
    }
}
