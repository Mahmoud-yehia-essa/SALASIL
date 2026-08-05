<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShipmentDriverInvitation;
use App\Models\Shipment;
use App\Models\User;
use App\Models\DriverTruck;
use App\Notifications\DriverInvitationResponded;
use Illuminate\Support\Facades\Notification;

class ShipmentInvitationController extends Controller
{
    /**
     * Display all driver invitations listing with stats & filters.
     */
    public function AllInvitations(Request $request)
    {
        $query = ShipmentDriverInvitation::with([
            'shipment.pickupCity',
            'shipment.dropoffCity',
            'shipment.customer',
            'shipment.truckType',
            'driver.driverProfile',
            'driverTruck.truckType',
            'driverTruck.truckSubType',
            'driverTruck.truckBrand',
            'driverTruck.truckModel'
        ])->latest();

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by Channel
        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        // Filter by Driver
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('driver', function($dq) use ($search) {
                      $dq->where('fname', 'like', "%{$search}%")
                         ->orWhere('lname', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('shipment', function($sq) use ($search) {
                      $sq->where('id', 'like', "%{$search}%")
                         ->orWhere('shipment_name', 'like', "%{$search}%")
                         ->orWhere('pickup_address', 'like', "%{$search}%")
                         ->orWhere('dropoff_address', 'like', "%{$search}%");
                  });
            });
        }

        $invitations = $query->get();

        // Statistics
        $stats = [
            'total'             => ShipmentDriverInvitation::count(),
            'pending'           => ShipmentDriverInvitation::where('status', 'pending')->count(),
            'accepted'          => ShipmentDriverInvitation::where('status', 'accepted')->count(),
            'rejected_canceled' => ShipmentDriverInvitation::whereIn('status', ['rejected', 'canceled'])->count(),
        ];

        $drivers = User::where('role', 'driver')->orderBy('fname', 'asc')->get(['id', 'fname', 'lname', 'phone']);

        return view('admin.backend.shipment_invitation.all_invitations', compact('invitations', 'stats', 'drivers'));
    }

    /**
     * Show form to create a new driver invitation.
     */
    public function AddInvitation()
    {
        $drivers   = User::where('role', 'driver')->orderBy('fname', 'asc')->get();
        $shipments = Shipment::with(['pickupCity', 'dropoffCity', 'customer'])->latest()->get();

        return view('admin.backend.shipment_invitation.add_invitation', compact('drivers', 'shipments'));
    }

