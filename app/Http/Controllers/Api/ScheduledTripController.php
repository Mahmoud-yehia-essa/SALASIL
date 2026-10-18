<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScheduledTrip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScheduledTripController extends Controller
{
    /**
     * List all scheduled trips with filters.
     */
    public function index(Request $request)
    {
        $query = ScheduledTrip::with([
            'route.originCountry:id,name_ar,name_en,code',
            'route.originCity:id,name_ar,name_en',
            'route.destinationCountry:id,name_ar,name_en,code',
            'route.destinationCity:id,name_ar,name_en',
            'truckType',
            'truckSubType',
            'shipmentType',
            'shipmentNature',
            'driver:id,fname,lname,phone,email,photo,client_code'
        ])->latest();

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Route Filter
        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }

        // Truck Type Filter
        if ($request->filled('truck_type_id')) {
            $query->where('truck_type_id', $request->truck_type_id);
        }

        // Truck Sub-Type Filter
        if ($request->filled('truck_sub_type_id')) {
            $query->where('truck_sub_type_id', $request->truck_sub_type_id);
        }

        // Shipment Type Filter (Trips with matching type or null/general)
        if ($request->filled('shipment_type_id')) {
            $query->where(function($q) use ($request) {
                $q->whereNull('shipment_type_id')
                  ->orWhere('shipment_type_id', $request->shipment_type_id);
            });
        } elseif ($request->filled('shipment_type')) {
            $stName = trim($request->shipment_type);
            $query->where(function($q) use ($stName) {
                $q->whereNull('shipment_type_id')
                  ->orWhereHas('shipmentType', function($sq) use ($stName) {
                      $sq->where('name_en', 'like', "%{$stName}%")
                        ->orWhere('name_ar', 'like', "%{$stName}%");
                  });
            });
        }

        // Shipment Nature Filter (Trips with matching nature or null/general)
        if ($request->filled('shipment_nature_id')) {
            $query->where(function($q) use ($request) {
                $q->whereNull('shipment_nature_id')
                  ->orWhere('shipment_nature_id', $request->shipment_nature_id);
            });
        } elseif ($request->filled('shipment_nature')) {
            $snName = trim($request->shipment_nature);
            $query->where(function($q) use ($snName) {
                $q->whereNull('shipment_nature_id')
                  ->orWhereHas('shipmentNature', function($sq) use ($snName) {
                      $sq->where('name_en', 'like', "%{$snName}%")
                        ->orWhere('name_ar', 'like', "%{$snName}%");
                  });
            });
        }

        // Available Capacity / Number of Trucks Filter
        if ($request->filled('truck_count') || $request->filled('number_of_trucks')) {
            $count = intval($request->truck_count ?: $request->number_of_trucks);
            if ($count > 0) {
                $query->where('available_capacity', '>=', $count);
            }
        }

        // Cargo Weight vs Truck Max Payload Filter (in Tons)
        if ($request->filled('weight')) {
            $cleanW = floatval(preg_replace('/[^0-9.]/', '', $request->weight));
            if ($cleanW > 100) { $cleanW = $cleanW / 1000.0; }
            if ($cleanW > 0) {
                $query->where(function($wq) use ($cleanW) {
                    $wq->whereNull('total_weight_ton')
                       ->orWhere('total_weight_ton', '>=', $cleanW);
                });
            }
        }

        // Driver Filter
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        // Date Range Filters
        if ($request->filled('from_date')) {
            $query->whereDate('trip_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('trip_date', '<=', $request->to_date);
        }

        // Customs Clearance Filter
        if ($request->has('customs_clearance') && $request->customs_clearance !== '') {
            $query->where('customs_clearance', filter_var($request->customs_clearance, FILTER_VALIDATE_BOOLEAN));
        }

        // Origin / From City Filter
        if ($request->filled('from_city') || $request->filled('origin_city')) {
            $from = trim($request->from_city ?: $request->origin_city);
            if (str_contains($from, ' - ')) {
                $from = trim(explode(' - ', $from)[0]);
            }
            $query->whereHas('route.originCity', function ($q) use ($from) {
                $q->where('name_en', 'like', "%{$from}%")
                  ->orWhere('name_ar', 'like', "%{$from}%");
            });
        }

        if ($request->filled('origin_city_id') || $request->filled('from_city_id')) {
            $cityId = $request->origin_city_id ?: $request->from_city_id;
            $query->whereHas('route', function ($q) use ($cityId) {
                $q->where('origin_city_id', $cityId);
            });
        }

        // Destination / To City Filter
        if ($request->filled('to_city') || $request->filled('destination_city')) {
            $to = trim($request->to_city ?: $request->destination_city);
            if (str_contains($to, ' - ')) {
                $to = trim(explode(' - ', $to)[0]);
            }
            $query->whereHas('route.destinationCity', function ($q) use ($to) {
                $q->where('name_en', 'like', "%{$to}%")
                  ->orWhere('name_ar', 'like', "%{$to}%");
            });
        }

        if ($request->filled('destination_city_id') || $request->filled('to_city_id')) {
            $cityId = $request->destination_city_id ?: $request->to_city_id;
            $query->whereHas('route', function ($q) use ($cityId) {
                $q->where('destination_city_id', $cityId);
            });
        }

        // Truck Type by name
        if ($request->filled('truck_type')) {
            $truckType = trim($request->truck_type);
            $query->whereHas('truckType', function ($q) use ($truckType) {
                $q->where('name_en', 'like', "%{$truckType}%")
                  ->orWhere('name_ar', 'like', "%{$truckType}%");
            });
        }

        // Truck Sub-Type by name
        if ($request->filled('truck_sub_type')) {
            $truckSubType = trim($request->truck_sub_type);
            $query->whereHas('truckSubType', function ($q) use ($truckSubType) {
                $q->where('name_en', 'like', "%{$truckSubType}%")
                  ->orWhere('name_ar', 'like', "%{$truckSubType}%");
            });
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('trip_date', 'like', "%{$search}%")
                  ->orWhereHas('driver', function ($dq) use ($search) {
                      $dq->where('fname', 'like', "%{$search}%")
                         ->orWhere('lname', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('route.originCity', function ($cq) use ($search) {
                      $cq->where('name_en', 'like', "%{$search}%")->orWhere('name_ar', 'like', "%{$search}%");
                  })
                  ->orWhereHas('route.destinationCity', function ($cq) use ($search) {
                      $cq->where('name_en', 'like', "%{$search}%")->orWhere('name_ar', 'like', "%{$search}%");
                  });
            });
        }

        $trips = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'status'  => true,
            'message' => 'Scheduled trips retrieved successfully',
            'data'    => $trips
        ], 200);
    }

    /**
     * Get trips assigned to currently authenticated driver.
     */
    public function myTrips(Request $request)
    {
        $user = $request->user();

        $query = ScheduledTrip::with([
            'route.originCountry',
            'route.originCity',
            'route.destinationCountry',
            'route.destinationCity',
            'truckType',
            'truckSubType',
            'shipmentType',
            'shipmentNature'
        ])->where('driver_id', $user->id)->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $trips = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'status'  => true,
            'message' => 'My scheduled trips retrieved successfully',
            'data'    => $trips
        ], 200);
    }

    /**
     * Get statistics overview for scheduled trips.
     */
    public function statistics(Request $request)
    {
        $user = $request->user();
        $query = ScheduledTrip::query();

        if ($user && $user->role === 'driver') {
            $query->where('driver_id', $user->id);
        }

        $stats = [
            'total'      => (clone $query)->count(),
            'published'  => (clone $query)->where('status', 'published')->count(),
            'boarding'   => (clone $query)->where('status', 'boarding')->count(),
            'in_transit' => (clone $query)->where('status', 'in_transit')->count(),
            'completed'  => (clone $query)->where('status', 'completed')->count(),
            'canceled'   => (clone $query)->where('status', 'canceled')->count(),
        ];

        return response()->json([
            'status'  => true,
            'message' => 'Scheduled trip statistics retrieved successfully',
            'data'    => $stats
        ], 200);
    }

    /**
     * Show single scheduled trip details.
     */
    public function show($id)
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
            'driver:id,fname,lname,phone,email,photo,client_code'
        ])->find($id);

        if (!$trip) {
            return response()->json([
                'status'  => false,
                'message' => 'Scheduled trip not found'
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Scheduled trip retrieved successfully',
            'data'    => $trip
        ], 200);
    }

    /**
     * Store new scheduled trip in database.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'route_id'           => 'required|exists:routes,id',
            'truck_type_id'      => 'required|exists:truck_types,id',
            'truck_sub_type_id'  => 'nullable|exists:truck_sub_types,id',
            'shipment_type_id'   => 'nullable|exists:shipment_types,id',
            'shipment_nature_id' => 'nullable|exists:shipment_natures,id',
            'number_of_trucks'   => 'nullable|integer|min:1',
            'total_weight_ton'   => 'nullable|numeric|min:0',
            'driver_id'          => 'nullable|exists:users,id',
            'trip_date'          => 'required|date',
            'trip_time'          => 'nullable',
            'price'              => 'required|numeric|min:0',
            'total_capacity'     => 'required|integer|min:1',
            'available_capacity' => 'required|integer|min:0',
            'status'             => 'required|in:published,boarding,in_transit,completed,canceled',
        ], [
            'route_id.required'           => 'Please select a route.',
            'truck_type_id.required'      => 'Please select a truck type.',
            'trip_date.required'          => 'Please specify the trip departure date.',
            'price.required'              => 'Please specify the trip booking price.',
            'total_capacity.required'     => 'Please specify total capacity.',
            'available_capacity.required' => 'Please specify available capacity.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $trip = ScheduledTrip::create([
            'route_id'           => $request->route_id,
            'truck_type_id'      => $request->truck_type_id,
            'truck_sub_type_id'  => $request->truck_sub_type_id ?: null,
            'shipment_type_id'   => $request->shipment_type_id ?: null,
            'shipment_nature_id' => $request->shipment_nature_id ?: null,
            'number_of_trucks'   => $request->number_of_trucks ?: 1,
            'total_weight_ton'   => $request->total_weight_ton ?: null,
            'driver_id'          => $request->driver_id ?: null,
            'trip_date'          => $request->trip_date,
            'trip_time'          => $request->trip_time,
            'price'              => $request->price,
            'total_capacity'     => $request->total_capacity ?: 1,
            'available_capacity' => $request->available_capacity ?: 1,
            'status'             => $request->status,
            'customs_clearance'  => $request->has('customs_clearance') ? (bool) $request->customs_clearance : true,
            'notes'              => $request->notes ?: null,
            'required_documents' => $request->required_documents ?: null,
        ]);

        $trip->load([
            'route.originCountry',
            'route.originCity',
            'route.destinationCountry',
            'route.destinationCity',
            'truckType',
            'truckSubType',
            'shipmentType',
            'shipmentNature',
            'driver:id,fname,lname,phone,email'
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Scheduled trip created successfully',
            'data'    => $trip
        ], 201);
    }

    /**
     * Update scheduled trip in database.
     */
    public function update(Request $request, $id)
    {
        $trip = ScheduledTrip::find($id);
        if (!$trip) {
            return response()->json([
                'status'  => false,
                'message' => 'Scheduled trip not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'route_id'           => 'required|exists:routes,id',
            'truck_type_id'      => 'required|exists:truck_types,id',
            'truck_sub_type_id'  => 'nullable|exists:truck_sub_types,id',
            'shipment_type_id'   => 'nullable|exists:shipment_types,id',
            'shipment_nature_id' => 'nullable|exists:shipment_natures,id',
            'number_of_trucks'   => 'nullable|integer|min:1',
            'total_weight_ton'   => 'nullable|numeric|min:0',
            'driver_id'          => 'nullable|exists:users,id',
            'trip_date'          => 'required|date',
            'trip_time'          => 'nullable',
            'price'              => 'required|numeric|min:0',
            'total_capacity'     => 'required|integer|min:1',
            'available_capacity' => 'required|integer|min:0',
            'status'             => 'required|in:published,boarding,in_transit,completed,canceled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $trip->update([
            'route_id'           => $request->route_id,
            'truck_type_id'      => $request->truck_type_id,
            'truck_sub_type_id'  => $request->truck_sub_type_id ?: null,
            'shipment_type_id'   => $request->has('shipment_type_id') ? ($request->shipment_type_id ?: null) : $trip->shipment_type_id,
            'shipment_nature_id' => $request->has('shipment_nature_id') ? ($request->shipment_nature_id ?: null) : $trip->shipment_nature_id,
            'number_of_trucks'   => $request->number_of_trucks ?: $trip->number_of_trucks,
            'total_weight_ton'   => $request->total_weight_ton ?: $trip->total_weight_ton,
            'driver_id'          => $request->filled('driver_id') ? $request->driver_id : $trip->driver_id,
            'trip_date'          => $request->trip_date,
            'trip_time'          => $request->trip_time,
            'price'              => $request->price,
            'total_capacity'     => $request->total_capacity ?: $trip->total_capacity,
            'available_capacity' => $request->available_capacity ?: $trip->available_capacity,
            'status'             => $request->status,
            'customs_clearance'  => $request->has('customs_clearance') ? (bool) $request->customs_clearance : $trip->customs_clearance,
            'notes'              => $request->has('notes') ? $request->notes : $trip->notes,
            'required_documents' => $request->has('required_documents') ? $request->required_documents : $trip->required_documents,
        ]);

        $trip->load([
            'route.originCountry',
            'route.originCity',
            'route.destinationCountry',
            'route.destinationCity',
            'truckType',
            'truckSubType',
            'shipmentType',
            'shipmentNature',
            'driver:id,fname,lname,phone,email'
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Scheduled trip updated successfully',
            'data'    => $trip
        ], 200);
    }

    /**
     * Change status of a scheduled trip.
     */
    public function changeStatus(Request $request, $id)
    {
        $trip = ScheduledTrip::find($id);
        if (!$trip) {
            return response()->json([
                'status'  => false,
                'message' => 'Scheduled trip not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:published,boarding,in_transit,completed,canceled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $trip->status = $request->status;
        $trip->save();

        return response()->json([
            'status'  => true,
            'message' => 'Scheduled trip status updated to ' . ucfirst(str_replace('_', ' ', $request->status)) . ' successfully',
            'data'    => $trip
        ], 200);
    }

    /**
     * Assign driver to scheduled trip.
     */
    public function assignDriver(Request $request, $id)
    {
        $trip = ScheduledTrip::find($id);
        if (!$trip) {
            return response()->json([
                'status'  => false,
                'message' => 'Scheduled trip not found'
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

        $trip->driver_id = $driver->id;
        $trip->save();

        $trip->load('driver:id,fname,lname,phone,email,photo,client_code');

        return response()->json([
            'status'  => true,
            'message' => 'Driver assigned to scheduled trip successfully',
            'data'    => $trip
        ], 200);
    }

    /**
     * Delete scheduled trip.
     */
    public function destroy($id)
    {
        $trip = ScheduledTrip::find($id);
        if (!$trip) {
            return response()->json([
                'status'  => false,
                'message' => 'Scheduled trip not found'
            ], 404);
        }

        $trip->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Scheduled trip deleted successfully'
        ], 200);
    }
}
