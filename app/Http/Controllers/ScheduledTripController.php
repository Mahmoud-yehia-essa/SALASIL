<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScheduledTrip;
use App\Models\Route;
use App\Models\TruckType;
use App\Models\TruckSubType;
use App\Models\User;
use App\Models\ShipmentType;
use App\Models\ShipmentNature;

class ScheduledTripController extends Controller
{
    /**
     * Display all scheduled trips with stats & filter search bar.
     */
    public function AllTrips(Request $request)
    {
        $query = ScheduledTrip::with([
            'route.originCountry',
            'route.originCity',
            'route.destinationCountry',
            'route.destinationCity',
            'truckType',
            'truckSubType',
            'shipmentType',
            'shipmentNature',
            'driver.driverProfile'
        ])->latest();

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by Route
        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }

        // Filter by Truck Type
        if ($request->filled('truck_type_id')) {
            $query->where('truck_type_id', $request->truck_type_id);
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
                  ->orWhere('trip_date', 'like', "%{$search}%")
                  ->orWhereHas('driver', function($dq) use ($search) {
                      $dq->where('fname', 'like', "%{$search}%")
                         ->orWhere('lname', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('route.originCity', function($cq) use ($search) {
                      $cq->where('name_en', 'like', "%{$search}%")->orWhere('name_ar', 'like', "%{$search}%");
                  })
                  ->orWhereHas('route.destinationCity', function($cq) use ($search) {
                      $cq->where('name_en', 'like', "%{$search}%")->orWhere('name_ar', 'like', "%{$search}%");
                  });
            });
        }

        $trips = $query->get();

        // Statistics
        $stats = [
            'total'      => ScheduledTrip::count(),
            'published'  => ScheduledTrip::where('status', 'published')->count(),
            'boarding'   => ScheduledTrip::where('status', 'boarding')->count(),
            'in_transit' => ScheduledTrip::where('status', 'in_transit')->count(),
            'completed'  => ScheduledTrip::where('status', 'completed')->count(),
            'canceled'   => ScheduledTrip::where('status', 'canceled')->count(),
        ];

        $routes     = Route::with(['originCity', 'destinationCity'])->where('status', 'active')->get();
        $truckTypes = TruckType::orderBy('id', 'asc')->get();
        $drivers    = User::where('role', 'driver')->orderBy('fname', 'asc')->get(['id', 'fname', 'lname', 'phone']);

        return view('admin.backend.scheduled_trip.all_trips', compact('trips', 'stats', 'routes', 'truckTypes', 'drivers'));
    }

    /**
     * Show form to create a new scheduled trip.
     */
    public function AddTrip()
    {
        $routes          = Route::with(['originCountry', 'originCity', 'destinationCountry', 'destinationCity'])->where('status', 'active')->get();
        $truckTypes      = TruckType::orderBy('id', 'asc')->get();
        $drivers         = User::where('role', 'driver')->orderBy('fname', 'asc')->get();
        $shipmentTypes   = ShipmentType::where('is_active', 1)->orderBy('id', 'asc')->get();
        $shipmentNatures = ShipmentNature::where('is_active', 1)->orderBy('id', 'asc')->get();

        return view('admin.backend.scheduled_trip.add_trip', compact('routes', 'truckTypes', 'drivers', 'shipmentTypes', 'shipmentNatures'));
    }

