<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TruckType;
use Illuminate\Http\Request;

class TruckTypeController extends Controller
{
    /**
     * List all truck types.
     */
    public function AllTruckTypes()
    {
        $truckTypes = TruckType::latest()->get();
        return view('admin.backend.truck_type.all_truck_types', compact('truckTypes'));
    }

    /**
     * Show form to add a new truck type.
     */
    public function AddTruckType()
    {
        return view('admin.backend.truck_type.add_truck_type');
    }

    /**
     * Store new truck type in database.
     */
    public function StoreTruckType(Request $request)
    {
        $request->validate([
            'name_en'    => 'required|string|max:255',
            'name_ar'    => 'nullable|string|max:255',
            'max_weight' => 'nullable|numeric|min:0|max:999999.99',
            'status'     => 'required|in:active,inactive',
            'photo'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ], [
            'name_en.required' => 'Truck type name (English) is required.',
        ]);

        $truckType = new TruckType();
        $truckType->name_en = trim($request->name_en);
        $truckType->name_ar = $request->filled('name_ar') ? trim($request->name_ar) : trim($request->name_en);
        $truckType->max_weight = $request->max_weight;
        $truckType->status = $request->status;

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = date('YmdHi') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/truck_images'), $filename);
            $truckType->photo = $filename;
        }

        $truckType->save();

        $notification = [
            'message'    => 'New Truck Type Added Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.truck.types')->with($notification);
    }

    /**
     * Show form to edit truck type details.
     */
    public function EditTruckType($id)
    {
        $truckType = TruckType::findOrFail($id);
        return view('admin.backend.truck_type.edit_truck_type', compact('truckType'));
    }

    /**
     * Update truck type details in database.
     */
    public function UpdateTruckType(Request $request)
    {
        $id = $request->id;
        $truckType = TruckType::findOrFail($id);

        $request->validate([
            'name_en'    => 'required|string|max:255',
            'name_ar'    => 'nullable|string|max:255',
            'max_weight' => 'nullable|numeric|min:0|max:999999.99',
            'status'     => 'required|in:active,inactive',
            'photo'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ], [
            'name_en.required' => 'Truck type name (English) is required.',
        ]);

        $truckType->name_en = trim($request->name_en);
        $truckType->name_ar = $request->filled('name_ar') ? trim($request->name_ar) : trim($request->name_en);
        $truckType->max_weight = $request->max_weight;
        $truckType->status = $request->status;

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            if (!empty($truckType->photo) && file_exists(public_path('upload/truck_images/' . $truckType->photo))) {
                @unlink(public_path('upload/truck_images/' . $truckType->photo));
            }
            $filename = date('YmdHi') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/truck_images'), $filename);
            $truckType->photo = $filename;
        }

        $truckType->save();

        $notification = [
            'message'    => 'Truck Type Updated Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.truck.types')->with($notification);
    }

    /**
     * Change status asynchronously via AJAX.
     */
    public function ChangeStatusAjax(Request $request)
    {
        $truckTypeId = $request->input('truck_type_id') ?? $request->input('id');
        $status = $request->input('status');

        if (!$truckTypeId || !$status) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid parameters provided.'
            ], 422);
        }

        $truckType = TruckType::find($truckTypeId);
        if (!$truckType) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Truck type not found.'
            ], 404);
        }

        $truckType->status = $status;
        $truckType->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Truck type status updated to ' . ucfirst($status) . ' successfully!'
        ]);
    }

    /**
     * Delete truck type from database.
     */
    public function DeleteTruckType($id)
    {
        $truckType = TruckType::findOrFail($id);

        if (!empty($truckType->photo) && file_exists(public_path('upload/truck_images/' . $truckType->photo))) {
            @unlink(public_path('upload/truck_images/' . $truckType->photo));
        }

        $truckType->delete();

        $notification = [
            'message'    => 'Truck Type Deleted Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }
}
