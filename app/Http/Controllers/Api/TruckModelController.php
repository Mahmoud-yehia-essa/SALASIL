<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TruckModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TruckModelController extends Controller
{
    /**
     * List all truck models.
     */
    public function index(Request $request)
    {
        $query = TruckModel::with('brand');

        if ($request->filled('truck_brand_id')) {
            $query->where('truck_brand_id', $request->truck_brand_id);
        }

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

        $models = $query->orderBy('name_en', 'asc')->get();

        return response()->json([
            'status'  => true,
            'message' => 'Truck models retrieved successfully',
            'data'    => $models
        ], 200);
    }

    /**
     * Show single truck model.
     */
    public function show($id)
    {
        $model = TruckModel::with('brand')->find($id);

        if (!$model) {
            return response()->json([
                'status'  => false,
                'message' => 'Truck model not found'
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Truck model retrieved successfully',
            'data'    => $model
        ], 200);
    }

    /**
     * Get models by Brand ID.
     */
    public function getByBrand($brandId)
    {
        $models = TruckModel::where('truck_brand_id', $brandId)
            ->where('is_active', 1)
            ->orderBy('name_en', 'asc')
            ->get();

        return response()->json([
            'status'  => true,
            'message' => 'Models for brand retrieved successfully',
            'data'    => $models
        ], 200);
    }

    /**
     * Store new truck model.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'truck_brand_id' => 'required|exists:truck_brands,id',
            'name_en'        => 'required|string|max:100',
            'name_ar'        => 'required|string|max:100',
            'is_active'      => 'nullable|boolean',
        ], [
            'truck_brand_id.required' => 'Please select a parent brand.',
            'name_en.required'        => 'Model name in English is required.',
            'name_ar.required'        => 'Model name in Arabic is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $model = new TruckModel();
        $model->truck_brand_id = $request->truck_brand_id;
        $model->name_en        = trim($request->name_en);
        $model->name_ar        = trim($request->name_ar);
        $model->is_active      = $request->has('is_active') ? (int)$request->is_active : 1;
        $model->save();

        $model->load('brand');

        return response()->json([
            'status'  => true,
            'message' => 'Truck model created successfully',
            'data'    => $model
        ], 201);
    }

    /**
     * Update truck model.
     */
    public function update(Request $request, $id)
    {
        $model = TruckModel::find($id);
        if (!$model) {
            return response()->json([
                'status'  => false,
                'message' => 'Truck model not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'truck_brand_id' => 'required|exists:truck_brands,id',
            'name_en'        => 'required|string|max:100',
            'name_ar'        => 'required|string|max:100',
            'is_active'      => 'nullable|boolean',
        ], [
            'truck_brand_id.required' => 'Please select a parent brand.',
            'name_en.required'        => 'Model name in English is required.',
            'name_ar.required'        => 'Model name in Arabic is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $model->truck_brand_id = $request->truck_brand_id;
        $model->name_en        = trim($request->name_en);
        $model->name_ar        = trim($request->name_ar);
        if ($request->has('is_active')) {
            $model->is_active = (int)$request->is_active;
        }
        $model->save();

        $model->load('brand');

        return response()->json([
            'status'  => true,
            'message' => 'Truck model updated successfully',
            'data'    => $model
        ], 200);
    }

    /**
     * Toggle status of truck model.
     */
    public function changeStatus(Request $request, $id)
    {
        $model = TruckModel::find($id);
        if (!$model) {
            return response()->json([
                'status'  => false,
                'message' => 'Truck model not found'
            ], 404);
        }

        if ($request->has('is_active')) {
            $model->is_active = (int)$request->is_active;
        } else {
            $model->is_active = $model->is_active ? 0 : 1;
        }

        $model->save();

        return response()->json([
            'status'  => true,
            'message' => 'Truck model status updated to ' . ($model->is_active ? 'Active' : 'Inactive') . ' successfully',
            'data'    => $model
        ], 200);
    }

    /**
     * Delete truck model.
     */
    public function destroy($id)
    {
        $model = TruckModel::find($id);
        if (!$model) {
            return response()->json([
                'status'  => false,
                'message' => 'Truck model not found'
            ], 404);
        }

        $model->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Truck model deleted successfully'
        ], 200);
    }
}
