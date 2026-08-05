<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ShipmentType;
use Illuminate\Http\Request;

class ShipmentTypeController extends Controller
{
    /**
     * Display listing of all shipment types.
     */
    public function AllShipmentTypes()
    {
        $shipmentTypes = ShipmentType::latest()->get();
        return view('admin.backend.shipment_type.all_shipment_types', compact('shipmentTypes'));
    }

    /**
     * Show form to add a new shipment type.
     */
    public function AddShipmentType()
    {
        return view('admin.backend.shipment_type.add_shipment_type');
    }

    /**
     * Store a new shipment type.
     */
    public function StoreShipmentType(Request $request)
    {
        $request->validate([
            'name_ar' => 'required|string|max:100',
            'name_en' => 'required|string|max:100',
            'is_active' => 'nullable|boolean',
        ], [
            'name_ar.required' => 'Please enter the Arabic name for the shipment type.',
            'name_en.required' => 'Please enter the English name for the shipment type.',
        ]);

        $shipmentType = new ShipmentType();
        $shipmentType->name_ar = trim($request->name_ar);
        $shipmentType->name_en = trim($request->name_en);
        $shipmentType->is_active = $request->has('is_active') ? (int)$request->is_active : 1;
        $shipmentType->save();

        $notification = [
            'message' => 'Shipment Type Created Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.shipment.types')->with($notification);
    }

    /**
     * Show form to edit a shipment type.
     */
    public function EditShipmentType($id)
    {
        $shipmentType = ShipmentType::findOrFail($id);
        return view('admin.backend.shipment_type.edit_shipment_type', compact('shipmentType'));
    }

    /**
     * Update shipment type.
     */
    public function UpdateShipmentType(Request $request)
    {
        $id = $request->id;
        $shipmentType = ShipmentType::findOrFail($id);

        $request->validate([
            'name_ar' => 'required|string|max:100',
            'name_en' => 'required|string|max:100',
            'is_active' => 'nullable|boolean',
        ], [
            'name_ar.required' => 'Please enter the Arabic name for the shipment type.',
            'name_en.required' => 'Please enter the English name for the shipment type.',
        ]);

        $shipmentType->name_ar = trim($request->name_ar);
        $shipmentType->name_en = trim($request->name_en);
        $shipmentType->is_active = $request->has('is_active') ? (int)$request->is_active : 1;
        $shipmentType->save();

        $notification = [
            'message' => 'Shipment Type Updated Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.shipment.types')->with($notification);
    }

    /**
     * Delete shipment type.
     */
    public function DeleteShipmentType($id)
    {
        $shipmentType = ShipmentType::findOrFail($id);
        $shipmentType->delete();

        $notification = [
            'message' => 'Shipment Type Deleted Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * AJAX endpoint to update active status (is_active).
     */
    public function ChangeStatusAjax(Request $request)
    {
        $request->validate([
            'shipment_type_id' => 'required|exists:shipment_types,id',
            'is_active' => 'required|in:0,1',
        ]);

        $shipmentType = ShipmentType::findOrFail($request->shipment_type_id);
        $shipmentType->is_active = (int)$request->is_active;
        $shipmentType->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Shipment type status updated successfully.'
        ]);
    }
}