    /**
     * Store new driver invitation in database.
     */
    public function StoreInvitation(Request $request)
    {
        $request->validate([
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

        $invitation = ShipmentDriverInvitation::create([
            'driver_id'       => $request->driver_id,
            'driver_truck_id' => $request->driver_truck_id ?: null,
            'shipment_id'     => $request->shipment_id,
            'offered_price'   => $request->offered_price,
            'channel'         => $request->channel,
            'status'          => 'pending',
        ]);

        $notification = [
            'message'    => 'Invitation #' . $invitation->id . ' Sent Successfully to Driver!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.shipment.invitations')->with($notification);
    }

    /**
     * Show form to edit an existing driver invitation.
     */
    public function EditInvitation($id)
    {
        $invitation = ShipmentDriverInvitation::with(['driver', 'driverTruck.truckType', 'shipment'])->findOrFail($id);
        $drivers    = User::where('role', 'driver')->orderBy('fname', 'asc')->get();
        $shipments  = Shipment::with(['pickupCity', 'dropoffCity', 'customer'])->latest()->get();

        return view('admin.backend.shipment_invitation.edit_invitation', compact('invitation', 'drivers', 'shipments'));
    }

    /**
     * Update invitation record in database.
     */
    public function UpdateInvitation(Request $request)
    {
        $id = $request->id;
        $invitation = ShipmentDriverInvitation::findOrFail($id);

        $request->validate([
            'driver_id'       => 'required|exists:users,id',
            'driver_truck_id' => 'nullable|exists:driver_trucks,id',
            'shipment_id'     => 'required|exists:shipments,id',
            'offered_price'   => 'required|numeric|min:0',
            'channel'         => 'required|in:in_app,whatsapp,sms',
            'status'          => 'required|in:pending,accepted,rejected,canceled',
        ]);

        $invitation->update([
            'driver_id'        => $request->driver_id,
            'driver_truck_id'  => $request->driver_truck_id ?: null,
            'shipment_id'      => $request->shipment_id,
            'offered_price'    => $request->offered_price,
            'channel'          => $request->channel,
            'status'           => $request->status,
            'rejection_reason' => $request->status === 'rejected' ? $request->rejection_reason : $invitation->rejection_reason,
        ]);

        $notification = [
            'message'    => 'Invitation #' . $invitation->id . ' Updated Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.shipment.invitations')->with($notification);
    }

    /**
     * Delete invitation record from database.
     */
    public function DeleteInvitation($id)
    {
        $invitation = ShipmentDriverInvitation::findOrFail($id);
        $invitation->delete();

        $notification = [
            'message'    => 'Driver Invitation #' . $id . ' Deleted Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * AJAX: Fetch trucks owned by a selected driver from driver_trucks table.
     */
    public function GetDriverTrucksAjax($driver_id)
    {
        $trucks = DriverTruck::where('driver_id', $driver_id)
                             ->with(['truckType', 'truckSubType', 'truckBrand', 'truckModel'])
                             ->latest()
                             ->get();

        return response()->json([
            'status' => 'success',
            'trucks' => $trucks
        ]);
    }

    /**
     * AJAX: Fetch full details for a selected shipment to display on screen with animations.
     */
    public function GetShipmentDataAjax($shipment_id)
    {
        $shipment = Shipment::with([
            'customer.companyProfile',
            'truckType',
            'truckSubType',
            'shipmentType',
            'shipmentNature',
            'pickupCountry',
            'pickupCity',
            'dropoffCountry',
            'dropoffCity'
        ])->find($shipment_id);

        if (!$shipment) {
            return response()->json(['status' => 'error', 'message' => 'Shipment not found.'], 404);
        }

        return response()->json([
            'status'   => 'success',
            'shipment' => [
                'id'                   => $shipment->id,
                'shipment_name'        => $shipment->shipment_name ?? ('Shipment #' . $shipment->id),
                'shipment_description' => $shipment->shipment_description ?? '—',
                'status'               => $shipment->status,
                'payment_status'       => $shipment->payment_status,
                'initial_price'        => $shipment->initial_price ? number_format($shipment->initial_price, 2) : '0.00',
                'is_fragile'           => (bool) $shipment->is_fragile,
                'weight'               => $shipment->weight ? number_format($shipment->weight, 2) : '—',
                'packages_count'       => $shipment->packages_count ?? 1,
                'dimensions'           => ($shipment->length || $shipment->width || $shipment->height) 
                                          ? ($shipment->length ?? '0') . ' × ' . ($shipment->width ?? '0') . ' × ' . ($shipment->height ?? '0') . ' cm' 
                                          : '—',

                'truck_type'     => $shipment->truckType->name_en ?? '—',
                'truck_sub_type' => $shipment->truckSubType->name_en ?? '—',
                'shipment_type'  => $shipment->shipmentType->name_en ?? '—',

                'customer_name'  => $shipment->customer ? ($shipment->customer->fname . ' ' . ($shipment->customer->lname ?? '')) : '—',
                'customer_phone' => $shipment->customer ? (($shipment->customer->country_code ?? '') . ' ' . $shipment->customer->phone) : '—',

                'pickup_city'    => $shipment->pickupCity->name_en ?? '—',
                'pickup_area'    => $shipment->pickup_area ?? '—',
                'pickup_address' => $shipment->pickup_address ?? '—',

                'dropoff_city'    => $shipment->dropoffCity->name_en ?? '—',
                'dropoff_area'    => $shipment->dropoff_area ?? '—',
                'dropoff_address' => $shipment->dropoff_address ?? '—',

                'loading_contact'  => ($shipment->loading_contact_name ?? '—') . ' (' . ($shipment->loading_contact_phone ?? '—') . ')',
                'receiving_contact' => ($shipment->arrival_contact_name ?? '—') . ' (' . ($shipment->arrival_contact_phone ?? '—') . ')',
            ]
        ]);
    }

    /**
     * AJAX: Change status of an invitation.
     */
    public function ChangeStatusAjax(Request $request)
    {
        $request->validate([
            'invitation_id' => 'required|exists:shipment_driver_invitations,id',
            'status'        => 'required|in:pending,accepted,rejected,canceled',
        ]);

        $invitation = ShipmentDriverInvitation::findOrFail($request->invitation_id);
        $invitation->status = $request->status;
        if ($request->filled('rejection_reason')) {
            $invitation->rejection_reason = $request->rejection_reason;
        }
        $invitation->save();

        if (in_array($invitation->status, ['accepted', 'rejected'])) {
            try {
                $admins = User::where('role', 'admin')->get();
                if ($admins->count() > 0) {
                    Notification::send($admins, new DriverInvitationResponded($invitation));
                }
            } catch (\Exception $e) {
                // Log exception silently without breaking AJAX flow
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Invitation status updated to ' . ucfirst($request->status) . '.'
        ]);
    }

    /**
     * Display public magic link page for driver invitation response.
     */
    public function ShowDriverInvitationMagicLink($token)
    {
        $invitation = ShipmentDriverInvitation::where('token', $token)
            ->with([
                'shipment.pickupCountry',
                'shipment.pickupCity',
                'shipment.dropoffCountry',
                'shipment.dropoffCity',
                'shipment.customer',
                'shipment.truckType',
                'driver',
                'driverTruck.truckType',
                'driverTruck.truckBrand',
                'driverTruck.truckModel'
            ])
            ->first();

        if (!$invitation) {
            return view('frontend.invitation.invalid');
        }

        $hsDetails = null;
        if ($invitation->shipment && $invitation->shipment->hs_code) {
            try {
                $hsDetails = app(\App\Services\HsCodeLookupService::class)->lookup($invitation->shipment->hs_code);
            } catch (\Exception $e) {
                $hsDetails = null;
            }
        }

        return view('frontend.invitation.show', compact('invitation', 'hsDetails'));
    }

    /**
     * Handle driver response (Accept or Reject) via magic link.
     */
    public function RespondDriverInvitationMagicLink(Request $request, $token)
    {
        $invitation = ShipmentDriverInvitation::where('token', $token)->firstOrFail();

        $request->validate([
            'action'           => 'required|in:accept,reject',
            'rejection_reason' => 'required_if:action,reject|nullable|string|max:1000',
        ]);

        if ($request->action === 'accept') {
            $invitation->status = 'accepted';
            $invitation->rejection_reason = null;
            $msg = 'Thank you! You have successfully accepted this shipment invitation.';
        } else {
            $invitation->status = 'rejected';
            $invitation->rejection_reason = $request->rejection_reason ?: 'Driver declined invitation.';
            $msg = 'Invitation response recorded. Thank you for your feedback.';
        }

        $invitation->save();

        // Send Laravel Database Notification to Admin Users
        try {
            $admins = User::where('role', 'admin')->get();
            if ($admins->count() > 0) {
                Notification::send($admins, new DriverInvitationResponded($invitation));
            }
        } catch (\Exception $e) {
            // Log notification error silently to ensure response returns smoothly
        }

        return redirect()->back()->with('success_message', $msg);
    }
}
