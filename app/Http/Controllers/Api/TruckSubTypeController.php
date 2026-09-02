<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TruckSubType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TruckSubTypeController extends Controller
{
    /**
     * List all truck sub-types.
     */
    public function index(Request $request)
    {
        $query = TruckSubType::with('truckType');

        if ($request->filled('truck_type_id')) {
            $query->where('truck_type_id', $request->truck_type_id);
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

        $subTypes = $query->orderBy('name_en', 'asc')->get();

        return response()->json([
            'status'  => true,
            'message' => 'Truck sub-types retrieved successfully',
            'data'    => $subTypes
        ], 200);
    }

    /**
     * Show single truck sub-type.
     */
    public function show($id)
    {
        $subType = TruckSubType::with('truckType')->find($id);

        if (!$subType) {
            return response()->json([
                'status'  => false,
                'message' => 'Truck sub-type not found'
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Truck sub-type retrieved successfully',
            'data'    => $subType
        ], 200);
    }

    /**
     * Get sub-types by parent Truck Type ID.
     */
    public function getByTruckType($truckTypeId)
    {
        $subTypes = TruckSubType::where('truck_type_id', $truckTypeId)
            ->where('is_active', 1)
            ->orderBy('name_en', 'asc')
            ->get();

        return response()->json([
            'status'  => true,
            'message' => 'Sub-types for truck type retrieved successfully',
            'data'    => $subTypes
        ], 200);
    }

    /**
     * Store new truck sub-type.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'truck_type_id' => 'required|exists:truck_types,id',
            'name_en'       => 'required|string|max:100',
            'name_ar'       => 'nullable|string|max:100',
            'max_payload'   => 'nullable|numeric|min:0|max:999999.99',
            'is_active'     => 'nullable|in:0,1',
        ], [
            'truck_type_id.required' => 'Please select a main truck type category.',
            'name_en.required'       => 'Truck sub-type name (English) is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $subType = new TruckSubType();
        $subType->truck_type_id = $request->truck_type_id;
        $subType->name_en       = trim($request->name_en);
        $subType->name_ar       = $request->filled('name_ar') ? trim($request->name_ar) : trim($request->name_en);
        $subType->max_payload   = $request->max_payload;
        $subType->is_active     = $request->has('is_active') ? (int)$request->is_active : 1;
        $subType->save();

        $subType->load('truckType');

        return response()->json([
            'status'  => true,
            'message' => 'Truck sub-type created successfully',
            'data'    => $subType
        ], 201);
    }

    /**
     * Update truck sub-type.
     */
    public function update(Request $request, $id)
    {
        $subType = TruckSubType::find($id);
        if (!$subType) {
            return response()->json([
                'status'  => false,
                'message' => 'Truck sub-type not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'truck_type_id' => 'required|exists:truck_types,id',
            'name_en'       => 'required|string|max:100',
            'name_ar'       => 'nullable|string|max:100',
            'max_payload'   => 'nullable|numeric|min:0|max:999999.99',
            'is_active'     => 'nullable|in:0,1',
        ], [
            'truck_type_id.required' => 'Please select a main truck type category.',
            'name_en.required'       => 'Truck sub-type name (English) is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $subType->truck_type_id = $request->truck_type_id;
        $subType->name_en       = trim($request->name_en);
        $subType->name_ar       = $request->filled('name_ar') ? trim($request->name_ar) : trim($request->name_en);
        $subType->max_payload   = $request->max_payload;
        if ($request->has('is_active')) {
            $subType->is_active = (int)$request->is_active;
        }
        $subType->save();

        $subType->load('truckType');

        return response()->json([
            'status'  => true,
            'message' => 'Truck sub-type updated successfully',
            'data'    => $subType
        ], 200);
    }

    /**
     * Toggle status of truck sub-type.
     */
    public function changeStatus(Request $request, $id)
    {
        $subType = TruckSubType::find($id);
        if (!$subType) {
            return response()->json([
                'status'  => false,
                'message' => 'Truck sub-type not found'
            ], 404);
        }

        if ($request->has('is_active')) {
            $subType->is_active = (int)$request->is_active;
        } else {
            $subType->is_active = $subType->is_active ? 0 : 1;
        }

        $subType->save();

        return response()->json([
            'status'  => true,
            'message' => 'Truck sub-type status updated to ' . ($subType->is_active ? 'Active' : 'Inactive') . ' successfully',
            'data'    => $subType
        ], 200);
    }

    /**
     * Delete truck sub-type.
     */
    public function destroy($id)
    {
        $subType = TruckSubType::find($id);
        if (!$subType) {
            return response()->json([
                'status'  => false,
                'message' => 'Truck sub-type not found'
            ], 404);
        }

        $subType->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Truck sub-type deleted successfully'
        ], 200);
    }
}
