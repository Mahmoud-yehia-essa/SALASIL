<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CityController extends Controller
{
    /**
     * List all cities.
     */
    public function index(Request $request)
    {
        $query = City::with('country');

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', (int)$request->is_active);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        $cities = $query->orderBy('name_en', 'asc')->get();

        return response()->json([
            'status'  => true,
            'message' => 'Cities retrieved successfully',
            'data'    => $cities
        ], 200);
    }

    /**
     * Show single city.
     */
    public function show($id)
    {
        $city = City::with('country')->find($id);

        if (!$city) {
            return response()->json([
                'status'  => false,
                'message' => 'City not found'
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'City retrieved successfully',
            'data'    => $city
        ], 200);
    }

    /**
     * Get active cities by country ID.
     */
    public function getByCountry($countryId)
    {
        $cities = City::where('country_id', $countryId)
            ->where('is_active', 1)
            ->orderBy('name_en', 'asc')
            ->get();

        return response()->json([
            'status'  => true,
            'message' => 'Cities for country retrieved successfully',
            'data'    => $cities
        ], 200);
    }

    /**
     * Store new city record.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required|exists:countries,id',
            'name_ar'    => 'required|string|max:100',
            'name_en'    => 'required|string|max:100',
            'is_active'  => 'nullable|boolean',
        ], [
            'country_id.required' => 'Please select a country.',
            'name_ar.required'    => 'City name in Arabic is required.',
            'name_en.required'    => 'City name in English is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $city = new City();
        $city->country_id = $request->country_id;
        $city->name_ar    = trim($request->name_ar);
        $city->name_en    = trim($request->name_en);
        $city->is_active  = $request->has('is_active') ? (int)$request->is_active : 1;
        $city->save();

        $city->load('country');

        return response()->json([
            'status'  => true,
            'message' => 'City created successfully',
            'data'    => $city
        ], 201);
    }

    /**
     * Update city record.
     */
    public function update(Request $request, $id)
    {
        $city = City::find($id);
        if (!$city) {
            return response()->json([
                'status'  => false,
                'message' => 'City not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'country_id' => 'required|exists:countries,id',
            'name_ar'    => 'required|string|max:100',
            'name_en'    => 'required|string|max:100',
            'is_active'  => 'nullable|boolean',
        ], [
            'country_id.required' => 'Please select a country.',
            'name_ar.required'    => 'City name in Arabic is required.',
            'name_en.required'    => 'City name in English is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $city->country_id = $request->country_id;
        $city->name_ar    = trim($request->name_ar);
        $city->name_en    = trim($request->name_en);
        if ($request->has('is_active')) {
            $city->is_active = (int)$request->is_active;
        }
        $city->save();

        $city->load('country');

        return response()->json([
            'status'  => true,
            'message' => 'City updated successfully',
            'data'    => $city
        ], 200);
    }

    /**
     * Toggle active status.
     */
    public function changeStatus(Request $request, $id)
    {
        $city = City::find($id);
        if (!$city) {
            return response()->json([
                'status'  => false,
                'message' => 'City not found'
            ], 404);
        }

        if ($request->has('is_active')) {
            $city->is_active = (int)$request->is_active;
        } else {
            $city->is_active = $city->is_active ? 0 : 1;
        }

        $city->save();

        return response()->json([
            'status'  => true,
            'message' => 'City status updated to ' . ($city->is_active ? 'Active' : 'Inactive') . ' successfully',
            'data'    => $city
        ], 200);
    }

    /**
     * Delete city record.
     */
    public function destroy($id)
    {
        $city = City::find($id);
        if (!$city) {
            return response()->json([
                'status'  => false,
                'message' => 'City not found'
            ], 404);
        }

        $city->delete();

        return response()->json([
            'status'  => true,
            'message' => 'City deleted successfully'
        ], 200);
    }
}
