<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScheduledTripOrder;
use App\Models\ScheduledTrip;
use App\Models\Invoice;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ScheduledTripOrderController extends Controller
{
    /**
     * List all scheduled trip orders with filters & pagination.
     */
    public function index(Request $request)
    {
        $query = ScheduledTripOrder::with([
            'scheduledTrip.route.originCountry:id,name_ar,name_en,code',
            'scheduledTrip.route.originCity:id,name_ar,name_en',
            'scheduledTrip.route.destinationCountry:id,name_ar,name_en,code',
            'scheduledTrip.route.destinationCity:id,name_ar,name_en',
            'customer:id,fname,lname,phone,email,photo,client_code',
            'driver:id,fname,lname,phone,email,photo,client_code',
            'truckType',
            'truckSubType',
            'shipmentType',
            'shipmentNature',
            'pickupCountry:id,name_ar,name_en',
            'pickupCity:id,name_ar,name_en',
            'dropoffCountry:id,name_ar,name_en',
            'dropoffCity:id,name_ar,name_en',
            'invoice'
        ])->latest();

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('scheduled_trip_id')) {
            $query->where('scheduled_trip_id', $request->scheduled_trip_id);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Search
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhere('shipment_name', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('fname', 'like', "%{$search}%")
                         ->orWhere('lname', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'status'  => true,
            'message' => 'Scheduled trip orders retrieved successfully',
            'data'    => $orders
        ], 200);
    }

    /**
     * Get orders related to the authenticated user (as customer or driver).
     */
    public function myOrders(Request $request)
    {
        $user = $request->user('sanctum') ?? $request->user();

        $query = ScheduledTripOrder::with([
            'scheduledTrip.route.originCountry:id,name_ar,name_en,code',
            'scheduledTrip.route.originCity:id,name_ar,name_en',
            'scheduledTrip.route.destinationCountry:id,name_ar,name_en,code',
            'scheduledTrip.route.destinationCity:id,name_ar,name_en',
            'scheduledTrip.truckType',
            'customer:id,fname,lname,phone,email,photo,client_code',
            'driver:id,fname,lname,phone,email,photo,client_code',
            'truckType',
            'truckSubType',
            'shipmentType',
            'shipmentNature',
            'pickupCountry:id,name_ar,name_en',
            'pickupCity:id,name_ar,name_en',
            'dropoffCountry:id,name_ar,name_en',
            'dropoffCity:id,name_ar,name_en',
            'invoice'
        ])->latest();

        if ($user) {
            if ($user->role === 'driver') {
                $query->where('driver_id', $user->id);
            } else {
                $query->where('customer_id', $user->id);
            }
        } elseif ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        } elseif ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        // Status Filter
        if ($request->filled('status')) {
            $status = strtolower(trim($request->status));
            if ($status === 'active') {
                $query->whereIn('status', ['confirmed', 'loaded', 'in_transit']);
            } elseif ($status === 'pending') {
                $query->whereIn('status', ['new']);
            } elseif ($status === 'delivered') {
                $query->whereIn('status', ['delivered', 'completed']);
            } elseif ($status === 'canceled' || $status === 'cancelled') {
                $query->whereIn('status', ['canceled']);
            } elseif ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhere('shipment_name', 'like', "%{$search}%")
                  ->orWhere('goods_description', 'like', "%{$search}%")
                  ->orWhereHas('scheduledTrip.route.originCity', function ($oc) use ($search) {
                      $oc->where('name_en', 'like', "%{$search}%")->orWhere('name_ar', 'like', "%{$search}%");
                  })
                  ->orWhereHas('scheduledTrip.route.destinationCity', function ($dc) use ($search) {
                      $dc->where('name_en', 'like', "%{$search}%")->orWhere('name_ar', 'like', "%{$search}%");
                  })
                  ->orWhereHas('pickupCity', function ($pc) use ($search) {
                      $pc->where('name_en', 'like', "%{$search}%")->orWhere('name_ar', 'like', "%{$search}%");
                  })
                  ->orWhereHas('dropoffCity', function ($dc) use ($search) {
                      $dc->where('name_en', 'like', "%{$search}%")->orWhere('name_ar', 'like', "%{$search}%");
                  })
                  ->orWhereHas('truckType', function ($tt) use ($search) {
                      $tt->where('name_en', 'like', "%{$search}%")->orWhere('name_ar', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = $request->input('per_page', 50);
        $paginated = $query->paginate($perPage);

        return response()->json([
            'status'     => true,
            'message'    => 'My scheduled trip orders retrieved successfully',
            'data'       => $paginated->items(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ]
        ], 200);
    }

    /**
     * Get KPI statistics.
     */
    public function statistics()
    {
        $stats = [
            'total'       => ScheduledTripOrder::count(),
            'new'         => ScheduledTripOrder::where('status', 'new')->count(),
            'confirmed'   => ScheduledTripOrder::where('status', 'confirmed')->count(),
            'loaded'      => ScheduledTripOrder::where('status', 'loaded')->count(),
            'in_transit'  => ScheduledTripOrder::where('status', 'in_transit')->count(),
            'delivered'   => ScheduledTripOrder::where('status', 'delivered')->count(),
            'canceled'    => ScheduledTripOrder::where('status', 'canceled')->count(),
            'unpaid'      => ScheduledTripOrder::where('payment_status', 'unpaid')->count(),
            'paid'        => ScheduledTripOrder::where('payment_status', 'paid')->count(),
            'total_sales' => (float) ScheduledTripOrder::where('status', '!=', 'canceled')->sum('total_price'),
        ];

        return response()->json([
            'status'  => true,
            'message' => 'Scheduled trip orders statistics retrieved successfully',
            'data'    => $stats
        ], 200);
    }

    /**
     * Show single order details with relations.
     */
    public function show($id)
    {
        $order = ScheduledTripOrder::with([
            'scheduledTrip.route.originCountry',
            'scheduledTrip.route.originCity',
            'scheduledTrip.route.destinationCountry',
            'scheduledTrip.route.destinationCity',
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
            return response()->json([
                'status'  => false,
                'message' => 'Scheduled trip order not found'
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Scheduled trip order retrieved successfully',
            'data'    => $order
        ], 200);
    }

    /**
     * Store a new scheduled trip order.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $rules = [
            'scheduled_trip_id'     => 'required|exists:scheduled_trips,id',
            'customer_id'           => ($user && in_array($user->role, ['individual_customer', 'company_customer'])) ? 'nullable|exists:users,id' : 'required|exists:users,id',
            'booked_capacity'       => 'nullable|integer|min:1',
            'shipment_name'         => 'nullable|string|max:255',
            'shipment_description'  => 'nullable|string',
            'shipment_type_id'      => 'nullable|exists:shipment_types,id',
            'shipment_nature_id'    => 'nullable|exists:shipment_natures,id',
            'length'                => 'nullable|numeric|min:0',
            'width'                 => 'nullable|numeric|min:0',
            'height'                => 'nullable|numeric|min:0',
            'weight'                => 'nullable|numeric|min:0',
            'packages_count'        => 'nullable|integer|min:1',
            'goods_description'     => 'nullable|string',
            'hs_code'               => 'nullable|string|max:50',
            'hs_code_description'   => 'nullable|string',
            'is_fragile'            => 'nullable|boolean',
            'truck_type_id'         => 'nullable|exists:truck_types,id',
            'truck_sub_type_id'     => 'nullable|exists:truck_sub_types,id',
            'number_of_trucks'      => 'nullable|integer|min:1',
            'special_equipment'     => 'nullable',
            'truck_notes'           => 'nullable|string',
            'pickup_country_id'     => 'nullable|exists:countries,id',
            'pickup_city_id'        => 'nullable|exists:cities,id',
            'pickup_area'           => 'nullable|string|max:255',
            'pickup_address'        => 'nullable|string|max:255',
            'pickup_lat'            => 'nullable|numeric',
            'pickup_lng'            => 'nullable|numeric',
            'loading_date'          => 'nullable|date',
            'loading_time'          => 'nullable',
            'loading_time_slot'     => 'nullable|string|max:100',
            'pickup_instructions'   => 'nullable|string',
            'dropoff_country_id'    => 'nullable|exists:countries,id',
            'dropoff_city_id'       => 'nullable|exists:cities,id',
            'dropoff_area'          => 'nullable|string|max:255',
            'dropoff_address'       => 'nullable|string|max:255',
            'dropoff_lat'           => 'nullable|numeric',
            'dropoff_lng'           => 'nullable|numeric',
            'receiving_date'        => 'nullable|date',
            'receiving_time'        => 'nullable',
            'receiving_time_slot'   => 'nullable|string|max:100',
            'delivery_instructions' => 'nullable|string',
            'loading_contact_name'  => 'nullable|string|max:255',
            'loading_contact_phone' => 'nullable|string|max:255',
            'arrival_contact_name'  => 'nullable|string|max:255',
            'arrival_contact_phone' => 'nullable|string|max:255',
            'customs_clearance'     => 'nullable|boolean',
            'payment_method'        => 'nullable|string|max:50',
            'documents'             => 'nullable|array',
            'initial_price'         => 'nullable|numeric|min:0',
            'total_price'           => 'nullable|numeric|min:0',
            'notes'                 => 'nullable|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $trip = ScheduledTrip::with('route')->findOrFail($request->scheduled_trip_id);

        $customerId = ($user && in_array($user->role, ['individual_customer', 'company_customer']) && !$request->filled('customer_id'))
            ? $user->id
            : $request->customer_id;

        $bookedUnits   = max(1, (int) ($request->booked_capacity ?: 1));
        $tripPrice     = (float) $trip->price;
        $computedTotal = $tripPrice * $bookedUnits;

        $order = new ScheduledTripOrder();
        $order->order_number      = 'STO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
        $order->scheduled_trip_id = $trip->id;
        $order->customer_id       = $customerId;
        $order->driver_id         = $trip->driver_id ?: null;

        // Step 2 — Cargo Details
        $order->shipment_name        = $request->shipment_name ?: ('Cargo for Scheduled Trip #' . $trip->id);
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
        $order->is_fragile           = $request->has('is_fragile') ? (int) $request->is_fragile : 0;

        // Step 3 — Truck Specs (inherits from trip if not specified)
        $order->truck_type_id     = $request->truck_type_id ?: $trip->truck_type_id;
        $order->truck_sub_type_id = $request->truck_sub_type_id ?: $trip->truck_sub_type_id;
        $order->number_of_trucks  = $request->number_of_trucks ?: ($trip->number_of_trucks ?: 1);
        $order->special_equipment = $request->has('special_equipment')
            ? (is_array($request->special_equipment) ? $request->special_equipment : json_decode($request->special_equipment, true))
            : null;
        $order->truck_notes       = $request->truck_notes ?: null;

        // Step 4 — Pickup (inherits from route origin) & Schedule
        $order->pickup_country_id   = $request->pickup_country_id ?: ($trip->route ? $trip->route->origin_country_id : null);
        $order->pickup_city_id      = $request->pickup_city_id ?: ($trip->route ? $trip->route->origin_city_id : null);
        $order->pickup_area         = $request->pickup_area;
        $order->pickup_address      = $request->pickup_address ?: ($trip->route ? ($trip->route->origin_address ?: 'Pickup Pin') : 'Pickup Pin');
        $order->pickup_lat          = $request->pickup_lat ?: ($trip->route ? $trip->route->origin_lat : null);
        $order->pickup_lng          = $request->pickup_lng ?: ($trip->route ? $trip->route->origin_lng : null);
        $order->loading_date        = $request->loading_date ?: ($trip->trip_date ?: null);
        $order->loading_time        = $request->loading_time ?: ($trip->trip_time ?: null);
        $order->loading_time_slot   = $request->loading_time_slot ?: 'Morning (08:00 - 12:00)';
        $order->pickup_instructions = $request->pickup_instructions;

        // Step 5 — Delivery (inherits from route destination) & Schedule
        $order->dropoff_country_id    = $request->dropoff_country_id ?: ($trip->route ? $trip->route->destination_country_id : null);
        $order->dropoff_city_id       = $request->dropoff_city_id ?: ($trip->route ? $trip->route->destination_city_id : null);
        $order->dropoff_area          = $request->dropoff_area;
        $order->dropoff_address       = $request->dropoff_address ?: ($trip->route ? ($trip->route->destination_address ?: 'Delivery Pin') : 'Delivery Pin');
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

        // Step 7 — Uploaded Documents Handling
        $savedDocuments = [];
        if ($request->hasFile('order_documents_files')) {
            $files   = $request->file('order_documents_files');
            $titles  = $request->input('order_documents_titles', []);
            $reqList = $request->input('order_documents_is_required', []);

            if (!file_exists(public_path('upload/order_documents'))) {
                @mkdir(public_path('upload/order_documents'), 0777, true);
            }

            foreach ($files as $idx => $file) {
                if ($file && $file->isValid()) {
                    $ext = $file->getClientOriginalExtension();
                    $uniqueName = time() . '_' . uniqid() . '.' . $ext;
                    $file->move(public_path('upload/order_documents'), $uniqueName);

                    $sizeBytes = @filesize(public_path('upload/order_documents/' . $uniqueName));
                    $sizeFormatted = $sizeBytes > 1048576 ? round($sizeBytes / 1048576, 2) . ' MB' : round($sizeBytes / 1024, 1) . ' KB';

                    $savedDocuments[] = [
                        'title'         => $titles[$idx] ?? ('Document ' . ($idx + 1)),
                        'file_name'     => $uniqueName,
                        'file_path'     => 'upload/order_documents/' . $uniqueName,
                        'original_name' => $file->getClientOriginalName(),
                        'file_size'     => $sizeFormatted,
                        'file_type'     => strtolower($ext),
                        'is_required'   => isset($reqList[$idx]) && ($reqList[$idx] == '1' || $reqList[$idx] === true),
                        'uploaded_at'   => now()->format('Y-m-d H:i:s'),
                    ];
                }
            }
        } elseif ($request->has('documents') && is_array($request->documents)) {
            $savedDocuments = [];
            if (!file_exists(public_path('upload/order_documents'))) {
                @mkdir(public_path('upload/order_documents'), 0777, true);
            }
            foreach ($request->documents as $docItem) {
                if (is_array($docItem)) {
                    $fp = $docItem['file_path'] ?? '';
                    if (!empty($fp) && file_exists($fp) && !str_starts_with($fp, 'upload/order_documents')) {
                        $ext = pathinfo($fp, PATHINFO_EXTENSION) ?: 'jpg';
                        $uniqueName = time() . '_' . uniqid() . '.' . $ext;
                        @copy($fp, public_path('upload/order_documents/' . $uniqueName));
                        $docItem['file_path'] = 'upload/order_documents/' . $uniqueName;
                        $docItem['file_name'] = $uniqueName;
                    }
                    $savedDocuments[] = $docItem;
                }
            }
        }
        $order->documents = count($savedDocuments) ? $savedDocuments : null;

        // Step 8 — Pricing & Payment
        $order->trip_price        = $tripPrice;
        $order->booked_capacity   = $bookedUnits;
        $order->initial_price     = $request->initial_price ?: $computedTotal;
        $order->total_price       = $request->total_price ?: ($request->initial_price ?: $computedTotal);
        $order->status            = 'new';
        $order->payment_method    = $request->payment_method ?: 'cash';

        $isCreditPayment = in_array(strtolower($order->payment_method), ['credit', 'wallet']);
        $order->payment_status = $isCreditPayment ? 'paid' : ($request->payment_status ?: 'unpaid');
        $order->customs_clearance = $request->has('customs_clearance')
            ? (bool) $request->customs_clearance
            : (bool) ($trip->customs_clearance ?? true);
        $order->notes             = $request->notes ?: null;

        // Verify credit if paying via credit
        $customer = $order->customer_id ? User::find($order->customer_id) : null;
        if ($isCreditPayment && $customer) {
            $customerCredit = (float) $customer->credit;
            if ($customerCredit < (float) $order->total_price) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Insufficient credit balance (رصيد الائتمان غير كافٍ)',
                    'errors'  => [
                        'payment_method' => ['Available credit: ' . number_format($customerCredit, 3) . ' KWD, Required: ' . number_format($order->total_price, 3) . ' KWD']
                    ]
                ], 422);
            }
        }

        DB::transaction(function () use ($order, $trip, $bookedUnits, $isCreditPayment, $customer) {
            $order->save();

            // Decrement capacity
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

            // Auto Invoice
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
            if ($order->customer_id && ($notifUser = User::find($order->customer_id))) {
                $notifUser->notify(new \App\Notifications\CustomerActivityNotification([
                    'title'        => 'Scheduled Trip Order Placed #' . $order->order_number,
                    'body'         => 'Your order #' . $order->order_number . ' has been booked successfully for trip "' . ($trip->trip_name ?? 'Scheduled Trip') . '". Status: ' . ucfirst($order->status),
                    'type'         => 'order',
                    'reference_id' => $order->order_number,
                    'action_route' => '/orders_screen',
                    'metadata'     => [
                        'order_id'     => $order->id,
                        'order_number' => $order->order_number,
                        'status'       => $order->status,
                    ]
                ]));

                $notifUser->notify(new \App\Notifications\CustomerActivityNotification([
                    'title'        => 'Invoice Generated #' . ('INV-' . date('Ymd') . '-STO' . sprintf('%04d', $order->id)),
                    'body'         => 'A new invoice has been generated for your order #' . $order->order_number . ' with total amount ' . number_format($finalAmount, 3) . ' KWD (' . ($isCreditPayment ? 'Paid via Credit' : 'Unpaid') . ').',
                    'type'         => 'invoice',
                    'reference_id' => 'INV-' . date('Ymd') . '-STO' . sprintf('%04d', $order->id),
                    'action_route' => '/invoices_screen',
                    'metadata'     => [
                        'order_id'       => $order->id,
                        'order_number'   => $order->order_number,
                        'amount'         => $finalAmount,
                        'payment_status' => $isCreditPayment ? 'paid' : 'unpaid',
                    ]
                ]));
            }
        } catch (\Throwable $ignoreOrderNotif) {}

        try {
            \App\Services\AdminNotificationService::newOrderCreated($order);
        } catch (\Throwable $ignoreAdminNotif) {}

        $order->load(['scheduledTrip.route', 'customer', 'driver', 'truckType', 'invoice']);

        return response()->json([
            'status'  => true,
            'message' => 'Scheduled trip order created successfully',
            'data'    => $order
        ], 201);
    }

    /**
     * Update an existing scheduled trip order.
     */
    public function update(Request $request, $id)
    {
        $order = ScheduledTripOrder::find($id);
        if (!$order) {
            return response()->json([
                'status'  => false,
                'message' => 'Scheduled trip order not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'shipment_name'         => 'nullable|string|max:255',
            'shipment_description'  => 'nullable|string',
            'goods_description'     => 'nullable|string',
            'hs_code'               => 'nullable|string|max:50',
            'hs_code_description'   => 'nullable|string',
            'length'                => 'nullable|numeric|min:0',
            'width'                 => 'nullable|numeric|min:0',
            'height'                => 'nullable|numeric|min:0',
            'weight'                => 'nullable|numeric|min:0',
            'packages_count'        => 'nullable|integer|min:1',
            'number_of_trucks'      => 'nullable|integer|min:1',
            'special_equipment'     => 'nullable',
            'truck_notes'           => 'nullable|string',
            'pickup_address'        => 'nullable|string|max:255',
            'dropoff_address'       => 'nullable|string|max:255',
            'loading_date'          => 'nullable|date',
            'loading_time'          => 'nullable',
            'loading_time_slot'     => 'nullable|string|max:100',
            'pickup_instructions'   => 'nullable|string',
            'receiving_date'        => 'nullable|date',
            'receiving_time'        => 'nullable',
            'receiving_time_slot'   => 'nullable|string|max:100',
            'delivery_instructions' => 'nullable|string',
            'loading_contact_name'  => 'nullable|string|max:255',
            'loading_contact_phone' => 'nullable|string|max:255',
            'arrival_contact_name'  => 'nullable|string|max:255',
            'arrival_contact_phone' => 'nullable|string|max:255',
            'customs_clearance'     => 'nullable|boolean',
            'documents'             => 'nullable|array',
            'notes'                 => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $order->fill($request->only([
            'shipment_name',
            'shipment_description',
            'goods_description',
            'hs_code',
            'hs_code_description',
            'length',
            'width',
            'height',
            'weight',
            'packages_count',
            'number_of_trucks',
            'truck_notes',
            'pickup_area',
            'pickup_address',
            'loading_date',
            'loading_time',
            'loading_time_slot',
            'pickup_instructions',
            'dropoff_area',
            'dropoff_address',
            'receiving_date',
            'receiving_time',
            'receiving_time_slot',
            'delivery_instructions',
            'loading_contact_name',
            'loading_contact_phone',
            'arrival_contact_name',
            'arrival_contact_phone',
            'notes',
        ]));

        if ($request->has('special_equipment')) {
            $order->special_equipment = is_array($request->special_equipment)
                ? $request->special_equipment
                : json_decode($request->special_equipment, true);
        }

        if ($request->has('customs_clearance')) {
            $order->customs_clearance = (bool) $request->customs_clearance;
        }

        if ($request->has('documents')) {
            $order->documents = $request->documents;
        }

        if ($request->filled('loading_contact_name') || $request->filled('loading_contact_phone')) {
            $order->loading_contact = trim(($request->loading_contact_name ?: $order->loading_contact_name) . ' — ' . ($request->loading_contact_phone ?: $order->loading_contact_phone), ' — ');
        }

        if ($request->filled('arrival_contact_name') || $request->filled('arrival_contact_phone')) {
            $order->arrival_contact = trim(($request->arrival_contact_name ?: $order->arrival_contact_name) . ' — ' . ($request->arrival_contact_phone ?: $order->arrival_contact_phone), ' — ');
        }

        $order->save();

        return response()->json([
            'status'  => true,
            'message' => 'Scheduled trip order updated successfully',
            'data'    => $order
        ], 200);
    }

    /**
     * Change order status.
     */
    public function changeStatus(Request $request, $id)
    {
        $order = ScheduledTripOrder::find($id);
        if (!$order) {
            return response()->json([
                'status'  => false,
                'message' => 'Scheduled trip order not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:new,confirmed,loaded,in_transit,delivered,canceled'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $order->status = $request->status;
        $order->save();
        try {
            if ($order->customer_id && ($statusUser = User::find($order->customer_id))) {
                $statusUser->notify(new \App\Notifications\CustomerActivityNotification([
                    'title'        => 'Order Status Updated #' . $order->order_number,
                    'body'         => 'Your order #' . $order->order_number . ' status has been updated to: ' . ucfirst(str_replace('_', ' ', $order->status)) . '.',
                    'type'         => 'order',
                    'reference_id' => $order->order_number,
                    'action_route' => '/orders_screen',
                    'metadata'     => [
                        'order_id'     => $order->id,
                        'order_number' => $order->order_number,
                        'status'       => $order->status,
                    ]
                ]));
            }
        } catch (\Throwable $ignoreStatusNotif) {}

        return response()->json([
            'status'  => true,
            'message' => 'Order status updated successfully',
            'data'    => $order
        ], 200);
    }

    /**
     * Cancel or delete scheduled trip order.
     */
    public function destroy($id)
    {
        $order = ScheduledTripOrder::find($id);
        if (!$order) {
            return response()->json([
                'status'  => false,
                'message' => 'Scheduled trip order not found'
            ], 404);
        }

        $trip = ScheduledTrip::find($order->scheduled_trip_id);
        if ($trip && $order->booked_capacity > 0) {
            $newCap = min($trip->total_capacity, $trip->available_capacity + $order->booked_capacity);
            $trip->update(['available_capacity' => $newCap]);
        }

        $order->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Scheduled trip order deleted successfully'
        ], 200);
    }
}
