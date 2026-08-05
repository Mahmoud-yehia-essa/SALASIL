<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ShipmentNature;
use Illuminate\Http\Request;

class ShipmentNatureController extends Controller
{
    /**
     * Display listing of all shipment natures.
     */
    public function AllShipmentNatures()
    {
        $shipmentNatures = ShipmentNature::latest()->get();
        return view('admin.backend.shipment_nature.all_shipment_natures', compact('shipmentNatures'));
    }

    /**
     * Show form to add a new shipment nature.
     */
    public function AddShipmentNature()
    {
        return view('admin.backend.shipment_nature.add_shipment_nature');
    }

    /**
     * Store a new shipment nature.
     */
    public function StoreShipmentNature(Request $request)
    {
        $request->validate([
            'name_ar' => 'required|string|max:100',
            'name_en' => 'required|string|max:100',
            'is_active' => 'nullable|boolean',
        ], [
            'name_ar.required' => 'Please enter the Arabic name for the shipment nature.',
            'name_en.required' => 'Please enter the English name for the shipment nature.',
        ]);

        $shipmentNature = new ShipmentNature();
        $shipmentNature->name_ar = trim($request->name_ar);
        $shipmentNature->name_en = trim($request->name_en);
        $shipmentNature->is_active = $request->has('is_active') ? (int)$request->is_active : 1;
        $shipmentNature->save();

        $notification = [
            'message' => 'Shipment Nature Created Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.shipment.natures')->with($notification);
    }

    /**
     * Show form to edit a shipment nature.
     */
    public function EditShipmentNature($id)
    {
        $shipmentNature = ShipmentNature::findOrFail($id);
        return view('admin.backend.shipment_nature.edit_shipment_nature', compact('shipmentNature'));
    }

    /**
     * Update shipment nature.
     */
    public function UpdateShipmentNature(Request $request)
    {
        $id = $request->id;
        $shipmentNature = ShipmentNature::findOrFail($id);

        $request->validate([
            'name_ar' => 'required|string|max:100',
            'name_en' => 'required|string|max:100',
            'is_active' => 'nullable|boolean',
        ], [
            'name_ar.required' => 'Please enter the Arabic name for the shipment nature.',
            'name_en.required' => 'Please enter the English name for the shipment nature.',
        ]);

        $shipmentNature->name_ar = trim($request->name_ar);
        $shipmentNature->name_en = trim($request->name_en);
        $shipmentNature->is_active = $request->has('is_active') ? (int)$request->is_active : 1;
        $shipmentNature->save();

        $notification = [
            'message' => 'Shipment Nature Updated Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.shipment.natures')->with($notification);
    }

    /**
     * Delete shipment nature.
     */
    public function DeleteShipmentNature($id)
    {
        $shipmentNature = ShipmentNature::findOrFail($id);
        $shipmentNature->delete();

        $notification = [
            'message' => 'Shipment Nature Deleted Successfully!',
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
            'shipment_nature_id' => 'required|exists:shipment_natures,id',
            'is_active' => 'required|in:0,1',
        ]);

        $shipmentNature = ShipmentNature::findOrFail($request->shipment_nature_id);
        $shipmentNature->is_active = (int)$request->is_active;
        $shipmentNature->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Shipment nature status updated successfully.'
        ]);
    }
}
