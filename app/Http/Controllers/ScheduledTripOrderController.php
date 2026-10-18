<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ScheduledTripOrder;
use App\Models\ScheduledTrip;
use App\Models\User;
use App\Models\ShipmentType;
use App\Models\ShipmentNature;
use App\Models\TruckType;
use App\Models\TruckSubType;
use App\Models\Country;
use App\Models\City;
use App\Models\Invoice;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduledTripOrderController extends Controller
{
    /**
     * Display all scheduled trip orders with stats, filters & data table.
     */
    public function AllOrders(Request $request)
    {
        $query = ScheduledTripOrder::with([
            'scheduledTrip.route.originCountry',
            'scheduledTrip.route.originCity',
            'scheduledTrip.route.destinationCountry',
            'scheduledTrip.route.destinationCity',
            'scheduledTrip.truckType',
            'customer.companyProfile',
            'driver',
            'truckType',
            'truckSubType',
            'pickupCity',
            'dropoffCity',
            'invoice'
        ])->latest();

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by Scheduled Trip
        if ($request->filled('trip_id')) {
            $query->where('scheduled_trip_id', $request->trip_id);
        }

        // Filter by Customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by Payment Status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhere('shipment_name', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('fname', 'like', "%{$search}%")
                         ->orWhere('lname', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('scheduledTrip.route.originCity', function($oc) use ($search) {
                      $oc->where('name_en', 'like', "%{$search}%")->orWhere('name_ar', 'like', "%{$search}%");
                  })
                  ->orWhereHas('scheduledTrip.route.destinationCity', function($dc) use ($search) {
                      $dc->where('name_en', 'like', "%{$search}%")->orWhere('name_ar', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->get();

        // Statistics
        $stats = [
            'total'       => ScheduledTripOrder::count(),
            'new'         => ScheduledTripOrder::where('status', 'new')->count(),
            'confirmed'   => ScheduledTripOrder::where('status', 'confirmed')->count(),
            'in_transit'  => ScheduledTripOrder::where('status', 'in_transit')->count(),
            'delivered'   => ScheduledTripOrder::where('status', 'delivered')->count(),
            'canceled'    => ScheduledTripOrder::where('status', 'canceled')->count(),
            'total_sales' => ScheduledTripOrder::where('status', '!=', 'canceled')->sum('total_price'),
        ];

        $trips     = ScheduledTrip::with(['route.originCity', 'route.destinationCity'])->latest()->get();
        $customers = User::whereIn('role', ['individual_customer', 'company_customer'])->orderBy('fname', 'asc')->get();

        return view('admin.backend.scheduled_trip_order.all_orders', compact('orders', 'stats', 'trips', 'customers'));
    }

    /**
     * Show the Add Scheduled Trip Order 8-Step Wizard.
     */
    public function AddOrder(Request $request)
    {
        // Scheduled trips available for booking
        $trips = ScheduledTrip::with([
            'route.originCountry',
            'route.originCity',
            'route.destinationCountry',
            'route.destinationCity',
            'truckType',
            'truckSubType',
            'shipmentType',
            'shipmentNature',
            'driver'
        ])
        ->whereIn('status', ['published', 'boarding'])
        ->latest()
        ->get();

        $selectedTripId = $request->query('trip_id');

        // If a specific trip was requested that is not in published/boarding, load it too
        if ($selectedTripId && !$trips->contains('id', $selectedTripId)) {
            $extraTrip = ScheduledTrip::with([
                'route.originCountry',
                'route.originCity',
                'route.destinationCountry',
                'route.destinationCity',
                'truckType',
                'truckSubType',
                'shipmentType',
                'shipmentNature',
                'driver'
            ])->find($selectedTripId);

            if ($extraTrip) {
                $trips->prepend($extraTrip);
            }
        }

        $customers       = User::whereIn('role', ['individual_customer', 'company_customer'])
                                ->with('companyProfile')
                                ->orderBy('fname', 'asc')
                                ->get();
        $shipmentTypes   = ShipmentType::orderBy('id', 'asc')->get();
        $shipmentNatures = ShipmentNature::orderBy('id', 'asc')->get();
        $truckTypes      = TruckType::orderBy('id', 'asc')->get();
        $countries       = Country::where('is_active', 1)->orderBy('name_en', 'asc')->get();

        return view('admin.backend.scheduled_trip_order.add_order', compact(
            'trips',
            'selectedTripId',
            'customers',
            'shipmentTypes',
            'shipmentNatures',
            'truckTypes',
            'countries'
        ));
    }

    /**
     * AJAX endpoint to return complete details of a selected Scheduled Trip.
     */
    public function GetTripDataAjax($id)
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
            'driver'
        ])->find($id);

        if (!$trip) {
            return response()->json(['status' => 'error', 'message' => 'Scheduled Trip not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'trip'   => [
                'id'                 => $trip->id,
                'trip_date'          => $trip->trip_date,
                'trip_time'          => $trip->trip_time,
                'price'              => (float) $trip->price,
                'total_capacity'     => (int) $trip->total_capacity,
                'available_capacity' => (int) $trip->available_capacity,
                'status'             => $trip->status,
                'number_of_trucks'   => $trip->number_of_trucks,
                'total_weight_ton'   => $trip->total_weight_ton,
                'customs_clearance'  => (bool) $trip->customs_clearance,
                'notes'              => $trip->notes,
                'driver'             => $trip->driver ? [
                    'id'    => $trip->driver->id,
                    'name'  => $trip->driver->fname . ' ' . $trip->driver->lname,
                    'phone' => $trip->driver->phone,
                ] : null,
                'required_documents' => $trip->required_documents ?: [],
                'truck_type'         => $trip->truckType ? [
                    'id'      => $trip->truckType->id,
                    'name_en' => $trip->truckType->name_en,
                    'name_ar' => $trip->truckType->name_ar,
                ] : null,
                'truck_sub_type'     => $trip->truckSubType ? [
                    'id'      => $trip->truckSubType->id,
                    'name_en' => $trip->truckSubType->name_en,
                    'name_ar' => $trip->truckSubType->name_ar,
                ] : null,
                'shipment_type_id'   => $trip->shipment_type_id,
                'shipment_nature_id' => $trip->shipment_nature_id,
                'shipment_type'      => $trip->shipmentType ? [
                    'id'      => $trip->shipmentType->id,
                    'name_en' => $trip->shipmentType->name_en,
                    'name_ar' => $trip->shipmentType->name_ar,
                ] : null,
                'shipment_nature'    => $trip->shipmentNature ? [
                    'id'      => $trip->shipmentNature->id,
                    'name_en' => $trip->shipmentNature->name_en,
                    'name_ar' => $trip->shipmentNature->name_ar,
                ] : null,
                'route'              => $trip->route ? [
                    'id'                 => $trip->route->id,
                    'quote_type'         => $trip->route->quote_type,
                    'distance_km'        => $trip->route->distance_km,
                    'estimated_duration' => $trip->route->estimated_duration_hours,
                    'origin_country_id'  => $trip->route->origin_country_id,
                    'origin_country'     => $trip->route->originCountry ? $trip->route->originCountry->name_en : null,
                    'origin_city_id'     => $trip->route->origin_city_id,
                    'origin_city'        => $trip->route->originCity ? $trip->route->originCity->name_en : null,
                    'origin_address'     => $trip->route->origin_address ?: ($trip->route->originCity ? $trip->route->originCity->name_en : ''),
                    'origin_lat'         => $trip->route->origin_lat,
                    'origin_lng'         => $trip->route->origin_lng,
                    'destination_country_id' => $trip->route->destination_country_id,
                    'destination_country'    => $trip->route->destinationCountry ? $trip->route->destinationCountry->name_en : null,
                    'destination_city_id'    => $trip->route->destination_city_id,
                    'destination_city'       => $trip->route->destinationCity ? $trip->route->destinationCity->name_en : null,
                    'destination_address'    => $trip->route->destination_address ?: ($trip->route->destinationCity ? $trip->route->destinationCity->name_en : ''),
                    'destination_lat'        => $trip->route->destination_lat,
                    'destination_lng'        => $trip->route->destination_lng,
                ] : null,
            ]
        ]);
    }

    /**
     * Store new scheduled trip order in database with 8-step specs.
     */
    public function StoreOrder(Request $request)
    {
        $request->validate([
            'scheduled_trip_id'     => 'required|exists:scheduled_trips,id',
            'customer_id'           => 'required|exists:users,id',
            'booked_capacity'       => 'required|integer|min:1',
            'shipment_name'         => 'nullable|string|max:255',
            'shipment_description'  => 'nullable|string',
            'shipment_type_id'      => 'nullable',
            'shipment_nature_id'    => 'nullable',
            'length'                => 'nullable|numeric|min:0',
            'width'                 => 'nullable|numeric|min:0',
            'height'                => 'nullable|numeric|min:0',
            'weight'                => 'nullable|numeric|min:0',
            'packages_count'        => 'nullable|integer|min:1',
            'goods_description'     => 'nullable|string',
            'hs_code'               => 'nullable|string|max:50',
            'hs_code_description'   => 'nullable|string',
            'truck_type_id'         => 'nullable',
            'truck_sub_type_id'     => 'nullable',
            'number_of_trucks'      => 'nullable|integer|min:1',
            'special_equipment'     => 'nullable',
            'truck_notes'           => 'nullable|string',
            'pickup_country_id'     => 'nullable',
            'pickup_city_id'        => 'nullable',
            'pickup_area'           => 'nullable|string|max:255',
            'pickup_address'        => 'nullable|string|max:255',
            'pickup_lat'            => 'nullable|numeric',
            'pickup_lng'            => 'nullable|numeric',
            'loading_date'          => 'nullable|date',
            'loading_time'          => 'nullable|string|max:50',
            'loading_time_slot'     => 'nullable|string|max:100',
            'pickup_instructions'   => 'nullable|string',
            'dropoff_country_id'    => 'nullable',
            'dropoff_city_id'       => 'nullable',
            'dropoff_area'          => 'nullable|string|max:255',
            'dropoff_address'       => 'nullable|string|max:255',
            'dropoff_lat'           => 'nullable|numeric',
            'dropoff_lng'           => 'nullable|numeric',
            'receiving_date'        => 'nullable|date',
            'receiving_time'        => 'nullable|string|max:50',
            'receiving_time_slot'   => 'nullable|string|max:100',
            'delivery_instructions' => 'nullable|string',
            'loading_contact_name'  => 'nullable|string|max:255',
            'loading_contact_phone' => 'nullable|string|max:255',
            'arrival_contact_name'  => 'nullable|string|max:255',
            'arrival_contact_phone' => 'nullable|string|max:255',
            'initial_price'         => 'nullable|numeric|min:0',
            'total_price'           => 'nullable|numeric|min:0',
        ], [
            'scheduled_trip_id.required' => 'Please select a Scheduled Trip to link this order.',
            'customer_id.required'       => 'Please select a customer in Step 1.',
            'booked_capacity.required'   => 'Please enter the number of booked capacity units.',
        ]);

        $trip = ScheduledTrip::with('route')->findOrFail($request->scheduled_trip_id);

        $order = new ScheduledTripOrder();

        // Unique order number
        $order->order_number      = 'STO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
        $order->scheduled_trip_id = $trip->id;
        $order->customer_id       = $request->customer_id;
        $order->driver_id         = $trip->driver_id ?: null; // inherit driver from scheduled trip

        // Step 2 — Shipment Cargo Details
        $order->shipment_name        = $request->shipment_name ?: ('Cargo for Trip #' . $trip->id);
        $order->shipment_description = $request->shipment_description;
        $order->shipment_type_id     = $request->shipment_type_id ?: null;
        $order->shipment_nature_id   = $request->shipment_nature_id ?: null;
        $order->length               = $request->length ?: null;
        $order->width                = $request->width ?: null;
        $order->height               = $request->height ?: null;
        $order->weight               = $request->weight ?: null;
        $order->packages_count       = $request->packages_count ?? 1;
        $order->goods_description    = $request->goods_description;
        $order->hs_code              = $request->hs_code ?: null;
        $order->hs_code_description  = $request->hs_code_description ?: null;
        $order->is_fragile           = $request->has('is_fragile') ? 1 : 0;

        // Step 3 — Truck Specs (from form or fallback to trip)
        $order->truck_type_id     = $request->truck_type_id ?: $trip->truck_type_id;
        $order->truck_sub_type_id = $request->truck_sub_type_id ?: $trip->truck_sub_type_id;
        $order->number_of_trucks  = $request->filled('number_of_trucks') ? max(1, (int) $request->number_of_trucks) : 1;

        if ($request->filled('special_equipment')) {
            $equip = $request->special_equipment;
            $order->special_equipment = is_array($equip) ? json_encode(array_values($equip)) : $equip;
        }

        if ($request->filled('truck_notes')) {
            $order->truck_notes = $request->truck_notes;
            if (empty($order->notes)) {
                $order->notes = $request->truck_notes;
            }
        }

        // Step 4 — Pickup Location & Schedule
        $order->pickup_country_id   = $request->pickup_country_id ?: ($trip->route ? $trip->route->origin_country_id : null);
        $order->pickup_city_id      = $request->pickup_city_id ?: ($trip->route ? $trip->route->origin_city_id : null);
        $order->pickup_area         = $request->pickup_area;
        $order->pickup_address      = $request->pickup_address ?: ($request->pickup_area ?: 'Pickup Location Pin');
        $order->pickup_lat          = $request->pickup_lat ?: ($trip->route ? $trip->route->origin_lat : null);
        $order->pickup_lng          = $request->pickup_lng ?: ($trip->route ? $trip->route->origin_lng : null);
        $order->loading_date        = $request->loading_date ?: ($trip->trip_date ?: null);
        $order->loading_time        = $request->loading_time ?: ($trip->trip_time ?: null);
        $order->loading_time_slot   = $request->loading_time_slot ?: 'Afternoon (12:00 - 16:00)';
        $order->pickup_instructions = $request->pickup_instructions;

        // Step 5 — Delivery Location & Schedule
        $order->dropoff_country_id    = $request->dropoff_country_id ?: ($trip->route ? $trip->route->destination_country_id : null);
        $order->dropoff_city_id       = $request->dropoff_city_id ?: ($trip->route ? $trip->route->destination_city_id : null);
        $order->dropoff_area          = $request->dropoff_area;
        $order->dropoff_address       = $request->dropoff_address ?: ($request->dropoff_area ?: 'Delivery Location Pin');
        $order->dropoff_lat           = $request->dropoff_lat ?: ($trip->route ? $trip->route->destination_lat : null);
        $order->dropoff_lng           = $request->dropoff_lng ?: ($trip->route ? $trip->route->destination_lng : null);
        $order->receiving_date        = $request->receiving_date ?: null;
        $order->receiving_time        = $request->receiving_time ?: null;
        $order->receiving_time_slot   = $request->receiving_time_slot ?: 'Evening (16:00 - 20:00)';
        $order->delivery_instructions = $request->delivery_instructions;

        // Step 6 — Contacts
        $order->loading_contact_name  = $request->loading_contact_name;
        $order->loading_contact_phone = $request->loading_contact_phone;
        $order->arrival_contact_name  = $request->arrival_contact_name;
        $order->arrival_contact_phone = $request->arrival_contact_phone;
        $order->loading_contact       = trim($request->loading_contact_name . ' — ' . $request->loading_contact_phone, ' — ') ?: '—';
        $order->arrival_contact       = trim($request->arrival_contact_name . ' — ' . $request->arrival_contact_phone, ' — ') ?: '—';

        // Step 7 — Pricing & Capacity
        $tripPrice     = (float) $trip->price;
        $bookedUnits   = max(1, (int) $request->booked_capacity);
        $computedTotal = $tripPrice * $bookedUnits;

        $order->trip_price      = $tripPrice;
        $order->booked_capacity = $bookedUnits;
        $order->initial_price   = $request->initial_price ?: $computedTotal;
        $order->total_price     = $request->total_price ?: ($request->initial_price ?: $computedTotal);

        // Status Defaults
        $order->status            = 'new';
        $order->payment_method    = $request->payment_method ?: 'cash';
        $isCreditPayment          = in_array(strtolower($order->payment_method), ['credit', 'wallet']);
        $order->payment_status    = $isCreditPayment ? 'paid' : ($request->payment_status ?: 'unpaid');
        $order->customs_clearance = $request->has('customs_clearance') ? (bool) $request->customs_clearance : (bool) ($trip->customs_clearance ?? true);

        // Step 7 — Uploaded Documents Handling
        $savedDocuments = [];
        if ($request->hasFile('order_documents_files')) {
            $files   = $request->file('order_documents_files');
            $titles  = $request->input('order_documents_titles', []);
            $reqList = $request->input('order_documents_is_required', []);

            foreach ($files as $idx => $file) {
                if ($file && $file->isValid()) {
                    $originalName = $file->getClientOriginalName();
                    $ext          = strtolower($file->getClientOriginalExtension());
                    $fileSizeKb   = round($file->getSize() / 1024, 1);
                    $fileSizeStr  = $fileSizeKb > 1024 ? (round($fileSizeKb / 1024, 2) . ' MB') : ($fileSizeKb . ' KB');
                    $title        = isset($titles[$idx]) && !empty($titles[$idx]) ? $titles[$idx] : ('Document ' . ($idx + 1));
                    $isRequired   = isset($reqList[$idx]) && $reqList[$idx] == '1';

                    $safeName     = time() . '_' . uniqid() . '.' . $ext;
                    $uploadPath   = 'upload/order_documents/';

                    if (!file_exists(public_path($uploadPath))) {
                        mkdir(public_path($uploadPath), 0777, true);
                    }

                    $file->move(public_path($uploadPath), $safeName);

                    $savedDocuments[] = [
                        'title'         => $title,
                        'file_name'     => $safeName,
                        'file_path'     => $uploadPath . $safeName,
                        'original_name' => $originalName,
                        'file_size'     => $fileSizeStr,
                        'file_type'     => $ext,
                        'is_required'   => $isRequired,
                        'uploaded_at'   => now()->toDateTimeString(),
                    ];
                }
            }
        }
        $order->documents = count($savedDocuments) ? $savedDocuments : null;

        $customer = $order->customer_id ? User::find($order->customer_id) : null;
        DB::transaction(function () use ($order, $trip, $bookedUnits, $isCreditPayment, $customer) {
            $order->save();

            // Decrement available capacity on the scheduled trip
            if ($trip->available_capacity >= $bookedUnits) {
                $trip->decrement('available_capacity', $bookedUnits);
            } else {
                $trip->update(['available_capacity' => 0]);
            }

            // Deduct user credit and record wallet transaction if paid via credit
            if ($isCreditPayment && $customer) {
                $orderTotal = (float) $order->total_price;
                $customer->decrement('credit', $orderTotal);
                $newBalance = (float) $customer->fresh()->credit;

                WalletTransaction::create([
                    'user_id'           => $customer->id,
                    'amount'            => $orderTotal,
                    'type'              => 'withdrawal',
                    'balance_after'     => $newBalance,
                    'description'       => 'Credit payment for Scheduled Trip Order #' . $order->order_number,
                    'scheduled_trip_id' => $trip->id,
                ]);
            }

            // ── Auto Generate Invoice for Scheduled Trip Order ──
            $baseAmount  = (float) $order->total_price;
            $taxAmount   = round($baseAmount * 0.15, 2);
            $finalAmount = $baseAmount + $taxAmount;

            Invoice::create([
                'shipment_id'              => null,
                'scheduled_trip_id'        => $trip->id,
                'scheduled_trip_order_id'  => $order->id,
                'user_id'                  => $order->customer_id,
                'invoice_number'           => 'INV-' . date('Ymd') . '-STO' . sprintf('%04d', $order->id),
                'base_amount'              => $baseAmount,
                'tax_amount'               => $taxAmount,
                'discount'                 => 0.00,
                'total_amount'             => $finalAmount,
                'issued_at'                => now(),
                'due_date'                 => now()->addDays(7),
                'status'                   => $isCreditPayment ? 'paid' : 'unpaid',
                'notes'                    => 'Auto-generated invoice for Scheduled Trip Order #' . $order->order_number . ($isCreditPayment ? ' (Paid via Credit)' : ''),
            ]);
        });

        try {
            \App\Services\AdminNotificationService::newOrderCreated($order);
        } catch (\Throwable $ignoreAdminNotif) {}

        $notification = [
            'message'    => 'Scheduled Trip Order ' . $order->order_number . ' Created Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.scheduled.trip.orders')->with($notification);
    }

    /**
     * AJAX endpoint to fetch full order specs for the modal preview.
     */
    public function GetOrderDetailsAjax($id)
    {
        $order = ScheduledTripOrder::with([
            'scheduledTrip.route.originCountry',
            'scheduledTrip.route.originCity',
            'scheduledTrip.route.destinationCountry',
            'scheduledTrip.route.destinationCity',
            'scheduledTrip.driver',
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
            'invoice'
        ])->find($id);

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
        }

        // Attach safe view_url and download_url to each document item
        if (is_array($order->documents)) {
            $docs = $order->documents;
            foreach ($docs as $idx => &$d) {
                $d['view_url']     = route('view.scheduled.trip.order.document', ['id' => $order->id, 'index' => $idx]);
                $d['download_url'] = route('download.scheduled.trip.order.document', ['id' => $order->id, 'index' => $idx]);
            }
            $order->documents = $docs;
        }

        return response()->json(['status' => 'success', 'order' => $order]);
    }

    /**
     * Safely preview an uploaded document inline (image, pdf, etc.)
     */
    public function ViewOrderDocument($id, $index)
    {
        $order = ScheduledTripOrder::findOrFail($id);
        $docs = $order->documents;
        if (!is_array($docs) || !isset($docs[$index])) {
            abort(404, 'Document record not found');
        }

        $doc = $docs[$index];
        $filePath = $doc['file_path'] ?? '';

        $resolvedPath = $this->resolveDocumentDiskPath($filePath);
        if (!$resolvedPath || !file_exists($resolvedPath)) {
            abort(404, 'Document file not found on server disk');
        }

        $mime = mime_content_type($resolvedPath) ?: 'application/octet-stream';
        $fileName = $doc['original_name'] ?? $doc['file_name'] ?? basename($resolvedPath);

        return response()->file($resolvedPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Download an uploaded order document
     */
    public function DownloadOrderDocument($id, $index)
    {
        $order = ScheduledTripOrder::findOrFail($id);
        $docs = $order->documents;
        if (!is_array($docs) || !isset($docs[$index])) {
            abort(404, 'Document record not found');
        }

        $doc = $docs[$index];
        $filePath = $doc['file_path'] ?? '';

        $resolvedPath = $this->resolveDocumentDiskPath($filePath);
        if (!$resolvedPath || !file_exists($resolvedPath)) {
            abort(404, 'Document file not found on server disk');
        }

        $fileName = $doc['original_name'] ?? $doc['file_name'] ?? basename($resolvedPath);
        return response()->download($resolvedPath, $fileName);
    }

    /**
     * Helper to resolve document disk path from public_path, upload/order_documents, or absolute path
     */
    private function resolveDocumentDiskPath($filePath)
    {
        if (empty($filePath)) return null;

        // 1. Check relative to public_path
        $p = public_path(ltrim($filePath, '/'));
        if (file_exists($p) && !is_dir($p)) {
            return $p;
        }

        // 2. Check if file is in public/upload/order_documents/ by filename
        $base = basename($filePath);
        $pUpload = public_path('upload/order_documents/' . $base);
        if (file_exists($pUpload) && !is_dir($pUpload)) {
            return $pUpload;
        }

        // 3. Check exact absolute path on server machine
        if (file_exists($filePath) && !is_dir($filePath)) {
            return $filePath;
        }

        return null;
    }

    /**
     * AJAX status updater.
     */
    public function ChangeStatusAjax(Request $request)
    {
        $order = ScheduledTripOrder::find($request->order_id);
        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
        }

        $order->status = $request->status;
        $order->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Scheduled Trip Order #' . $order->id . ' status updated to ' . ucfirst($order->status)
        ]);
    }

    /**
     * Delete order and restore trip available capacity.
     */
    public function DeleteOrder($id)
    {
        $order = ScheduledTripOrder::find($id);
        if ($order) {
            $trip = ScheduledTrip::find($order->scheduled_trip_id);
            if ($trip && $order->booked_capacity > 0) {
                $newCap = min($trip->total_capacity, $trip->available_capacity + $order->booked_capacity);
                $trip->update(['available_capacity' => $newCap]);
            }
            $order->delete();
        }

        $notification = [
            'message'    => 'Scheduled Trip Order Deleted Successfully!',
            'alert-type' => 'info'
        ];

        return redirect()->back()->with($notification);
    }
}
