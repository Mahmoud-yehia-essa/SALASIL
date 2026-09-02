<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TruckBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TruckBrandController extends Controller
{
    /**
     * List all truck brands.
     */
    public function index(Request $request)
    {
        $query = TruckBrand::withCount('models');

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%");
            });
        }

        $brands = $query->orderBy('name_en', 'asc')->get();

        return response()->json([
            'status'  => true,
            'message' => 'Truck brands retrieved successfully',
            'data'    => $brands
        ], 200);
    }

    /**
     * Show single truck brand with its models.
     */
    public function show($id)
    {
        $brand = TruckBrand::with(['models' => function ($q) {
            $q->orderBy('name_en', 'asc');
        }])->find($id);

        if (!$brand) {
            return response()->json([
                'status'  => false,
                'message' => 'Truck brand not found'
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Truck brand retrieved successfully',
            'data'    => $brand
        ], 200);
    }

    /**
     * Store a new truck brand.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_en'   => 'required|string|max:100',
            'name_ar'   => 'required|string|max:100',
            'is_active' => 'nullable|boolean',
        ], [
            'name_en.required' => 'Brand name in English is required.',
            'name_ar.required' => 'Brand name in Arabic is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $brand = new TruckBrand();
        $brand->name_en   = trim($request->name_en);
        $brand->name_ar   = trim($request->name_ar);
        $brand->is_active = $request->has('is_active') ? (int)$request->is_active : 1;
        $brand->save();

        return response()->json([
            'status'  => true,
            'message' => 'Truck brand created successfully',
            'data'    => $brand
        ], 201);
    }

    /**
     * Update an existing truck brand.
     */
    public function update(Request $request, $id)
    {
        $brand = TruckBrand::find($id);
        if (!$brand) {
            return response()->json([
                'status'  => false,
                'message' => 'Truck brand not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name_en'   => 'required|string|max:100',
            'name_ar'   => 'required|string|max:100',
            'is_active' => 'nullable|boolean',
        ], [
            'name_en.required' => 'Brand name in English is required.',
            'name_ar.required' => 'Brand name in Arabic is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $brand->name_en = trim($request->name_en);
        $brand->name_ar = trim($request->name_ar);
        if ($request->has('is_active')) {
            $brand->is_active = (int)$request->is_active;
        }
        $brand->save();

        return response()->json([
            'status'  => true,
            'message' => 'Truck brand updated successfully',
            'data'    => $brand
        ], 200);
    }

    /**
     * Toggle status of truck brand.
     */
    public function changeStatus(Request $request, $id)
    {
        $brand = TruckBrand::find($id);
        if (!$brand) {
            return response()->json([
                'status'  => false,
                'message' => 'Truck brand not found'
            ], 404);
        }

        if ($request->has('is_active')) {
            $brand->is_active = (int)$request->is_active;
        } else {
            $brand->is_active = $brand->is_active ? 0 : 1;
        }

        $brand->save();

        return response()->json([
            'status'  => true,
            'message' => 'Truck brand status updated to ' . ($brand->is_active ? 'Active' : 'Inactive') . ' successfully',
            'data'    => $brand
        ], 200);
    }

    /**
     * Delete truck brand.
     */
    public function destroy($id)
    {
        $brand = TruckBrand::find($id);
        if (!$brand) {
            return response()->json([
                'status'  => false,
                'message' => 'Truck brand not found'
            ], 404);
        }

        $brand->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Truck brand deleted successfully'
        ], 200);
    }
}
