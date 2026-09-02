<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShipmentController extends Controller
{
    /**
     * List all shipments with full filtering and relationship loading.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Shipment::with([
            'customer:id,fname,lname,email,phone,country_code,client_code,photo,role',
            'customer.companyProfile',
            'driver:id,fname,lname,email,phone,client_code,photo',
            'truckType',
            'truckSubType',
            'shipmentType',
            'shipmentNature',
            'pickupCountry',
            'pickupCity',
            'dropoffCountry',
            'dropoffCity',
            'latestTracking'
        ])->latest();

        // Scope by User Role if not Admin
        if ($user && in_array($user->role, ['individual_customer', 'company_customer']) && !$request->filled('customer_id')) {
            $query->where('customer_id', $user->id);
        } elseif ($user && $user->role === 'driver' && !$request->filled('driver_id')) {
            $query->where('driver_id', $user->id);
        } elseif ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Payment Status Filter
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Location Filters
        if ($request->filled('pickup_city_id')) {
            $query->where('pickup_city_id', $request->pickup_city_id);
        }

        if ($request->filled('dropoff_city_id')) {
            $query->where('dropoff_city_id', $request->dropoff_city_id);
        }

        // Truck Filters
        if ($request->filled('truck_type_id')) {
            $query->where('truck_type_id', $request->truck_type_id);
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('shipment_name', 'like', "%{$search}%")
                  ->orWhere('goods_description', 'like', "%{$search}%")
                  ->orWhere('hs_code', 'like', "%{$search}%")
                  ->orWhere('pickup_address', 'like', "%{$search}%")
                  ->orWhere('dropoff_address', 'like', "%{$search}%")
                  ->orWhere('loading_contact_name', 'like', "%{$search}%")
                  ->orWhere('loading_contact_phone', 'like', "%{$search}%")
                  ->orWhere('arrival_contact_name', 'like', "%{$search}%")
                  ->orWhere('arrival_contact_phone', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('fname', 'like', "%{$search}%")
                         ->orWhere('lname', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('client_code', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = $request->input('per_page', 20);
        $shipments = $query->paginate($perPage);

        return response()->json([
            'status'  => true,
            'message' => 'Shipments retrieved successfully',
            'data'    => $shipments
        ], 200);
    }

    /**
     * Get shipments belonging to currently authenticated user.
     */
    public function myShipments(Request $request)
    {
        $user = $request->user();

        $query = Shipment::with([
            'customer:id,fname,lname,email,phone,country_code,client_code,photo',
            'driver:id,fname,lname,email,phone,client_code,photo',
            'truckType',
            'truckSubType',
            'shipmentType',
            'shipmentNature',
            'pickupCountry',
            'pickupCity',
            'dropoffCountry',
            'dropoffCity',
            'latestTracking'
        ])->latest();

        if ($user->role === 'driver') {
            $query->where('driver_id', $user->id);
        } else {
            $query->where('customer_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $shipments = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'status'  => true,
            'message' => 'My shipments retrieved successfully',
            'data'    => $shipments
        ], 200);
    }

    /**
     * Get KPI statistics overview for shipments.
     */
    public function statistics(Request $request)
    {
        $user = $request->user();
        $query = Shipment::query();

        if ($user && in_array($user->role, ['individual_customer', 'company_customer'])) {
            $query->where('customer_id', $user->id);
        } elseif ($user && $user->role === 'driver') {
            $query->where('driver_id', $user->id);
        }

        $stats = [
            'total'       => (clone $query)->count(),
            'new'         => (clone $query)->where('status', 'new')->count(),
            'approved'    => (clone $query)->where('status', 'approved')->count(),
            'in_transit'  => (clone $query)->whereIn('status', ['in_transit', 'picked_up', 'out_for_delivery'])->count(),
            'delivered'   => (clone $query)->whereIn('status', ['delivered', 'completed'])->count(),
            'canceled'    => (clone $query)->whereIn('status', ['canceled', 'rejected'])->count(),
            'total_value' => (clone $query)->sum('initial_price') ?? 0,
        ];

        return response()->json([
            'status'  => true,
            'message' => 'Shipment statistics retrieved successfully',
            'data'    => $stats
        ], 200);
    }

    /**
     * Show single shipment with all related models, addresses, contacts, and logs.
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

        // Attach Invoice if exists
        $invoice = Invoice::where('shipment_id', $shipment->id)->first();
        $data = $shipment->toArray();
        $data['invoice'] = $invoice;

        return response()->json([
            'status'  => true,
            'message' => 'Shipment details retrieved successfully',
            'data'    => $data
        ], 200);
    }

    /**
     * Store a new shipment order.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $rules = [
            'customer_id'           => ($user && in_array($user->role, ['individual_customer', 'company_customer'])) ? 'nullable|exists:users,id' : 'required|exists:users,id',
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
            'pickup_country_id'     => 'nullable|exists:countries,id',
            'pickup_city_id'        => 'nullable|exists:cities,id',
            'pickup_area'           => 'nullable|string|max:255',
            'pickup_address'        => 'required|string|max:255',
            'pickup_lat'            => 'nullable|numeric',
            'pickup_lng'            => 'nullable|numeric',
            'dropoff_country_id'    => 'nullable|exists:countries,id',
            'dropoff_city_id'       => 'nullable|exists:cities,id',
            'dropoff_area'          => 'nullable|string|max:255',
            'dropoff_address'       => 'required|string|max:255',
            'dropoff_lat'           => 'nullable|numeric',
            'dropoff_lng'           => 'nullable|numeric',
            'loading_contact_name'  => 'nullable|string|max:255',
            'loading_contact_phone' => 'nullable|string|max:255',
            'arrival_contact_name'  => 'nullable|string|max:255',
            'arrival_contact_phone' => 'nullable|string|max:255',
            'initial_price'         => 'nullable|numeric|min:0',
            'scheduled_date'        => 'nullable|date',
        ];

        $validator = Validator::make($request->all(), $rules, [
            'customer_id.required'     => 'Please specify the customer.',
            'pickup_address.required'  => 'Pickup address is required.',
            'dropoff_address.required' => 'Delivery/dropoff address is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $customerId = ($user && in_array($user->role, ['individual_customer', 'company_customer']) && !$request->filled('customer_id'))
            ? $user->id
            : $request->customer_id;

        $shipment = new Shipment();
        $shipment->customer_id           = $customerId;
        $shipment->shipment_name         = $request->shipment_name ?: 'General Cargo Shipment';
        $shipment->shipment_description  = $request->shipment_description;
        $shipment->shipment_type_id      = $request->shipment_type_id ?: null;
        $shipment->shipment_nature_id    = $request->shipment_nature_id ?: null;
        $shipment->length                = $request->length ?: null;
        $shipment->width                 = $request->width ?: null;
        $shipment->height                = $request->height ?: null;
        $shipment->weight                = $request->weight ?: null;
        $shipment->packages_count        = $request->packages_count ?? 1;
        $shipment->goods_description     = $request->goods_description;
        $shipment->hs_code               = $request->hs_code ?: null;
        $shipment->hs_code_description   = $request->hs_code_description ?: null;
        $shipment->is_fragile            = $request->has('is_fragile') ? (int)$request->is_fragile : 0;

        $shipment->truck_type_id         = $request->truck_type_id ?: null;
        $shipment->truck_sub_type_id     = $request->truck_sub_type_id ?: null;

        $shipment->pickup_country_id     = $request->pickup_country_id ?: null;
        $shipment->pickup_city_id        = $request->pickup_city_id ?: null;
        $shipment->pickup_area           = $request->pickup_area;
        $shipment->pickup_address        = $request->pickup_address;
        $shipment->pickup_lat            = $request->pickup_lat ?: null;
        $shipment->pickup_lng            = $request->pickup_lng ?: null;

        $shipment->dropoff_country_id    = $request->dropoff_country_id ?: null;
        $shipment->dropoff_city_id       = $request->dropoff_city_id ?: null;
        $shipment->dropoff_area          = $request->dropoff_area;
        $shipment->dropoff_address       = $request->dropoff_address;
        $shipment->dropoff_lat           = $request->dropoff_lat ?: null;
        $shipment->dropoff_lng           = $request->dropoff_lng ?: null;

        $shipment->loading_contact_name  = $request->loading_contact_name;
        $shipment->loading_contact_phone = $request->loading_contact_phone;
        $shipment->arrival_contact_name  = $request->arrival_contact_name;
        $shipment->arrival_contact_phone = $request->arrival_contact_phone;
        $shipment->loading_contact       = trim($request->loading_contact_name . ' — ' . $request->loading_contact_phone, ' — ') ?: '—';
        $shipment->arrival_contact       = trim($request->arrival_contact_name . ' — ' . $request->arrival_contact_phone, ' — ') ?: '—';

        $shipment->initial_price         = $request->initial_price ?: null;
        $shipment->scheduled_date        = $request->scheduled_date ?: null;
        $shipment->status                = 'new';
        $shipment->payment_status        = 'unpaid';

        $shipment->save();

        // Auto-generate invoice
        $baseAmount = (float) ($request->initial_price ?: 0.00);
        $invoice = Invoice::create([
            'shipment_id'       => $shipment->id,
            'scheduled_trip_id' => null,
            'user_id'           => $shipment->customer_id,
            'invoice_number'    => 'INV-' . date('Ymd') . '-' . sprintf('%04d', $shipment->id),
            'base_amount'       => $baseAmount,
            'tax_amount'        => 0.00,
            'discount'          => 0.00,
            'total_amount'      => $baseAmount,
            'status'            => 'unpaid',
            'issued_at'         => now(),
            'due_date'          => now()->addDays(14)->format('Y-m-d'),
        ]);

        $shipment->load([
            'customer.companyProfile',
            'truckType',
            'truckSubType',
            'shipmentType',
            'shipmentNature',
            'pickupCountry',
            'pickupCity',
            'dropoffCountry',
            'dropoffCity'
        ]);

        $response = $shipment->toArray();
        $response['invoice'] = $invoice;

        return response()->json([
            'status'  => true,
            'message' => 'Shipment order created and invoice generated successfully',
            'data'    => $response
        ], 201);
    }

    /**
     * Update an existing shipment order.
     */
    public function update(Request $request, $id)
    {
        $shipment = Shipment::find($id);
        if (!$shipment) {
            return response()->json([
                'status'  => false,
                'message' => 'Shipment not found'
            ], 404);
        }

        $user = $request->user();

        // Check customer ownership
        if ($user && in_array($user->role, ['individual_customer', 'company_customer']) && $shipment->customer_id !== $user->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized to update this shipment.'
            ], 403);
        }

        $rules = [
            'customer_id'           => 'nullable|exists:users,id',
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
            'pickup_country_id'     => 'nullable|exists:countries,id',
            'pickup_city_id'        => 'nullable|exists:cities,id',
            'pickup_area'           => 'nullable|string|max:255',
            'pickup_address'        => 'required|string|max:255',
            'pickup_lat'            => 'nullable|numeric',
            'pickup_lng'            => 'nullable|numeric',
            'dropoff_country_id'    => 'nullable|exists:countries,id',
            'dropoff_city_id'       => 'nullable|exists:cities,id',
            'dropoff_area'          => 'nullable|string|max:255',
            'dropoff_address'       => 'required|string|max:255',
            'dropoff_lat'           => 'nullable|numeric',
            'dropoff_lng'           => 'nullable|numeric',
            'loading_contact_name'  => 'nullable|string|max:255',
            'loading_contact_phone' => 'nullable|string|max:255',
            'arrival_contact_name'  => 'nullable|string|max:255',
            'arrival_contact_phone' => 'nullable|string|max:255',
            'initial_price'         => 'nullable|numeric|min:0',
            'scheduled_date'        => 'nullable|date',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        if ($request->filled('customer_id') && ($user && $user->role === 'admin')) {
            $shipment->customer_id = $request->customer_id;
        }

        $shipment->shipment_name         = $request->shipment_name ?: $shipment->shipment_name;
        $shipment->shipment_description  = $request->shipment_description;
        $shipment->shipment_type_id      = $request->shipment_type_id ?: null;
        $shipment->shipment_nature_id    = $request->shipment_nature_id ?: null;
        $shipment->length                = $request->length ?: null;
        $shipment->width                 = $request->width ?: null;
        $shipment->height                = $request->height ?: null;
        $shipment->weight                = $request->weight ?: null;
        $shipment->packages_count        = $request->packages_count ?? $shipment->packages_count;
        $shipment->goods_description     = $request->goods_description;
        $shipment->hs_code               = $request->hs_code ?: null;
        $shipment->hs_code_description   = $request->hs_code_description ?: null;
        if ($request->has('is_fragile')) {
            $shipment->is_fragile = (int)$request->is_fragile;
        }

        $shipment->truck_type_id         = $request->truck_type_id ?: null;
        $shipment->truck_sub_type_id     = $request->truck_sub_type_id ?: null;

        $shipment->pickup_country_id     = $request->pickup_country_id ?: null;
        $shipment->pickup_city_id        = $request->pickup_city_id ?: null;
        $shipment->pickup_area           = $request->pickup_area;
        $shipment->pickup_address        = $request->pickup_address;
        $shipment->pickup_lat            = $request->pickup_lat ?: null;
        $shipment->pickup_lng            = $request->pickup_lng ?: null;

        $shipment->dropoff_country_id    = $request->dropoff_country_id ?: null;
        $shipment->dropoff_city_id       = $request->dropoff_city_id ?: null;
        $shipment->dropoff_area          = $request->dropoff_area;
        $shipment->dropoff_address       = $request->dropoff_address;
        $shipment->dropoff_lat           = $request->dropoff_lat ?: null;
        $shipment->dropoff_lng           = $request->dropoff_lng ?: null;

        $shipment->loading_contact_name  = $request->loading_contact_name;
        $shipment->loading_contact_phone = $request->loading_contact_phone;
        $shipment->arrival_contact_name  = $request->arrival_contact_name;
        $shipment->arrival_contact_phone = $request->arrival_contact_phone;
        $shipment->loading_contact       = trim($request->loading_contact_name . ' — ' . $request->loading_contact_phone, ' — ') ?: '—';
        $shipment->arrival_contact       = trim($request->arrival_contact_name . ' — ' . $request->arrival_contact_phone, ' — ') ?: '—';

        if ($request->filled('initial_price')) {
            $shipment->initial_price = $request->initial_price;
        }
        if ($request->filled('scheduled_date')) {
            $shipment->scheduled_date = $request->scheduled_date;
        }

        $shipment->save();

        $shipment->load([
            'customer.companyProfile',
            'truckType',
            'truckSubType',
            'shipmentType',
            'shipmentNature',
            'pickupCountry',
            'pickupCity',
            'dropoffCountry',
            'dropoffCity'
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Shipment updated successfully',
            'data'    => $shipment
        ], 200);
    }

    /**
     * Change shipment status.
     */
    public function changeStatus(Request $request, $id)
    {
        $shipment = Shipment::find($id);
        if (!$shipment) {
            return response()->json([
                'status'  => false,
                'message' => 'Shipment not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:new,under_review,pending_approval,approved,in_transit,picked_up,out_for_delivery,delivered,completed,rejected,canceled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $shipment->status = $request->status;
        $shipment->save();

        return response()->json([
            'status'  => true,
            'message' => 'Shipment status updated to ' . ucfirst(str_replace('_', ' ', $request->status)) . ' successfully',
            'data'    => $shipment
        ], 200);
    }

    /**
     * Assign a driver to the shipment.
     */
    public function assignDriver(Request $request, $id)
    {
        $shipment = Shipment::find($id);
        if (!$shipment) {
            return response()->json([
                'status'  => false,
                'message' => 'Shipment not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'driver_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $driver = User::where('id', $request->driver_id)->where('role', 'driver')->first();
        if (!$driver) {
            return response()->json([
                'status'  => false,
                'message' => 'The selected user is not a driver.'
            ], 422);
        }

        $shipment->driver_id = $driver->id;
        $shipment->status = 'approved';
        $shipment->save();

        $shipment->load('driver');

        return response()->json([
            'status'  => true,
            'message' => 'Driver assigned to shipment successfully',
            'data'    => $shipment
        ], 200);
    }

    /**
     * Delete shipment.
     */
    public function destroy($id)
    {
        $shipment = Shipment::find($id);
        if (!$shipment) {
            return response()->json([
                'status'  => false,
                'message' => 'Shipment not found'
            ], 404);
        }

        $shipment->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Shipment deleted successfully'
        ], 200);
    }
}
