<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShipmentNature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShipmentNatureController extends Controller
{
    /**
     * List all shipment natures.
     */
    public function index(Request $request)
    {
        $query = ShipmentNature::query();

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

        $natures = $query->orderBy('id', 'asc')->get();

        return response()->json([
            'status'  => true,
            'message' => 'Shipment natures retrieved successfully',
            'data'    => $natures
        ], 200);
    }

    /**
     * Show single shipment nature.
     */
    public function show($id)
    {
        $nature = ShipmentNature::find($id);

        if (!$nature) {
            return response()->json([
                'status'  => false,
                'message' => 'Shipment nature not found'
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Shipment nature retrieved successfully',
            'data'    => $nature
        ], 200);
    }

    /**
     * Store new shipment nature.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar'   => 'required|string|max:100',
            'name_en'   => 'required|string|max:100',
            'is_active' => 'nullable|boolean',
        ], [
            'name_ar.required' => 'Shipment nature name in Arabic is required.',
            'name_en.required' => 'Shipment nature name in English is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $nature = new ShipmentNature();
        $nature->name_ar   = trim($request->name_ar);
        $nature->name_en   = trim($request->name_en);
        $nature->is_active = $request->has('is_active') ? (int)$request->is_active : 1;
        $nature->save();

        return response()->json([
            'status'  => true,
            'message' => 'Shipment nature created successfully',
            'data'    => $nature
        ], 201);
    }

    /**
     * Update shipment nature.
     */
    public function update(Request $request, $id)
    {
        $nature = ShipmentNature::find($id);
        if (!$nature) {
            return response()->json([
                'status'  => false,
                'message' => 'Shipment nature not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name_ar'   => 'required|string|max:100',
            'name_en'   => 'required|string|max:100',
            'is_active' => 'nullable|boolean',
        ], [
            'name_ar.required' => 'Shipment nature name in Arabic is required.',
            'name_en.required' => 'Shipment nature name in English is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $nature->name_ar   = trim($request->name_ar);
        $nature->name_en   = trim($request->name_en);
        if ($request->has('is_active')) {
            $nature->is_active = (int)$request->is_active;
        }
        $nature->save();

        return response()->json([
            'status'  => true,
            'message' => 'Shipment nature updated successfully',
            'data'    => $nature
        ], 200);
    }

    /**
     * Toggle active status.
     */
    public function changeStatus(Request $request, $id)
    {
        $nature = ShipmentNature::find($id);
        if (!$nature) {
            return response()->json([
                'status'  => false,
                'message' => 'Shipment nature not found'
            ], 404);
        }

        if ($request->has('is_active')) {
            $nature->is_active = (int)$request->is_active;
        } else {
            $nature->is_active = $nature->is_active ? 0 : 1;
        }

        $nature->save();

        return response()->json([
            'status'  => true,
            'message' => 'Shipment nature status updated to ' . ($nature->is_active ? 'Active' : 'Inactive') . ' successfully',
            'data'    => $nature
        ], 200);
    }

    /**
     * Delete shipment nature.
     */
    public function destroy($id)
    {
        $nature = ShipmentNature::find($id);
        if (!$nature) {
            return response()->json([
                'status'  => false,
                'message' => 'Shipment nature not found'
            ], 404);
        }

        $nature->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Shipment nature deleted successfully'
        ], 200);
    }
}
