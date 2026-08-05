<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\User;
use App\Models\ShipmentType;
use App\Models\ShipmentNature;
use App\Models\TruckType;
use App\Models\TruckSubType;
use App\Models\Country;
use App\Models\City;
use App\Models\Invoice;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    /**
     * Show the Add New Shipment multi-step wizard form.
     */
    public function AddShipment()
    {
        $customers       = User::whereIn('role', ['individual_customer', 'company_customer'])
                               ->with('companyProfile')
                               ->orderBy('fname', 'asc')
                               ->get();
        $shipmentTypes   = ShipmentType::orderBy('id', 'asc')->get();
        $shipmentNatures = ShipmentNature::orderBy('id', 'asc')->get();
        $truckTypes      = TruckType::orderBy('id', 'asc')->get();
        $countries       = Country::where('is_active', 1)->orderBy('name_en', 'asc')->get();

        return view('admin.backend.shipment.add_shipment', compact(
            'customers',
            'shipmentTypes',
            'shipmentNatures',
            'truckTypes',
            'countries'
        ));
    }

    /**
     * Store the new shipment order in the database.
     */
    public function StoreShipment(Request $request)
    {
        $request->validate([
            'customer_id'           => 'required|exists:users,id',
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
            'pickup_country_id'     => 'nullable',
            'pickup_city_id'        => 'nullable',
            'pickup_area'           => 'nullable|string|max:255',
            'pickup_address'        => 'nullable|string|max:255',
            'pickup_lat'            => 'nullable|numeric',
            'pickup_lng'            => 'nullable|numeric',
            'dropoff_country_id'    => 'nullable',
            'dropoff_city_id'       => 'nullable',
            'dropoff_area'          => 'nullable|string|max:255',
            'dropoff_address'       => 'nullable|string|max:255',
            'dropoff_lat'           => 'nullable|numeric',
            'dropoff_lng'           => 'nullable|numeric',
            'loading_contact_name'  => 'nullable|string|max:255',
            'loading_contact_phone' => 'nullable|string|max:255',
            'arrival_contact_name'  => 'nullable|string|max:255',
            'arrival_contact_phone' => 'nullable|string|max:255',
            'initial_price'         => 'nullable|numeric|min:0',
        ], [
            'customer_id.required'     => 'Please select a customer in Step 1.',
            'customer_id.exists'       => 'The selected customer is invalid.',
        ]);

        $shipment = new Shipment();

        // Step 1 — Customer
        $shipment->customer_id = $request->customer_id;

        // Step 2 — Shipment Details
        $shipment->shipment_name        = $request->shipment_name ?: 'General Cargo Shipment';
        $shipment->shipment_description = $request->shipment_description;
        $shipment->shipment_type_id     = $request->shipment_type_id ?: null;
        $shipment->shipment_nature_id   = $request->shipment_nature_id ?: null;
        $shipment->length               = $request->length ?: null;
        $shipment->width                = $request->width ?: null;
        $shipment->height               = $request->height ?: null;
        $shipment->weight               = $request->weight ?: null;
        $shipment->packages_count       = $request->packages_count ?? 1;
        $shipment->goods_description    = $request->goods_description;
        $shipment->hs_code              = $request->hs_code ?: null;
        $shipment->hs_code_description  = $request->hs_code_description ?: null;

        // Step 3 — Truck Type
        $shipment->truck_type_id     = $request->truck_type_id ?: null;
        $shipment->truck_sub_type_id = $request->truck_sub_type_id ?: null;

        // Step 4 — Pickup Location
        $shipment->pickup_country_id = $request->pickup_country_id ?: null;
        $shipment->pickup_city_id    = $request->pickup_city_id ?: null;
        $shipment->pickup_area       = $request->pickup_area;
        $shipment->pickup_address    = $request->pickup_address ?: ($request->pickup_area ?: 'Pickup Location Pin');
        $shipment->pickup_lat        = $request->pickup_lat ?: null;
        $shipment->pickup_lng        = $request->pickup_lng ?: null;

        // Step 5 — Delivery Location
        $shipment->dropoff_country_id = $request->dropoff_country_id ?: null;
        $shipment->dropoff_city_id    = $request->dropoff_city_id ?: null;
        $shipment->dropoff_area       = $request->dropoff_area;
        $shipment->dropoff_address    = $request->dropoff_address ?: ($request->dropoff_area ?: 'Delivery Location Pin');
        $shipment->dropoff_lat        = $request->dropoff_lat ?: null;
        $shipment->dropoff_lng        = $request->dropoff_lng ?: null;

        // Step 6 — Contacts
        $shipment->loading_contact_name  = $request->loading_contact_name;
        $shipment->loading_contact_phone = $request->loading_contact_phone;
        $shipment->arrival_contact_name  = $request->arrival_contact_name;
        $shipment->arrival_contact_phone = $request->arrival_contact_phone;
        // Backward-compat combined columns
        $shipment->loading_contact = trim($request->loading_contact_name . ' — ' . $request->loading_contact_phone, ' — ') ?: '—';
        $shipment->arrival_contact = trim($request->arrival_contact_name . ' — ' . $request->arrival_contact_phone, ' — ') ?: '—';

        // Step 7 — Pricing
        $shipment->initial_price = $request->initial_price ?: null;

        // Defaults
        $shipment->status         = 'new';
        $shipment->payment_status = 'unpaid';

        $shipment->save();

        // ── Auto Generate Invoice for Shipment Order ──
        $baseAmount = (float) ($request->initial_price ?: 0.00);

        Invoice::create([
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

        $notification = [
            'message'    => 'Shipment Order #' . $shipment->id . ' Created & Invoice Issued Successfully!',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.shipments')->with($notification);
    }

    /**
     * Show form to edit an existing shipment order.
     */
    public function EditShipment($id)
    {
        $shipment = Shipment::with([
            'customer.companyProfile',
            'truckType',
            'truckSubType',
            'pickupCountry',
            'pickupCity',
            'dropoffCountry',
            'dropoffCity'
        ])->findOrFail($id);

        $customers       = User::whereIn('role', ['individual_customer', 'company_customer'])
                               ->with('companyProfile')
                               ->orderBy('fname', 'asc')
                               ->get();
        $shipmentTypes   = ShipmentType::orderBy('id', 'asc')->get();
        $shipmentNatures = ShipmentNature::orderBy('id', 'asc')->get();
        $truckTypes      = TruckType::orderBy('id', 'asc')->get();
        $countries       = Country::where('is_active', 1)->orderBy('name_en', 'asc')->get();

        $pickupCities = $shipment->pickup_country_id 
            ? City::where('country_id', $shipment->pickup_country_id)->where('is_active', 1)->orderBy('name_en', 'asc')->get() 
            : collect();

        $dropoffCities = $shipment->dropoff_country_id 
            ? City::where('country_id', $shipment->dropoff_country_id)->where('is_active', 1)->orderBy('name_en', 'asc')->get() 
            : collect();

        $truckSubTypes = $shipment->truck_type_id 
            ? TruckSubType::where('truck_type_id', $shipment->truck_type_id)->orderBy('id', 'asc')->get() 
            : collect();

        return view('admin.backend.shipment.edit_shipment', compact(
            'shipment',
            'customers',
            'shipmentTypes',
            'shipmentNatures',
            'truckTypes',
            'countries',
            'pickupCities',
            'dropoffCities',
            'truckSubTypes'
        ));
    }

    /**
     * Update existing shipment order in database.
     */
    public function UpdateShipment(Request $request)
    {
        $id = $request->id;
        $shipment = Shipment::findOrFail($id);

        $request->validate([
            'customer_id'           => 'required|exists:users,id',
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
            'pickup_country_id'     => 'nullable',
            'pickup_city_id'        => 'nullable',
            'pickup_area'           => 'nullable|string|max:255',
            'pickup_address'        => 'required|string|max:255',
            'pickup_lat'            => 'nullable|numeric',
            'pickup_lng'            => 'nullable|numeric',
            'dropoff_country_id'    => 'nullable',
            'dropoff_city_id'       => 'nullable',
            'dropoff_area'          => 'nullable|string|max:255',
            'dropoff_address'       => 'required|string|max:255',
            'dropoff_lat'           => 'nullable|numeric',
            'dropoff_lng'           => 'nullable|numeric',
            'loading_contact_name'  => 'nullable|string|max:255',
            'loading_contact_phone' => 'nullable|string|max:255',
            'arrival_contact_name'  => 'nullable|string|max:255',
            'arrival_contact_phone' => 'nullable|string|max:255',
            'initial_price'         => 'nullable|numeric|min:0',
        ], [
            'customer_id.required'     => 'Please select a customer.',
            'pickup_address.required'  => 'Pickup address is required.',
            'dropoff_address.required' => 'Delivery address is required.',
        ]);

        // Step 1 — Customer
        $shipment->customer_id = $request->customer_id;

        // Step 2 — Shipment Details
        $shipment->shipment_name        = $request->shipment_name;
        $shipment->shipment_description = $request->shipment_description;
        $shipment->shipment_type_id     = $request->shipment_type_id ?: null;
        $shipment->shipment_nature_id   = $request->shipment_nature_id ?: null;
        $shipment->length               = $request->length ?: null;
        $shipment->width                = $request->width ?: null;
        $shipment->height               = $request->height ?: null;
        $shipment->weight               = $request->weight ?: null;
        $shipment->packages_count       = $request->packages_count ?? 1;
        $shipment->goods_description    = $request->goods_description;
        $shipment->hs_code              = $request->hs_code ?: null;
        $shipment->hs_code_description  = $request->hs_code_description ?: null;

        // Step 3 — Truck Type
        $shipment->truck_type_id     = $request->truck_type_id ?: null;
        $shipment->truck_sub_type_id = $request->truck_sub_type_id ?: null;

        // Step 4 — Pickup Location
        $shipment->pickup_country_id = $request->pickup_country_id ?: null;
        $shipment->pickup_city_id    = $request->pickup_city_id ?: null;
        $shipment->pickup_area       = $request->pickup_area;
        $shipment->pickup_address    = $request->pickup_address;
        $shipment->pickup_lat        = $request->pickup_lat ?: null;
        $shipment->pickup_lng        = $request->pickup_lng ?: null;

        // Step 5 — Delivery Location
        $shipment->dropoff_country_id = $request->dropoff_country_id ?: null;
        $shipment->dropoff_city_id    = $request->dropoff_city_id ?: null;
        $shipment->dropoff_area       = $request->dropoff_area;
        $shipment->dropoff_address    = $request->dropoff_address;
        $shipment->dropoff_lat        = $request->dropoff_lat ?: null;
        $shipment->dropoff_lng        = $request->dropoff_lng ?: null;

        // Step 6 — Contacts
        $shipment->loading_contact_name  = $request->loading_contact_name;
        $shipment->loading_contact_phone = $request->loading_contact_phone;
        $shipment->arrival_contact_name  = $request->arrival_contact_name;
        $shipment->arrival_contact_phone = $request->arrival_contact_phone;
        $shipment->loading_contact = trim($request->loading_contact_name . ' — ' . $request->loading_contact_phone, ' — ') ?: '—';
        $shipment->arrival_contact = trim($request->arrival_contact_name . ' — ' . $request->arrival_contact_phone, ' — ') ?: '—';

        // Step 7 — Pricing
        $shipment->initial_price = $request->initial_price ?: null;

        $shipment->save();

        $notification = [
            'message'    => 'Shipment Order #' . $shipment->id . ' Updated Successfully!',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.shipments')->with($notification);
    }

    /**
     * AJAX: Return full user data (with company profile) as JSON.
     */
    public function GetUserDataAjax($id)
    {
        $user = User::with('companyProfile')->find($id);

        if (! $user) {
            return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'user'   => [
                'id'           => $user->id,
                'fname'        => $user->fname,
                'lname'        => $user->lname,
                'email'        => $user->email,
                'phone'        => $user->phone,
                'country_code' => $user->country_code,
                'address'      => $user->address,
                'role'         => $user->role,
                'status'       => $user->status,
                'photo'        => $user->photo,
                'dateofbirth'  => $user->dateofbirth ? $user->dateofbirth->format('Y-m-d') : null,
                'company'      => $user->companyProfile ? [
                    'company_name'            => $user->companyProfile->company_name,
                    'commercial_register'     => $user->companyProfile->commercial_register,
                    'civil_id'                => $user->companyProfile->civil_id,
                    'tax_number'              => $user->companyProfile->tax_number,
                    'representative_name'     => $user->companyProfile->representative_name,
                    'representative_position' => $user->companyProfile->representative_position,
                    'representative_phone'    => $user->companyProfile->representative_phone,
                    'verification_status'     => $user->companyProfile->verification_status,
                ] : null,
            ],
        ]);
    }

    /**
     * Display all shipments listing page with filters and stats.
     */
    public function AllShipments(Request $request)
    {
        $query = Shipment::with([
            'customer.companyProfile',
            'driver',
            'truckType',
            'truckSubType',
            'shipmentType',
            'shipmentNature',
            'pickupCountry',
            'pickupCity',
            'dropoffCountry',
            'dropoffCity'
        ])->latest();

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Payment Status Filter
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Customer Filter
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('shipment_name', 'like', "%{$search}%")
                  ->orWhere('pickup_address', 'like', "%{$search}%")
                  ->orWhere('dropoff_address', 'like', "%{$search}%")
                  ->orWhere('loading_contact_name', 'like', "%{$search}%")
                  ->orWhere('loading_contact_phone', 'like', "%{$search}%")
                  ->orWhere('arrival_contact_name', 'like', "%{$search}%")
                  ->orWhere('arrival_contact_phone', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('fname', 'like', "%{$search}%")
                         ->orWhere('lname', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $shipments = $query->get();

        // Calculate KPI Statistics
        $stats = [
            'total'     => Shipment::count(),
            'new'       => Shipment::where('status', 'new')->count(),
            'approved'  => Shipment::where('status', 'approved')->count(),
            'revenue'   => Shipment::sum('initial_price') ?? 0,
        ];

        $customers = User::whereIn('role', ['individual_customer', 'company_customer'])
                         ->orderBy('fname', 'asc')
                         ->get(['id', 'fname', 'lname', 'role']);

        return view('admin.backend.shipment.all_shipments', compact('shipments', 'stats', 'customers'));
    }

    /**
     * AJAX: Fetch complete shipment details (all 40+ fields) for the modal drawer.
     */
    public function GetShipmentDetailsAjax($id)
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
            'dropoffCity'
        ])->find($id);

        if (!$shipment) {
            return response()->json(['status' => 'error', 'message' => 'Shipment not found.'], 404);
        }

        return response()->json([
            'status'   => 'success',
            'shipment' => [
                'id'                   => $shipment->id,
                'shipment_name'        => $shipment->shipment_name ?? '—',
                'shipment_description' => $shipment->shipment_description ?? '—',
                'status'               => $shipment->status,
                'payment_status'       => $shipment->payment_status,
                'payment_method'       => $shipment->payment_method ?? '—',
                'initial_price'        => $shipment->initial_price ? number_format($shipment->initial_price, 2) : '0.00',
                'is_fragile'           => (bool) $shipment->is_fragile,
                'goods_description'    => $shipment->goods_description ?? '—',
                'hs_code'              => $shipment->hs_code ?? null,
                'hs_code_description'  => $shipment->hs_code_description ?? null,
                'weight'               => $shipment->weight ? number_format($shipment->weight, 2) : '—',
                'packages_count'       => $shipment->packages_count ?? 1,
                'length'               => $shipment->length ?? '—',
                'width'                => $shipment->width ?? '—',
                'height'               => $shipment->height ?? '—',
                'dimensions'           => ($shipment->length || $shipment->width || $shipment->height) 
                                          ? ($shipment->length ?? '0') . ' × ' . ($shipment->width ?? '0') . ' × ' . ($shipment->height ?? '0') . ' cm' 
                                          : '—',

                // Classifications
                'shipment_type'   => $shipment->shipmentType->name_en ?? $shipment->shipmentType->name_ar ?? '—',
                'shipment_nature' => $shipment->shipmentNature->name_en ?? $shipment->shipmentNature->name_ar ?? '—',
                'truck_type'      => $shipment->truckType->name_en ?? $shipment->truckType->name_ar ?? '—',
                'truck_sub_type'  => $shipment->truckSubType->name_en ?? $shipment->truckSubType->name_ar ?? '—',

                // Customer
                'customer' => $shipment->customer ? [
                    'id'           => $shipment->customer->id,
                    'name'         => $shipment->customer->fname . ' ' . ($shipment->customer->lname ?? ''),
                    'email'        => $shipment->customer->email,
                    'phone'        => ($shipment->customer->country_code ?? '') . ' ' . $shipment->customer->phone,
                    'role'         => $shipment->customer->role,
                    'company_name' => $shipment->customer->companyProfile->company_name ?? null,
                ] : null,

                // Driver
                'driver' => $shipment->driver ? [
                    'id'    => $shipment->driver->id,
                    'name'  => $shipment->driver->fname . ' ' . ($shipment->driver->lname ?? ''),
                    'phone' => $shipment->driver->phone,
                ] : null,

                // Pickup Location
                'pickup' => [
                    'country' => $shipment->pickupCountry->name_en ?? '—',
                    'city'    => $shipment->pickupCity->name_en ?? '—',
                    'area'    => $shipment->pickup_area ?? '—',
                    'address' => $shipment->pickup_address ?? '—',
                    'lat'     => $shipment->pickup_lat,
                    'lng'     => $shipment->pickup_lng,
                ],

                // Dropoff Location
                'dropoff' => [
                    'country' => $shipment->dropoffCountry->name_en ?? '—',
                    'city'    => $shipment->dropoffCity->name_en ?? '—',
                    'area'    => $shipment->dropoff_area ?? '—',
                    'address' => $shipment->dropoff_address ?? '—',
                    'lat'     => $shipment->dropoff_lat,
                    'lng'     => $shipment->dropoff_lng,
                ],

                // Contacts
                'loading_contact_name'  => $shipment->loading_contact_name ?? '—',
                'loading_contact_phone' => $shipment->loading_contact_phone ?? '—',
                'arrival_contact_name'  => $shipment->arrival_contact_name ?? '—',
                'arrival_contact_phone' => $shipment->arrival_contact_phone ?? '—',

                // Tracking & Timestamps
                'created_at'                => $shipment->created_at ? $shipment->created_at->format('Y-m-d H:i:s') : '—',
                'scheduled_date'            => $shipment->scheduled_date ? date('Y-m-d H:i', strtotime($shipment->scheduled_date)) : '—',
                'driver_arrival_at_loading' => $shipment->driver_arrival_at_loading ? date('Y-m-d H:i:s', strtotime($shipment->driver_arrival_at_loading)) : '—',
                'loading_start_at'          => $shipment->loading_start_at ? date('Y-m-d H:i:s', strtotime($shipment->loading_start_at)) : '—',
                'loading_end_at'            => $shipment->loading_end_at ? date('Y-m-d H:i:s', strtotime($shipment->loading_end_at)) : '—',
                'trip_start_at'             => $shipment->trip_start_at ? date('Y-m-d H:i:s', strtotime($shipment->trip_start_at)) : '—',
                'unloading_start_at'        => $shipment->unloading_start_at ? date('Y-m-d H:i:s', strtotime($shipment->unloading_start_at)) : '—',
                'unloading_end_at'          => $shipment->unloading_end_at ? date('Y-m-d H:i:s', strtotime($shipment->unloading_end_at)) : '—',
                'delay_reason'              => $shipment->delay_reason ?? '—',
            ]
        ]);
    }

    /**
     * AJAX: Change status of a shipment.
     */
    public function ChangeStatusAjax(Request $request)
    {
        $request->validate([
            'shipment_id' => 'required|exists:shipments,id',
            'status'      => 'required|in:new,under_review,pending_approval,approved,rejected,canceled',
        ]);

        $shipment = Shipment::findOrFail($request->shipment_id);
        $shipment->status = $request->status;
        $shipment->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Shipment status updated successfully to ' . ucfirst(str_replace('_', ' ', $request->status)) . '.'
        ]);
    }

    /**
     * Delete shipment record.
     */
    public function DeleteShipment($id)
    {
        $shipment = Shipment::findOrFail($id);
        $shipment->delete();

        $notification = [
            'message'    => 'Shipment Order #' . $id . ' Deleted Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * AJAX: Return truck sub-types for a given truck type.
     */
    public function GetSubTypesAjax($truck_type_id)
    {
        $subTypes = TruckSubType::where('truck_type_id', $truck_type_id)
                                ->orderBy('id', 'asc')
                                ->get(['id', 'name_en', 'name_ar']);

        return response()->json($subTypes);
    }

    /**
     * AJAX: Return active cities for a given country.
     */
    public function GetCitiesAjax($country_id)
    {
        $cities = City::where('country_id', $country_id)
                      ->where('is_active', 1)
                      ->orderBy('name_en', 'asc')
                      ->get(['id', 'name_en', 'name_ar']);

        return response()->json($cities);
    }
}
