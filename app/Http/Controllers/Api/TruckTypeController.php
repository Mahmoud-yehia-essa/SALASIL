<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TruckType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TruckTypeController extends Controller
{
    /**
     * List all truck types.
     */
    public function index(Request $request)
    {
        $query = TruckType::with('subTypes');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%");
            });
        }

        $truckTypes = $query->latest()->get()->map(function ($type) {
            return $this->formatTruckType($type);
        });

        return response()->json([
            'status'  => true,
            'message' => 'Truck types retrieved successfully',
            'data'    => $truckTypes
        ], 200);
    }

    /**
     * Show single truck type.
     */
    public function show($id)
    {
        $truckType = TruckType::with('subTypes')->find($id);

        if (!$truckType) {
            return response()->json([
                'status'  => false,
                'message' => 'Truck type not found'
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Truck type retrieved successfully',
            'data'    => $this->formatTruckType($truckType)
        ], 200);
    }

    /**
     * Store new truck type.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_en'    => 'required|string|max:255',
            'name_ar'    => 'nullable|string|max:255',
            'max_weight' => 'nullable|numeric|min:0|max:999999.99',
            'status'     => 'nullable|in:active,inactive',
            'photo'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
        ], [
            'name_en.required' => 'Truck type name (English) is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $truckType = new TruckType();
        $truckType->name_en    = trim($request->name_en);
        $truckType->name_ar    = $request->filled('name_ar') ? trim($request->name_ar) : trim($request->name_en);
        $truckType->max_weight = $request->max_weight;
        $truckType->status     = $request->status ?? 'active';

        if ($request->hasFile('photo')) {
            $dir = public_path('upload/truck_images');
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
            $file = $request->file('photo');
            $filename = date('YmdHi') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $truckType->photo = $filename;
        }

        $truckType->save();

        return response()->json([
            'status'  => true,
            'message' => 'Truck type created successfully',
            'data'    => $this->formatTruckType($truckType)
        ], 201);
    }

    /**
     * Update truck type.
     */
    public function update(Request $request, $id)
    {
        $truckType = TruckType::find($id);
        if (!$truckType) {
            return response()->json([
                'status'  => false,
                'message' => 'Truck type not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name_en'    => 'required|string|max:255',
            'name_ar'    => 'nullable|string|max:255',
            'max_weight' => 'nullable|numeric|min:0|max:999999.99',
            'status'     => 'nullable|in:active,inactive',
            'photo'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
        ], [
            'name_en.required' => 'Truck type name (English) is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $truckType->name_en    = trim($request->name_en);
        $truckType->name_ar    = $request->filled('name_ar') ? trim($request->name_ar) : trim($request->name_en);
        $truckType->max_weight = $request->max_weight;
        if ($request->filled('status')) {
            $truckType->status = $request->status;
        }

        if ($request->hasFile('photo')) {
            $dir = public_path('upload/truck_images');
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
            if (!empty($truckType->photo) && file_exists($dir . '/' . $truckType->photo)) {
                @unlink($dir . '/' . $truckType->photo);
            }
            $file = $request->file('photo');
            $filename = date('YmdHi') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $truckType->photo = $filename;
        }

        $truckType->save();

        return response()->json([
            'status'  => true,
            'message' => 'Truck type updated successfully',
            'data'    => $this->formatTruckType($truckType)
        ], 200);
    }

    /**
     * Toggle status of truck type.
     */
    public function changeStatus(Request $request, $id)
    {
        $truckType = TruckType::find($id);
        if (!$truckType) {
            return response()->json([
                'status'  => false,
                'message' => 'Truck type not found'
            ], 404);
        }

        if ($request->filled('status')) {
            $truckType->status = $request->status;
        } else {
            $truckType->status = ($truckType->status === 'active') ? 'inactive' : 'active';
        }

        $truckType->save();

        return response()->json([
            'status'  => true,
            'message' => 'Truck type status updated to ' . ucfirst($truckType->status) . ' successfully',
            'data'    => $this->formatTruckType($truckType)
        ], 200);
    }

    /**
     * Delete truck type.
     */
    public function destroy($id)
    {
        $truckType = TruckType::find($id);
        if (!$truckType) {
            return response()->json([
                'status'  => false,
                'message' => 'Truck type not found'
            ], 404);
        }

        $dir = public_path('upload/truck_images');
        if (!empty($truckType->photo) && file_exists($dir . '/' . $truckType->photo)) {
            @unlink($dir . '/' . $truckType->photo);
        }

        $truckType->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Truck type deleted successfully'
        ], 200);
    }

    /**
     * Format truck type with photo URL.
     */
    private function formatTruckType(TruckType $type): array
    {
        $data = $type->toArray();
        $data['photo_url'] = $type->photo ? asset('upload/truck_images/' . $type->photo) : null;
        return $data;
    }
}
