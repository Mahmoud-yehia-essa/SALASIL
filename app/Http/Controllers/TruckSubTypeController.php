<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TruckSubType;
use App\Models\TruckType;
use Illuminate\Http\Request;

class TruckSubTypeController extends Controller
{
    /**
     * List all truck sub-types.
     */
    public function AllTruckSubTypes()
    {
        $truckSubTypes = TruckSubType::with('truckType')->latest()->get();
        return view('admin.backend.truck_sub_type.all_truck_sub_types', compact('truckSubTypes'));
    }

    /**
     * Show form to add a new truck sub-type.
     */
    public function AddTruckSubType()
    {
        $truckTypes = TruckType::latest()->get();
        return view('admin.backend.truck_sub_type.add_truck_sub_type', compact('truckTypes'));
    }

    /**
     * Store new truck sub-type in database.
     */
    public function StoreTruckSubType(Request $request)
    {
        $request->validate([
            'truck_type_id' => 'required|exists:truck_types,id',
            'name_en'       => 'required|string|max:100',
            'name_ar'       => 'nullable|string|max:100',
            'max_payload'   => 'nullable|numeric|min:0|max:999999.99',
            'is_active'     => 'required|in:0,1',
        ], [
            'truck_type_id.required' => 'Please select a main truck type category.',
            'name_en.required'       => 'Truck sub-type name (English) is required.',
        ]);

        $subType = new TruckSubType();
        $subType->truck_type_id = $request->truck_type_id;
        $subType->name_en = trim($request->name_en);
        $subType->name_ar = $request->filled('name_ar') ? trim($request->name_ar) : trim($request->name_en);
        $subType->max_payload = $request->max_payload;
        $subType->is_active = (int)$request->is_active;
        $subType->save();

        $notification = [
            'message'    => 'New Truck Sub-Type Added Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.truck.sub.types')->with($notification);
    }

    /**
     * Show form to edit truck sub-type details.
     */
    public function EditTruckSubType($id)
    {
        $subType = TruckSubType::findOrFail($id);
        $truckTypes = TruckType::latest()->get();
        return view('admin.backend.truck_sub_type.edit_truck_sub_type', compact('subType', 'truckTypes'));
    }

    /**
     * Update truck sub-type details in database.
     */
    public function UpdateTruckSubType(Request $request)
    {
        $id = $request->id;
        $subType = TruckSubType::findOrFail($id);

        $request->validate([
            'truck_type_id' => 'required|exists:truck_types,id',
            'name_en'       => 'required|string|max:100',
            'name_ar'       => 'nullable|string|max:100',
            'max_payload'   => 'nullable|numeric|min:0|max:999999.99',
            'is_active'     => 'required|in:0,1',
        ], [
            'truck_type_id.required' => 'Please select a main truck type category.',
            'name_en.required'       => 'Truck sub-type name (English) is required.',
        ]);

        $subType->truck_type_id = $request->truck_type_id;
        $subType->name_en = trim($request->name_en);
        $subType->name_ar = $request->filled('name_ar') ? trim($request->name_ar) : trim($request->name_en);
        $subType->max_payload = $request->max_payload;
        $subType->is_active = (int)$request->is_active;
        $subType->save();

        $notification = [
            'message'    => 'Truck Sub-Type Updated Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.truck.sub.types')->with($notification);
    }

    /**
     * Change status asynchronously via AJAX.
     */
    public function ChangeStatusAjax(Request $request)
    {
        $subTypeId = $request->input('sub_type_id') ?? $request->input('id');
        $isActive = $request->input('is_active');

        if ($subTypeId === null || $isActive === null) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid parameters provided.'
            ], 422);
        }

        $subType = TruckSubType::find($subTypeId);
        if (!$subType) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Truck sub-type not found.'
            ], 404);
        }

        $subType->is_active = (int)$isActive;
        $subType->save();

        $statusMsg = $subType->is_active ? 'Active' : 'Inactive';

        return response()->json([
            'status'  => 'success',
            'message' => 'Truck sub-type status updated to ' . $statusMsg . ' successfully!'
        ]);
    }

    /**
     * Delete truck sub-type from database.
     */
    public function DeleteTruckSubType($id)
    {
        $subType = TruckSubType::findOrFail($id);
        $subType->delete();

        $notification = [
            'message'    => 'Truck Sub-Type Deleted Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }
}
