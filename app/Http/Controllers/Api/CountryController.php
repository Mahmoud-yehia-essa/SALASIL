<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CountryController extends Controller
{
    /**
     * List all countries.
     */
    public function index(Request $request)
    {
        $query = Country::withCount('cities');

        if ($request->has('is_active')) {
            $query->where('is_active', (int)$request->is_active);
        }

        if ($request->has('include') && $request->include == 'cities' || $request->boolean('with_cities')) {
            $query->with(['cities' => function ($q) {
                $q->where('is_active', 1)->orderBy('name_en', 'asc');
            }]);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $countries = $query->orderBy('name_en', 'asc')->get();

        return response()->json([
            'status'  => true,
            'message' => 'Countries retrieved successfully',
            'data'    => $countries
        ], 200);
    }

    /**
     * Show single country with its cities.
     */
    public function show($id)
    {
        $country = Country::with(['cities' => function ($q) {
            $q->orderBy('name_en', 'asc');
        }])->find($id);

        if (!$country) {
            return response()->json([
                'status'  => false,
                'message' => 'Country not found'
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Country retrieved successfully',
            'data'    => $country
        ], 200);
    }

    /**
     * Store new country record.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar'   => 'required|string|max:100',
            'name_en'   => 'required|string|max:100',
            'code'      => 'nullable|string|max:10',
            'is_active' => 'nullable|boolean',
        ], [
            'name_ar.required' => 'Country name in Arabic is required.',
            'name_en.required' => 'Country name in English is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $country = new Country();
        $country->name_ar   = trim($request->name_ar);
        $country->name_en   = trim($request->name_en);
        $country->code      = $request->filled('code') ? strtoupper(trim($request->code)) : null;
        $country->is_active = $request->has('is_active') ? (int)$request->is_active : 1;
        $country->save();

        return response()->json([
            'status'  => true,
            'message' => 'Country created successfully',
            'data'    => $country
        ], 201);
    }

    /**
     * Update country record.
     */
    public function update(Request $request, $id)
    {
        $country = Country::find($id);
        if (!$country) {
            return response()->json([
                'status'  => false,
                'message' => 'Country not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name_ar'   => 'required|string|max:100',
            'name_en'   => 'required|string|max:100',
            'code'      => 'nullable|string|max:10',
            'is_active' => 'nullable|boolean',
        ], [
            'name_ar.required' => 'Country name in Arabic is required.',
            'name_en.required' => 'Country name in English is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $country->name_ar   = trim($request->name_ar);
        $country->name_en   = trim($request->name_en);
        $country->code      = $request->filled('code') ? strtoupper(trim($request->code)) : null;
        if ($request->has('is_active')) {
            $country->is_active = (int)$request->is_active;
        }
        $country->save();

        return response()->json([
            'status'  => true,
            'message' => 'Country updated successfully',
            'data'    => $country
        ], 200);
    }

    /**
     * Toggle active status.
     */
    public function changeStatus(Request $request, $id)
    {
        $country = Country::find($id);
        if (!$country) {
            return response()->json([
                'status'  => false,
                'message' => 'Country not found'
            ], 404);
        }

        if ($request->has('is_active')) {
            $country->is_active = (int)$request->is_active;
        } else {
            $country->is_active = $country->is_active ? 0 : 1;
        }

        $country->save();

        return response()->json([
            'status'  => true,
            'message' => 'Country status updated to ' . ($country->is_active ? 'Active' : 'Inactive') . ' successfully',
            'data'    => $country
        ], 200);
    }

    /**
     * Delete country record.
     */
    public function destroy($id)
    {
        $country = Country::find($id);
        if (!$country) {
            return response()->json([
                'status'  => false,
                'message' => 'Country not found'
            ], 404);
        }

        $country->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Country and associated cities deleted successfully'
        ], 200);
    }
}
