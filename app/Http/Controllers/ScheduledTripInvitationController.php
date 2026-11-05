<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScheduledTripDriverInvitation;
use App\Models\ScheduledTrip;
use App\Models\ScheduledTripOrder;
use App\Models\User;
use App\Models\DriverTruck;
use App\Notifications\ScheduledTripDriverInvitationResponded;
use Illuminate\Support\Facades\Notification;

class ScheduledTripInvitationController extends Controller
{
    /**
     * Display all scheduled trip driver invitations listing with stats & filters.
     */
    public function AllInvitations(Request $request)
    {
        $query = ScheduledTripDriverInvitation::with([
            'scheduledTripOrder.customer.companyProfile',
            'scheduledTripOrder.pickupCity',
            'scheduledTripOrder.pickupCountry',
            'scheduledTripOrder.dropoffCity',
            'scheduledTripOrder.dropoffCountry',
            'scheduledTripOrder.truckType',
            'scheduledTrip.route.originCountry',
            'scheduledTrip.route.originCity',
            'scheduledTrip.route.destinationCountry',
            'scheduledTrip.route.destinationCity',
            'scheduledTrip.truckType',
            'scheduledTrip.truckSubType',
            'scheduledTrip.driver',
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
                  ->orWhereHas('scheduledTripOrder', function($oq) use ($search) {
                      $oq->where('id', 'like', "%{$search}%")
                         ->orWhere('order_number', 'like', "%{$search}%")
                         ->orWhere('shipment_name', 'like', "%{$search}%")
                         ->orWhereHas('customer', function($cq) use ($search) {
                             $cq->where('fname', 'like', "%{$search}%")
                                ->orWhere('lname', 'like', "%{$search}%");
                         });
                  })
                  ->orWhereHas('scheduledTrip', function($tq) use ($search) {
                      $tq->where('id', 'like', "%{$search}%")
                         ->orWhere('trip_date', 'like', "%{$search}%")
                         ->orWhereHas('route.originCity', function($ocq) use ($search) {
                             $ocq->where('name_en', 'like', "%{$search}%")
                                 ->orWhere('name_ar', 'like', "%{$search}%");
                         })
                         ->orWhereHas('route.destinationCity', function($dcq) use ($search) {
                             $dcq->where('name_en', 'like', "%{$search}%")
                                 ->orWhere('name_ar', 'like', "%{$search}%");
                         });
                  });
            });
        }

        $invitations = $query->get();

        // Statistics
        $stats = [
            'total'             => ScheduledTripDriverInvitation::count(),
            'pending'           => ScheduledTripDriverInvitation::where('status', 'pending')->count(),
            'accepted'          => ScheduledTripDriverInvitation::where('status', 'accepted')->count(),
            'rejected_canceled' => ScheduledTripDriverInvitation::whereIn('status', ['rejected', 'canceled'])->count(),
        ];

        $drivers = User::where('role', 'driver')->orderBy('fname', 'asc')->get(['id', 'fname', 'lname', 'phone']);

