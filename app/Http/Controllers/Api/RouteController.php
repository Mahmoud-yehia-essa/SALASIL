<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RouteController extends Controller
{
    /**
     * List all fixed routes with filtering.
     */
    public function index(Request $request)
    {
        $query = Route::with([
            'originCountry:id,name_ar,name_en,code',
            'originCity:id,name_ar,name_en,country_id',
            'destinationCountry:id,name_ar,name_en,code',
            'destinationCity:id,name_ar,name_en,country_id',
        ])->withCount('scheduledTrips')->latest();

        if ($request->filled('quote_type')) {
            $query->where('quote_type', $request->quote_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('origin_country_id')) {
            $query->where('origin_country_id', $request->origin_country_id);
        }

        if ($request->filled('origin_city_id')) {
            $query->where('origin_city_id', $request->origin_city_id);
        }

        if ($request->filled('destination_country_id')) {
            $query->where('destination_country_id', $request->destination_country_id);
        }

        if ($request->filled('destination_city_id')) {
            $query->where('destination_city_id', $request->destination_city_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('originCity', function ($cq) use ($search) {
                      $cq->where('name_en', 'like', "%{$search}%")->orWhere('name_ar', 'like', "%{$search}%");
                  })
                  ->orWhereHas('destinationCity', function ($cq) use ($search) {
                      $cq->where('name_en', 'like', "%{$search}%")->orWhere('name_ar', 'like', "%{$search}%");
                  });
            });
        }

        $routes = $query->get();

        return response()->json([
            'status'  => true,
            'message' => 'Routes retrieved successfully',
            'count'   => $routes->count(),
            'data'    => $routes
        ], 200);
    }

    /**
     * Get statistics overview for routes.
     */
    public function statistics()
    {
        $stats = [
            'total'         => Route::count(),
            'local'         => Route::where('quote_type', 'local')->count(),
            'international' => Route::where('quote_type', 'international')->count(),
            'active'        => Route::where('status', 'active')->count(),
            'inactive'      => Route::where('status', 'inactive')->count(),
        ];

        return response()->json([
            'status'  => true,
            'message' => 'Route statistics retrieved successfully',
            'data'    => $stats
        ], 200);
    }

    /**
     * Show single route with its scheduled trips.
     */
    public function show($id)
    {
        $route = Route::with([
            'originCountry',
            'originCity',
            'destinationCountry',
            'destinationCity',
            'scheduledTrips' => function ($q) {
                $q->latest();
            }
        ])->find($id);

        if (!$route) {
            return response()->json([
                'status'  => false,
                'message' => 'Route not found'
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Route retrieved successfully',
            'data'    => $route
        ], 200);
    }

    /**
     * Store new route in database.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quote_type'             => 'required|in:local,international',
            'origin_country_id'      => 'required|exists:countries,id',
            'origin_city_id'         => 'required|exists:cities,id',
            'destination_country_id' => 'required|exists:countries,id',
            'destination_city_id'    => 'required|exists:cities,id',
            'estimated_distance'     => 'nullable|numeric|min:0',
            'status'                 => 'nullable|in:active,inactive',
        ], [
            'quote_type.required'             => 'Please specify if route is local or international.',
            'origin_country_id.required'      => 'Origin country is required.',
            'origin_city_id.required'         => 'Origin city is required.',
            'destination_country_id.required' => 'Destination country is required.',
            'destination_city_id.required'    => 'Destination city is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $route = Route::create([
            'quote_type'             => $request->quote_type,
            'origin_country_id'      => $request->origin_country_id,
            'origin_city_id'         => $request->origin_city_id,
            'destination_country_id' => $request->destination_country_id,
            'destination_city_id'    => $request->destination_city_id,
            'estimated_distance'     => $request->estimated_distance,
            'status'                 => $request->status ?? 'active',
        ]);

        $route->load(['originCountry', 'originCity', 'destinationCountry', 'destinationCity']);

        return response()->json([
            'status'  => true,
            'message' => 'Route created successfully',
            'data'    => $route
        ], 201);
    }

    /**
     * Update route in database.
     */
    public function update(Request $request, $id)
    {
        $route = Route::find($id);
        if (!$route) {
            return response()->json([
                'status'  => false,
                'message' => 'Route not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'quote_type'             => 'required|in:local,international',
            'origin_country_id'      => 'required|exists:countries,id',
            'origin_city_id'         => 'required|exists:cities,id',
            'destination_country_id' => 'required|exists:countries,id',
            'destination_city_id'    => 'required|exists:cities,id',
            'estimated_distance'     => 'nullable|numeric|min:0',
            'status'                 => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $route->update([
            'quote_type'             => $request->quote_type,
            'origin_country_id'      => $request->origin_country_id,
            'origin_city_id'         => $request->origin_city_id,
            'destination_country_id' => $request->destination_country_id,
            'destination_city_id'    => $request->destination_city_id,
            'estimated_distance'     => $request->estimated_distance,
            'status'                 => $request->status ?? $route->status,
        ]);

        $route->load(['originCountry', 'originCity', 'destinationCountry', 'destinationCity']);

        return response()->json([
            'status'  => true,
            'message' => 'Route updated successfully',
            'data'    => $route
        ], 200);
    }

    /**
     * Toggle route active status.
     */
    public function changeStatus(Request $request, $id)
    {
        $route = Route::find($id);
        if (!$route) {
            return response()->json([
                'status'  => false,
                'message' => 'Route not found'
            ], 404);
        }

        if ($request->filled('status')) {
            $route->status = $request->status;
        } else {
            $route->status = ($route->status === 'active') ? 'inactive' : 'active';
        }

        $route->save();

        return response()->json([
            'status'  => true,
            'message' => 'Route status updated to ' . ucfirst($route->status) . ' successfully',
            'data'    => $route
        ], 200);
    }

    /**
     * Delete route.
     */
    public function destroy($id)
    {
        $route = Route::find($id);
        if (!$route) {
            return response()->json([
                'status'  => false,
                'message' => 'Route not found'
            ], 404);
        }

        $route->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Route deleted successfully'
        ], 200);
    }
}