    /**
     * Store new scheduled trip in database.
     */
    public function StoreTrip(Request $request)
    {
        $request->validate([
            'route_id'           => 'required|exists:routes,id',
            'truck_type_id'      => 'required|exists:truck_types,id',
            'truck_sub_type_id'  => 'nullable|exists:truck_sub_types,id',
            'number_of_trucks'   => 'required|integer|min:1',
            'total_weight_ton'   => 'nullable|numeric|min:0',
            'driver_id'          => 'nullable|exists:users,id',
            'trip_date'          => 'nullable|date',
            'trip_time'          => 'nullable',
            'price'              => 'required|numeric|min:0',
            'total_capacity'     => 'nullable|integer|min:1',
            'available_capacity' => 'nullable|integer|min:0',
            'status'             => 'required|in:published,boarding,in_transit,completed,canceled',
            'shipment_type_id'   => 'nullable|exists:shipment_types,id',
            'shipment_nature_id' => 'nullable|exists:shipment_natures,id',
            'customs_clearance'  => 'nullable|boolean',
            'notes'              => 'nullable|string',
            'required_documents' => 'nullable|array',
        ], [
            'route_id.required'           => 'Please select a Route (يرجى اختيار مسار الرحلة).',
            'route_id.exists'             => 'Selected route is invalid (مسار الرحلة المختار غير موجود).',
            'truck_type_id.required'      => 'Please select a Truck Type (يرجى اختيار نوع الشاحنة).',
            'truck_type_id.exists'        => 'Selected truck type is invalid (نوع الشاحنة المختار غير صحيح).',
            'number_of_trucks.required'   => 'Number of trucks is required (عدد الشاحنات مطلوب).',
            'number_of_trucks.min'        => 'Number of trucks must be at least 1 (عدد الشاحنات لا يقل عن 1).',
            'price.required'              => 'Trip price is required (سعر الرحلة مطلوب).',
            'price.numeric'               => 'Price must be a valid number (سعر الرحلة يجب أن يكون رقماً).',
            'status.required'             => 'Status is required (حالة الرحلة مطلوبة).',
        ]);

        $docs = $request->filled('required_documents') ? array_values(array_filter($request->required_documents)) : null;

        $trip = ScheduledTrip::create([
            'route_id'           => $request->route_id,
            'truck_type_id'      => $request->truck_type_id,
            'truck_sub_type_id'  => $request->truck_sub_type_id ?: null,
            'shipment_type_id'   => $request->shipment_type_id ?: null,
            'shipment_nature_id' => $request->shipment_nature_id ?: null,
            'number_of_trucks'   => $request->number_of_trucks ?: 1,
            'total_weight_ton'   => $request->total_weight_ton ?: null,
            'driver_id'          => $request->driver_id ?: null,
            'trip_date'          => $request->trip_date ?: null,
            'trip_time'          => $request->trip_time ?: null,
            'price'              => $request->price,
            'total_capacity'     => $request->filled('total_capacity') ? $request->total_capacity : 9999,
            'available_capacity' => $request->filled('available_capacity') ? $request->available_capacity : 9999,
            'status'             => $request->status,
            'customs_clearance'  => $request->has('customs_clearance') ? (bool) $request->customs_clearance : true,
            'notes'              => $request->notes ?: null,
            'required_documents' => $docs,
        ]);

        $notification = [
            'message'    => 'Scheduled Trip #' . $trip->id . ' Added Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.scheduled.trips')->with($notification);
    }

    /**
     * Show form to edit scheduled trip.
     */
    public function EditTrip($id)
    {
        $trip = ScheduledTrip::with([
            'route.originCountry',
            'route.originCity',
            'route.destinationCountry',
            'route.destinationCity',
            'truckType',
            'truckSubType',
            'driver'
        ])->findOrFail($id);

        $routes          = Route::with(['originCountry', 'originCity', 'destinationCountry', 'destinationCity'])->where('status', 'active')->get();
        $truckTypes      = TruckType::orderBy('id', 'asc')->get();
        $truckSubTypes   = TruckSubType::where('truck_type_id', $trip->truck_type_id)->where('is_active', 1)->get();
        $drivers         = User::where('role', 'driver')->orderBy('fname', 'asc')->get();
        $shipmentTypes   = ShipmentType::where('is_active', 1)->orderBy('id', 'asc')->get();
        $shipmentNatures = ShipmentNature::where('is_active', 1)->orderBy('id', 'asc')->get();

        return view('admin.backend.scheduled_trip.edit_trip', compact('trip', 'routes', 'truckTypes', 'truckSubTypes', 'drivers', 'shipmentTypes', 'shipmentNatures'));
    }