        return view('admin.backend.scheduled_trip_invitation.all_invitations', compact('invitations', 'stats', 'drivers'));
    }

    /**
     * Show form to create a new scheduled trip driver invitation for an order requested by a user.
     */
    public function AddInvitation(Request $request)
    {
        $drivers = User::where('role', 'driver')->orderBy('fname', 'asc')->get();

        // Fetch Scheduled Trip Orders placed by customers
        $orders = ScheduledTripOrder::with([
            'scheduledTrip.route.originCountry',
            'scheduledTrip.route.originCity',
            'scheduledTrip.route.destinationCountry',
            'scheduledTrip.route.destinationCity',
            'customer.companyProfile',
            'truckType',
            'truckSubType',
            'shipmentType',
            'shipmentNature',
            'pickupCountry',
            'pickupCity',
            'dropoffCountry',
            'dropoffCity',
        ])->latest()->get();

        $selectedOrderId = $request->order_id ?: ($request->trip_order_id ?: $request->trip_id);

        return view('admin.backend.scheduled_trip_invitation.add_invitation', compact('drivers', 'orders', 'selectedOrderId'));
    }

    /**
     * Store new scheduled trip driver invitation in database.
     */
    public function StoreInvitation(Request $request)
    {
        $request->validate([
            'driver_id'               => 'required|exists:users,id',
            'driver_truck_id'         => 'nullable|exists:driver_trucks,id',
            'scheduled_trip_order_id' => 'required|exists:scheduled_trip_orders,id',
            'offered_price'           => 'required|numeric|min:0',
            'channel'                 => 'required|in:in_app,whatsapp,sms',
        ], [
            'driver_id.required'               => 'Please select a driver.',
            'scheduled_trip_order_id.required' => 'Please select a scheduled trip order.',
            'offered_price.required'           => 'Please specify the offered price for the driver.',
            'channel.required'                 => 'Please choose an invitation channel.',
        ]);

        $order = ScheduledTripOrder::findOrFail($request->scheduled_trip_order_id);

        $invitation = ScheduledTripDriverInvitation::create([
            'driver_id'               => $request->driver_id,
            'driver_truck_id'         => $request->driver_truck_id ?: null,
            'scheduled_trip_order_id' => $order->id,
            'scheduled_trip_id'       => $order->scheduled_trip_id,
            'offered_price'           => $request->offered_price,
            'channel'                 => $request->channel,
            'status'                  => 'pending',
        ]);

        $orderLabel = $order->order_number ?: ('#' . $order->id);
        $notification = [
            'message'    => 'Invitation #' . $invitation->id . ' Sent Successfully to Driver for Order ' . $orderLabel . '!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.scheduled.trip.invitations')->with($notification);
    }

    /**
     * Show form to edit an existing driver invitation.
     */
    public function EditInvitation($id)
    {
        $invitation = ScheduledTripDriverInvitation::with([
            'driver',
            'driverTruck.truckType',
            'scheduledTripOrder.scheduledTrip.route.originCountry',
            'scheduledTripOrder.scheduledTrip.route.originCity',
            'scheduledTripOrder.scheduledTrip.route.destinationCountry',
            'scheduledTripOrder.scheduledTrip.route.destinationCity',
            'scheduledTripOrder.customer',
            'scheduledTripOrder.truckType',
            'scheduledTrip.route.originCountry',
            'scheduledTrip.route.originCity',
            'scheduledTrip.route.destinationCountry',
            'scheduledTrip.route.destinationCity',
            'scheduledTrip.truckType'
        ])->findOrFail($id);

        $drivers = User::where('role', 'driver')->orderBy('fname', 'asc')->get();
        $orders  = ScheduledTripOrder::with([
            'scheduledTrip.route.originCountry',
            'scheduledTrip.route.originCity',
            'scheduledTrip.route.destinationCountry',
            'scheduledTrip.route.destinationCity',
            'customer',
            'truckType',
            'pickupCity',
            'dropoffCity'
        ])->latest()->get();

        return view('admin.backend.scheduled_trip_invitation.edit_invitation', compact('invitation', 'drivers', 'orders'));
    }

    /**
     * Update invitation record in database.
     */
    public function UpdateInvitation(Request $request)
    {
        $id = $request->id;
        $invitation = ScheduledTripDriverInvitation::findOrFail($id);

        $request->validate([
            'driver_id'               => 'required|exists:users,id',
            'driver_truck_id'         => 'nullable|exists:driver_trucks,id',
            'scheduled_trip_order_id' => 'required|exists:scheduled_trip_orders,id',
            'offered_price'           => 'required|numeric|min:0',
            'channel'                 => 'required|in:in_app,whatsapp,sms',
            'status'                  => 'required|in:pending,accepted,rejected,canceled',
        ]);

        $order = ScheduledTripOrder::findOrFail($request->scheduled_trip_order_id);

        $invitation->update([
            'driver_id'               => $request->driver_id,
            'driver_truck_id'         => $request->driver_truck_id ?: null,
            'scheduled_trip_order_id' => $order->id,
            'scheduled_trip_id'       => $order->scheduled_trip_id,
            'offered_price'           => $request->offered_price,
            'channel'                 => $request->channel,
            'status'                  => $request->status,
            'rejection_reason'        => $request->status === 'rejected' ? $request->rejection_reason : $invitation->rejection_reason,
        ]);

        // If status changed to accepted, optionally assign driver to order and scheduled trip if unassigned
        if ($request->status === 'accepted') {
            if (!$order->driver_id) {
                $order->driver_id = $request->driver_id;
                $order->save();
            }
            if ($order->scheduledTrip && !$order->scheduledTrip->driver_id) {
                $order->scheduledTrip->driver_id = $request->driver_id;
                $order->scheduledTrip->save();
            }
        }

        $notification = [
            'message'    => 'Trip Invitation #' . $invitation->id . ' Updated Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.scheduled.trip.invitations')->with($notification);
    }

    /**
     * Delete invitation record from database.
     */
    public function DeleteInvitation($id)
    {
        $invitation = ScheduledTripDriverInvitation::findOrFail($id);
        $invitation->delete();

        $notification = [
            'message'    => 'Scheduled Trip Driver Invitation #' . $id . ' Deleted Successfully!',
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
     * AJAX: Fetch full details for a selected Scheduled Trip Order (orders from scheduled-trip-orders/all).
     */
    public function GetScheduledTripOrderDataAjax($order_id)
    {
        $order = ScheduledTripOrder::with([
            'scheduledTrip.route.originCountry',
            'scheduledTrip.route.originCity',
            'scheduledTrip.route.destinationCountry',
            'scheduledTrip.route.destinationCity',
            'scheduledTrip.truckType',
            'scheduledTrip.truckSubType',
            'customer.companyProfile',
            'truckType',
            'truckSubType',
            'shipmentType',
            'shipmentNature',
            'pickupCountry',
            'pickupCity',
            'dropoffCountry',
            'dropoffCity',
            'driver.driverProfile'
        ])->find($order_id);

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Scheduled trip order not found.'], 404);
        }

        $trip = $order->scheduledTrip;

        // Origin / Pickup
        $originCountry = $order->pickupCountry->name_en ?? ($trip->route->originCountry->name_en ?? '—');
        $originCity    = $order->pickupCity->name_en ?? ($trip->route->originCity->name_en ?? 'Origin');
        $pickupAddress = $order->pickup_address ?: '—';

        // Destination / Dropoff
        $destinationCountry = $order->dropoffCountry->name_en ?? ($trip->route->destinationCountry->name_en ?? '—');
        $destinationCity    = $order->dropoffCity->name_en ?? ($trip->route->destinationCity->name_en ?? 'Destination');
        $dropoffAddress     = $order->dropoff_address ?: '—';

        $customerName    = $order->customer ? ($order->customer->fname . ' ' . ($order->customer->lname ?? '')) : 'Customer';
        $customerPhone   = $order->customer->phone ?? '—';
        $customerCompany = $order->customer->companyProfile->company_name ?? null;

        $truckTypeName    = $order->truckType->name_en ?? ($trip->truckType->name_en ?? '—');
        $truckSubTypeName = $order->truckSubType->name_en ?? ($trip->truckSubType->name_en ?? '—');

        $distanceKm = $trip->route->estimated_distance ?? null;

        return response()->json([
            'status' => 'success',
            'order'  => [
                'id'                  => $order->id,
                'order_number'        => $order->order_number ?: ('#STO-' . $order->id),
                'scheduled_trip_id'   => $order->scheduled_trip_id,
                'customer_name'       => $customerName,
                'customer_phone'      => $customerPhone,
                'customer_company'    => $customerCompany,
                'route_title'         => $originCity . ' ➔ ' . $destinationCity,
                'quote_type'          => ucfirst($trip->route->quote_type ?? 'local'),
                'estimated_distance'  => $distanceKm ? number_format($distanceKm, 1) . ' km' : '—',

                'origin_country'      => $originCountry,
                'origin_city'         => $originCity,
                'pickup_address'      => $pickupAddress,
                'loading_date'        => $order->loading_date ? date('Y-m-d (D)', strtotime($order->loading_date)) : ($trip->trip_date ? date('Y-m-d (D)', strtotime($trip->trip_date)) : 'Flexible Date'),
                'loading_time'        => $order->loading_time ?: ($order->loading_time_slot ?: ($trip->trip_time ? date('h:i A', strtotime($trip->trip_time)) : 'Flexible Time')),
                'loading_contact'     => $order->loading_contact_name ? ($order->loading_contact_name . ($order->loading_contact_phone ? ' (' . $order->loading_contact_phone . ')' : '')) : ($order->loading_contact ?: '—'),

                'destination_country' => $destinationCountry,
                'destination_city'    => $destinationCity,
                'dropoff_address'     => $dropoffAddress,
                'receiving_date'      => $order->receiving_date ? date('Y-m-d (D)', strtotime($order->receiving_date)) : 'Upon Arrival',
                'receiving_time'      => $order->receiving_time ?: ($order->receiving_time_slot ?: 'Flexible Time'),
                'arrival_contact'     => $order->arrival_contact_name ? ($order->arrival_contact_name . ($order->arrival_contact_phone ? ' (' . $order->arrival_contact_phone . ')' : '')) : ($order->arrival_contact ?: '—'),

                'truck_type'          => $truckTypeName,
                'truck_sub_type'      => $truckSubTypeName,
                'number_of_trucks'    => $order->number_of_trucks ?: ($order->booked_capacity ?: 1),
                'special_equipment'   => $order->special_equipment ?: [],

                'shipment_name'       => $order->shipment_name ?: 'General Cargo',
                'shipment_type'       => $order->shipmentType->name_en ?? '—',
                'shipment_nature'     => $order->shipmentNature->name_en ?? '—',
                'weight'              => $order->weight ? number_format($order->weight, 2) . ' Tons/Kg' : '—',
                'packages_count'      => $order->packages_count ?? 1,
                'is_fragile'          => (bool) $order->is_fragile,
                'customs_clearance'   => (bool) $order->customs_clearance,
                'dimensions'          => ($order->length || $order->width || $order->height) ? ($order->length . ' × ' . $order->width . ' × ' . $order->height . ' m') : '—',

                'driver_name'         => $order->driver ? ($order->driver->fname . ' ' . ($order->driver->lname ?? '')) : 'Unassigned',
                'driver_phone'        => $order->driver ? $order->driver->phone : '—',

                'trip_price'          => $order->trip_price ? number_format($order->trip_price, 2) : '0.00',
                'total_price'         => $order->total_price ? number_format($order->total_price, 2) : '0.00',
                'total_price_raw'     => (float) ($order->total_price ?: $order->trip_price ?: 0),
                'booked_capacity'     => $order->booked_capacity ?: 1,
                'status'              => $order->status,
                'payment_status'      => $order->payment_status ?: 'unpaid',
                'notes'               => $order->notes ?: '—',
                'pickup_instructions' => $order->pickup_instructions ?: '—',
                'delivery_instructions'=> $order->delivery_instructions ?: '—',
                'documents_count'     => is_array($order->documents) ? count($order->documents) : 0,
                'created_at'          => $order->created_at ? $order->created_at->format('Y-m-d H:i') : '—',
            ]
        ]);
    }

    /**
     * AJAX: Fetch full details for a scheduled trip (backwards compatible alias).
     */
    public function GetScheduledTripDataAjax($trip_id)
    {
        $trip = ScheduledTrip::with([
            'route.originCountry',
            'route.originCity',
            'route.destinationCountry',
            'route.destinationCity',
            'truckType',
            'truckSubType',
            'shipmentType',
            'shipmentNature',
            'driver.driverProfile'
        ])->find($trip_id);

        if (!$trip) {
            return response()->json(['status' => 'error', 'message' => 'Scheduled trip not found.'], 404);
        }

        $originCountry      = $trip->route->originCountry->name_en ?? '—';
        $originCity         = $trip->route->originCity->name_en ?? 'Origin';
        $destinationCountry = $trip->route->destinationCountry->name_en ?? '—';
        $destinationCity    = $trip->route->destinationCity->name_en ?? 'Destination';

        return response()->json([
            'status' => 'success',
            'trip'   => [
                'id'                  => $trip->id,
                'quote_type'          => ucfirst($trip->route->quote_type ?? 'local'),
                'origin_country'      => $originCountry,
                'origin_city'         => $originCity,
                'destination_country' => $destinationCountry,
                'destination_city'    => $destinationCity,
                'route_title'         => $originCity . ' ➔ ' . $destinationCity,
                'estimated_distance'  => $trip->route->estimated_distance ? number_format($trip->route->estimated_distance, 1) . ' km' : '—',

                'truck_type'        => $trip->truckType->name_en ?? '—',
                'truck_sub_type'    => $trip->truckSubType->name_en ?? '—',
                'shipment_type'     => $trip->shipmentType->name_en ?? '—',
                'shipment_nature'   => $trip->shipmentNature->name_en ?? '—',
                'number_of_trucks'  => $trip->number_of_trucks ?? 1,
                'total_weight_ton'  => $trip->total_weight_ton ? number_format($trip->total_weight_ton, 2) . ' Tons' : '—',

                'driver_name'       => $trip->driver ? ($trip->driver->fname . ' ' . ($trip->driver->lname ?? '')) : 'Unassigned',
                'driver_phone'      => $trip->driver ? $trip->driver->phone : '—',

                'trip_date'         => $trip->trip_date ? date('Y-m-d (D)', strtotime($trip->trip_date)) : 'Flexible / Open Date',
                'trip_time'         => $trip->trip_time ? date('h:i A', strtotime($trip->trip_time)) : 'Flexible Time',
                'price'             => $trip->price ? number_format($trip->price, 2) : '0.00',
                'price_raw'         => $trip->price ?: 0,
                'total_capacity'    => $trip->total_capacity,
                'available_capacity'=> $trip->available_capacity,
                'customs_clearance' => (bool) $trip->customs_clearance,
                'notes'             => $trip->notes ?: '—',
                'required_documents'=> $trip->required_documents ?: [],
                'pricing_additions' => $trip->pricing_additions ?: [],
                'status'            => $trip->status,
                'created_at'        => $trip->created_at ? $trip->created_at->format('Y-m-d H:i') : '—',
            ]
        ]);
    }

    /**
     * AJAX: Change status of a scheduled trip driver invitation.
     */
    public function ChangeStatusAjax(Request $request)
    {
        $request->validate([
            'invitation_id' => 'required|exists:scheduled_trip_driver_invitations,id',
            'status'        => 'required|in:pending,accepted,rejected,canceled',
        ]);

        $invitation = ScheduledTripDriverInvitation::with(['scheduledTrip', 'scheduledTripOrder'])->findOrFail($request->invitation_id);
        $invitation->status = $request->status;
        if ($request->filled('rejection_reason')) {
            $invitation->rejection_reason = $request->rejection_reason;
        }
        $invitation->save();

        // If status changed to accepted, optionally assign driver to order and scheduled trip if unassigned
        if ($request->status === 'accepted') {
            if ($invitation->scheduledTripOrder && !$invitation->scheduledTripOrder->driver_id) {
                $invitation->scheduledTripOrder->driver_id = $invitation->driver_id;
                $invitation->scheduledTripOrder->save();
            }
            if ($invitation->scheduledTrip && !$invitation->scheduledTrip->driver_id) {
                $invitation->scheduledTrip->driver_id = $invitation->driver_id;
                $invitation->scheduledTrip->save();
            }
        }

        if (in_array($invitation->status, ['accepted', 'rejected'])) {
            try {
                $admins = User::where('role', 'admin')->get();
                if ($admins->count() > 0) {
                    Notification::send($admins, new ScheduledTripDriverInvitationResponded($invitation));
                }
            } catch (\Exception $e) {
                // Log exception silently without breaking AJAX flow
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Scheduled trip invitation status updated to ' . ucfirst($request->status) . '.'
        ]);
    }

    /**
     * Display public magic link page for scheduled trip driver invitation response.
     */
    public function ShowTripInvitationMagicLink($token)
    {
        $invitation = ScheduledTripDriverInvitation::where('token', $token)
            ->with([
                'scheduledTripOrder.customer.companyProfile',
                'scheduledTripOrder.pickupCountry',
                'scheduledTripOrder.pickupCity',
                'scheduledTripOrder.dropoffCountry',
                'scheduledTripOrder.dropoffCity',
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
                'driver.driverProfile',
                'driverTruck.truckType',
                'driverTruck.truckSubType',
                'driverTruck.truckBrand',
                'driverTruck.truckModel'
            ])
            ->first();

        if (!$invitation) {
            return view('frontend.invitation.invalid');
        }

        return view('frontend.invitation.trip_show', compact('invitation'));
    }

    /**
     * Handle driver response (Accept or Reject) via magic link for scheduled trip.
     */
    public function RespondTripInvitationMagicLink(Request $request, $token)
    {
        $invitation = ScheduledTripDriverInvitation::where('token', $token)
            ->with(['scheduledTrip', 'scheduledTripOrder'])
            ->firstOrFail();

        $request->validate([
            'action'           => 'required|in:accept,reject',
            'rejection_reason' => 'required_if:action,reject|nullable|string|max:1000',
        ]);

        if ($request->action === 'accept') {
            $invitation->status = 'accepted';
            $invitation->rejection_reason = null;
            $msg = 'Thank you! You have successfully accepted this scheduled trip invitation.';

            // Assign driver to scheduled trip order if unassigned
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
            $invitation->rejection_reason = $request->rejection_reason ?: 'Driver declined scheduled trip invitation.';
            $msg = 'Scheduled trip invitation response recorded. Thank you for your feedback.';
        }

        $invitation->save();

        // Send Laravel Database Notification to Admin Users
        try {
            $admins = User::where('role', 'admin')->get();
            if ($admins->count() > 0) {
                Notification::send($admins, new ScheduledTripDriverInvitationResponded($invitation));
            }
        } catch (\Exception $e) {
            // Log notification error silently
        }

        return redirect()->back()->with('success_message', $msg);
    }
}
