<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TruckBrand;
use Illuminate\Http\Request;

class TruckBrandController extends Controller
{
    /**
     * List all truck brands.
     */
    public function AllTruckBrands()
    {
        $brands = TruckBrand::withCount('models')->latest()->get();
        return view('admin.backend.truck_brand.all_truck_brands', compact('brands'));
    }

    /**
     * Show form to add a new truck brand.
     */
    public function AddTruckBrand()
    {
        return view('admin.backend.truck_brand.add_truck_brand');
    }

    /**
     * Store new truck brand.
     */
    public function StoreTruckBrand(Request $request)
    {
        $request->validate([
            'name_en'   => 'required|string|max:100',
            'name_ar'   => 'required|string|max:100',
            'is_active' => 'required|boolean',
        ], [
            'name_en.required' => 'Brand name in English is required.',
            'name_ar.required' => 'Brand name in Arabic is required.',
        ]);

        $brand = new TruckBrand();
        $brand->name_en = trim($request->name_en);
        $brand->name_ar = trim($request->name_ar);
        $brand->is_active = (int)$request->is_active;
        $brand->save();

        $notification = [
            'message'    => 'Truck Brand Added Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.truck.brands')->with($notification);
    }

    /**
     * Show form to edit truck brand.
     */
    public function EditTruckBrand($id)
    {
        $brand = TruckBrand::findOrFail($id);
        return view('admin.backend.truck_brand.edit_truck_brand', compact('brand'));
    }

    /**
     * Update truck brand.
     */
    public function UpdateTruckBrand(Request $request)
    {
        $id = $request->id;
        $brand = TruckBrand::findOrFail($id);

        $request->validate([
            'name_en'   => 'required|string|max:100',
            'name_ar'   => 'required|string|max:100',
            'is_active' => 'required|boolean',
        ], [
            'name_en.required' => 'Brand name in English is required.',
            'name_ar.required' => 'Brand name in Arabic is required.',
        ]);

        $brand->name_en = trim($request->name_en);
        $brand->name_ar = trim($request->name_ar);
        $brand->is_active = (int)$request->is_active;
        $brand->save();

        $notification = [
            'message'    => 'Truck Brand Updated Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.truck.brands')->with($notification);
    }

    /**
     * Delete truck brand.
     */
    public function DeleteTruckBrand($id)
    {
        $brand = TruckBrand::findOrFail($id);
        $brand->delete();

        $notification = [
            'message'    => 'Truck Brand Deleted Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.truck.brands')->with($notification);
    }

    /**
     * Toggle status via AJAX.
     */
    public function ChangeStatusAjax(Request $request)
    {
        $brandId = $request->input('id') ?? $request->input('brand_id');
        $status = $request->has('is_active') ? $request->input('is_active') : $request->input('status');

        $brand = TruckBrand::find($brandId);
        if (!$brand) {
            return response()->json(['status' => 'error', 'message' => 'Brand not found.'], 404);
        }

        $brand->is_active = ($status == 1 || $status === '1' || $status === 'active') ? 1 : 0;
        $brand->save();

        return response()->json(['status' => 'success', 'message' => 'Brand status updated to ' . ($brand->is_active ? 'Active' : 'Inactive') . ' successfully!']);
    }
}
