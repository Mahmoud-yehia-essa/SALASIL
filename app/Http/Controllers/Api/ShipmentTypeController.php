<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShipmentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShipmentTypeController extends Controller
{
    /**
     * List all shipment types.
     */
    public function index(Request $request)
    {
        $query = ShipmentType::query();

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

        $types = $query->orderBy('id', 'asc')->get();

        return response()->json([
            'status'  => true,
            'message' => 'Shipment types retrieved successfully',
            'data'    => $types
        ], 200);
    }

    /**
     * Show single shipment type.
     */
    public function show($id)
    {
        $type = ShipmentType::find($id);

        if (!$type) {
            return response()->json([
                'status'  => false,
                'message' => 'Shipment type not found'
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Shipment type retrieved successfully',
            'data'    => $type
        ], 200);
    }

    /**
     * Store new shipment type.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar'   => 'required|string|max:100',
            'name_en'   => 'required|string|max:100',
            'is_active' => 'nullable|boolean',
        ], [
            'name_ar.required' => 'Shipment type name in Arabic is required.',
            'name_en.required' => 'Shipment type name in English is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $type = new ShipmentType();
        $type->name_ar   = trim($request->name_ar);
        $type->name_en   = trim($request->name_en);
        $type->is_active = $request->has('is_active') ? (int)$request->is_active : 1;
        $type->save();

        return response()->json([
            'status'  => true,
            'message' => 'Shipment type created successfully',
            'data'    => $type
        ], 201);
    }

    /**
     * Update shipment type.
     */
    public function update(Request $request, $id)
    {
        $type = ShipmentType::find($id);
        if (!$type) {
            return response()->json([
                'status'  => false,
                'message' => 'Shipment type not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name_ar'   => 'required|string|max:100',
            'name_en'   => 'required|string|max:100',
            'is_active' => 'nullable|boolean',
        ], [
            'name_ar.required' => 'Shipment type name in Arabic is required.',
            'name_en.required' => 'Shipment type name in English is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $type->name_ar   = trim($request->name_ar);
        $type->name_en   = trim($request->name_en);
        if ($request->has('is_active')) {
            $type->is_active = (int)$request->is_active;
        }
        $type->save();

        return response()->json([
            'status'  => true,
            'message' => 'Shipment type updated successfully',
            'data'    => $type
        ], 200);
    }

    /**
     * Toggle active status.
     */
    public function changeStatus(Request $request, $id)
    {
        $type = ShipmentType::find($id);
        if (!$type) {
            return response()->json([
                'status'  => false,
                'message' => 'Shipment type not found'
            ], 404);
        }

        if ($request->has('is_active')) {
            $type->is_active = (int)$request->is_active;
        } else {
            $type->is_active = $type->is_active ? 0 : 1;
        }

        $type->save();

        return response()->json([
            'status'  => true,
            'message' => 'Shipment type status updated to ' . ($type->is_active ? 'Active' : 'Inactive') . ' successfully',
            'data'    => $type
        ], 200);
    }

    /**
     * Delete shipment type.
     */
    public function destroy($id)
    {
        $type = ShipmentType::find($id);
        if (!$type) {
            return response()->json([
                'status'  => false,
                'message' => 'Shipment type not found'
            ], 404);
        }

        $type->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Shipment type deleted successfully'
        ], 200);
    }
}
