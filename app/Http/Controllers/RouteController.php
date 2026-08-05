<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Route;
use App\Models\Country;
use App\Models\City;

class RouteController extends Controller
{
    /**
     * Display all fixed routes with statistics & search filter.
     */
    public function AllRoutes(Request $request)
    {
        $query = Route::with([
            'originCountry',
            'originCity',
            'destinationCountry',
            'destinationCity'
        ])->latest();

        if ($request->filled('quote_type')) {
            $query->where('quote_type', $request->quote_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('originCity', function($cq) use ($search) {
                      $cq->where('name_en', 'like', "%{$search}%")->orWhere('name_ar', 'like', "%{$search}%");
                  })
                  ->orWhereHas('destinationCity', function($cq) use ($search) {
                      $cq->where('name_en', 'like', "%{$search}%")->orWhere('name_ar', 'like', "%{$search}%");
                  });
            });
        }

        $routes = $query->get();

        $stats = [
            'total'         => Route::count(),
            'local'         => Route::where('quote_type', 'local')->count(),
            'international' => Route::where('quote_type', 'international')->count(),
            'active'        => Route::where('status', 'active')->count(),
        ];

        return view('admin.backend.route.all_routes', compact('routes', 'stats'));
    }

    /**
     * Show form to create a new fixed route.
     */
    public function AddRoute()
    {
        $countries = Country::where('is_active', 1)->orderBy('name_en', 'asc')->get();
        return view('admin.backend.route.add_route', compact('countries'));
    }

    /**
     * Store new route in database.
     */
    public function StoreRoute(Request $request)
    {
        $request->validate([
            'quote_type'             => 'required|in:local,international',
            'origin_country_id'      => 'required|exists:countries,id',
            'origin_city_id'         => 'required|exists:cities,id',
            'destination_country_id' => 'required|exists:countries,id',
            'destination_city_id'    => 'required|exists:cities,id',
            'estimated_distance'     => 'nullable|numeric|min:0',
            'status'                 => 'required|in:active,inactive',
        ]);

        $route = Route::create([
            'quote_type'             => $request->quote_type,
            'origin_country_id'      => $request->origin_country_id,
            'origin_city_id'         => $request->origin_city_id,
            'destination_country_id' => $request->destination_country_id,
            'destination_city_id'    => $request->destination_city_id,
            'estimated_distance'     => $request->estimated_distance,
            'status'                 => $request->status,
        ]);

        $notification = [
            'message'    => 'Route #' . $route->id . ' Added Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.routes')->with($notification);
    }

    /**
     * Show form to edit route.
     */
    public function EditRoute($id)
    {
        $route     = Route::with(['originCountry', 'originCity', 'destinationCountry', 'destinationCity'])->findOrFail($id);
        $countries = Country::where('is_active', 1)->orderBy('name_en', 'asc')->get();

        $originCities      = City::where('country_id', $route->origin_country_id)->orderBy('name_en', 'asc')->get();
        $destinationCities = City::where('country_id', $route->destination_country_id)->orderBy('name_en', 'asc')->get();

        return view('admin.backend.route.edit_route', compact('route', 'countries', 'originCities', 'destinationCities'));
    }

    /**
     * Update route in database.
     */
    public function UpdateRoute(Request $request)
    {
        $id    = $request->id;
        $route = Route::findOrFail($id);

        $request->validate([
            'quote_type'             => 'required|in:local,international',
            'origin_country_id'      => 'required|exists:countries,id',
            'origin_city_id'         => 'required|exists:cities,id',
            'destination_country_id' => 'required|exists:countries,id',
            'destination_city_id'    => 'required|exists:cities,id',
            'estimated_distance'     => 'nullable|numeric|min:0',
            'status'                 => 'required|in:active,inactive',
        ]);

        $route->update([
            'quote_type'             => $request->quote_type,
            'origin_country_id'      => $request->origin_country_id,
            'origin_city_id'         => $request->origin_city_id,
            'destination_country_id' => $request->destination_country_id,
            'destination_city_id'    => $request->destination_city_id,
            'estimated_distance'     => $request->estimated_distance,
            'status'                 => $request->status,
        ]);

        $notification = [
            'message'    => 'Route #' . $route->id . ' Updated Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.routes')->with($notification);
    }

    /**
     * Delete route from database.
     */
    public function DeleteRoute($id)
    {
        $route = Route::findOrFail($id);
        $route->delete();

        $notification = [
            'message'    => 'Route #' . $id . ' Deleted Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * AJAX: Toggle status active / inactive
     */
    public function ChangeStatusAjax(Request $request)
    {
        $route = Route::findOrFail($request->route_id);
        $route->status = $request->status;
        $route->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Route status updated to ' . ucfirst($request->status) . '.'
        ]);
    }
}