    /**
     * Update scheduled trip in database.
     */
    public function UpdateTrip(Request $request)
    {
        $id   = $request->id;
        $trip = ScheduledTrip::findOrFail($id);

        $request->validate([
            'route_id'           => 'required|exists:routes,id',
            'truck_type_id'      => 'required|exists:truck_types,id',
            'truck_sub_type_id'  => 'nullable|exists:truck_sub_types,id',
            'number_of_trucks'   => 'required|integer|min:1',
            'total_weight_ton'   => 'nullable|numeric|min:0',
            'driver_id'          => 'nullable|exists:users,id',
            'trip_date'          => 'nullable|date',
            'trip_time'          => 'nullable',
            'price'              => 'required|numeric|min:0',
            'total_capacity'     => 'nullable|integer|min:1',
            'available_capacity' => 'nullable|integer|min:0',
            'status'             => 'required|in:published,boarding,in_transit,completed,canceled',
            'shipment_type_id'   => 'nullable|exists:shipment_types,id',
            'shipment_nature_id' => 'nullable|exists:shipment_natures,id',
            'customs_clearance'  => 'nullable|boolean',
            'notes'              => 'nullable|string',
            'required_documents' => 'nullable|array',
        ], [
            'route_id.required'           => 'Please select a Route (يرجى اختيار مسار الرحلة).',
            'route_id.exists'             => 'Selected route is invalid (مسار الرحلة المختار غير موجود).',
            'truck_type_id.required'      => 'Please select a Truck Type (يرجى اختيار نوع الشاحنة).',
            'truck_type_id.exists'        => 'Selected truck type is invalid (نوع الشاحنة المختار غير صحيح).',
            'number_of_trucks.required'   => 'Number of trucks is required (عدد الشاحنات مطلوب).',
            'number_of_trucks.min'        => 'Number of trucks must be at least 1 (عدد الشاحنات لا يقل عن 1).',
            'price.required'              => 'Trip price is required (سعر الرحلة مطلوب).',
            'price.numeric'               => 'Price must be a valid number (سعر الرحلة يجب أن يكون رقماً).',
            'status.required'             => 'Status is required (حالة الرحلة مطلوبة).',
        ]);

        $docs = $request->filled('required_documents') ? array_values(array_filter($request->required_documents)) : null;

        $trip->update([
            'route_id'           => $request->route_id,
            'truck_type_id'      => $request->truck_type_id,
            'truck_sub_type_id'  => $request->truck_sub_type_id ?: null,
            'shipment_type_id'   => $request->shipment_type_id ?: null,
            'shipment_nature_id' => $request->shipment_nature_id ?: null,
            'number_of_trucks'   => $request->number_of_trucks ?: 1,
            'total_weight_ton'   => $request->total_weight_ton ?: null,
            'driver_id'          => $request->driver_id ?: null,
            'trip_date'          => $request->trip_date ?: null,
            'trip_time'          => $request->trip_time ?: null,
            'price'              => $request->price,
            'total_capacity'     => $request->filled('total_capacity') ? $request->total_capacity : 9999,
            'available_capacity' => $request->filled('available_capacity') ? $request->available_capacity : 9999,
            'status'             => $request->status,
            'customs_clearance'  => $request->has('customs_clearance') ? (bool) $request->customs_clearance : true,
            'notes'              => $request->notes ?: null,
            'required_documents' => $docs,
        ]);

        $notification = [
            'message'    => 'Scheduled Trip #' . $trip->id . ' Updated Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.scheduled.trips')->with($notification);
    }

    /**
     * Delete scheduled trip from database.
     */
    public function DeleteTrip($id)
    {
        $trip = ScheduledTrip::findOrFail($id);
        $trip->delete();

        $notification = [
            'message'    => 'Scheduled Trip #' . $id . ' Deleted Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * AJAX: Change status directly from listing.
     */
    public function ChangeStatusAjax(Request $request)
    {
        $trip = ScheduledTrip::findOrFail($request->trip_id);
        $trip->status = $request->status;
        $trip->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Trip status updated to ' . ucfirst(str_replace('_', ' ', $request->status)) . '.'
        ]);
    }

    /**
     * AJAX: Fetch full details for a scheduled trip for modal drawer.
     */
    public function GetTripDetailsAjax($id)
    {
        $trip = ScheduledTrip::with([
            'route.originCountry',
            'route.originCity',
            'route.destinationCountry',
            'route.destinationCity',
            'truckType',
            'truckSubType',
            'driver.driverProfile'
        ])->find($id);

        if (!$trip) {
            return response()->json(['status' => 'error', 'message' => 'Trip not found.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'trip'   => [
                'id'                 => $trip->id,
                'quote_type'         => ucfirst($trip->route->quote_type ?? 'local'),
                'origin_country'     => $trip->route->originCountry->name_en ?? '—',
                'origin_city'        => $trip->route->originCity->name_en ?? '—',
                'destination_country'=> $trip->route->destinationCountry->name_en ?? '—',
                'destination_city'   => $trip->route->destinationCity->name_en ?? '—',
                'estimated_distance' => $trip->route->estimated_distance ? number_format($trip->route->estimated_distance, 1) . ' km' : '—',

                'truck_type'        => $trip->truckType->name_en ?? '—',
                'truck_sub_type'    => $trip->truckSubType->name_en ?? '—',
                'number_of_trucks'  => $trip->number_of_trucks ?? 1,
                'total_weight_ton'  => $trip->total_weight_ton ? number_format($trip->total_weight_ton, 2) . ' Tons' : '—',

                'driver_name'       => $trip->driver ? ($trip->driver->fname . ' ' . ($trip->driver->lname ?? '')) : 'Unassigned',
                'driver_phone'      => $trip->driver ? $trip->driver->phone : '—',

                'trip_date'         => $trip->trip_date,
                'trip_time'         => $trip->trip_time ? date('h:i A', strtotime($trip->trip_time)) : '—',
                'price'             => number_format($trip->price, 2),
                'total_capacity'    => $trip->total_capacity,
                'available_capacity'=> $trip->available_capacity,
                'customs_clearance' => (bool) $trip->customs_clearance,
                'notes'             => $trip->notes,
                'required_documents'=> $trip->required_documents ?: [],
                'status'            => $trip->status,
                'created_at'        => $trip->created_at ? $trip->created_at->format('Y-m-d H:i') : '—',
            ]
        ]);
    }
}
