<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TruckBrand;
use App\Models\TruckModel;
use Illuminate\Http\Request;

class TruckModelController extends Controller
{
    /**
     * List all truck models.
     */
    public function AllTruckModels()
    {
        $models = TruckModel::with('brand')->latest()->get();
        return view('admin.backend.truck_model.all_truck_models', compact('models'));
    }

    /**
     * Show form to add a new truck model.
     */
    public function AddTruckModel()
    {
        $brands = TruckBrand::where('is_active', 1)->orderBy('name_en', 'asc')->get();
        return view('admin.backend.truck_model.add_truck_model', compact('brands'));
    }

    /**
     * Store new truck model.
     */
    public function StoreTruckModel(Request $request)
    {
        $request->validate([
            'truck_brand_id' => 'required|exists:truck_brands,id',
            'name_en'        => 'required|string|max:100',
            'name_ar'        => 'required|string|max:100',
            'is_active'      => 'required|boolean',
        ], [
            'truck_brand_id.required' => 'Please select a parent brand.',
            'name_en.required'        => 'Model name in English is required.',
            'name_ar.required'        => 'Model name in Arabic is required.',
        ]);

        $model = new TruckModel();
        $model->truck_brand_id = $request->truck_brand_id;
        $model->name_en = trim($request->name_en);
        $model->name_ar = trim($request->name_ar);
        $model->is_active = (int)$request->is_active;
        $model->save();

        $notification = [
            'message'    => 'Truck Model Added Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.truck.models')->with($notification);
    }

    /**
     * Show form to edit truck model.
     */
    public function EditTruckModel($id)
    {
        $model = TruckModel::findOrFail($id);
        $brands = TruckBrand::where('is_active', 1)->orderBy('name_en', 'asc')->get();
        return view('admin.backend.truck_model.edit_truck_model', compact('model', 'brands'));
    }

    /**
     * Update truck model.
     */
    public function UpdateTruckModel(Request $request)
    {
        $id = $request->id;
        $model = TruckModel::findOrFail($id);

        $request->validate([
            'truck_brand_id' => 'required|exists:truck_brands,id',
            'name_en'        => 'required|string|max:100',
            'name_ar'        => 'required|string|max:100',
            'is_active'      => 'required|boolean',
        ], [
            'truck_brand_id.required' => 'Please select a parent brand.',
            'name_en.required'        => 'Model name in English is required.',
            'name_ar.required'        => 'Model name in Arabic is required.',
        ]);

        $model->truck_brand_id = $request->truck_brand_id;
        $model->name_en = trim($request->name_en);
        $model->name_ar = trim($request->name_ar);
        $model->is_active = (int)$request->is_active;
        $model->save();

        $notification = [
            'message'    => 'Truck Model Updated Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.truck.models')->with($notification);
    }

    /**
     * Delete truck model.
     */
    public function DeleteTruckModel($id)
    {
        $model = TruckModel::findOrFail($id);
        $model->delete();

        $notification = [
            'message'    => 'Truck Model Deleted Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.truck.models')->with($notification);
    }

    /**
     * Toggle status via AJAX.
     */
    public function ChangeStatusAjax(Request $request)
    {
        $modelId = $request->input('id') ?? $request->input('model_id');
        $status = $request->has('is_active') ? $request->input('is_active') : $request->input('status');

        $model = TruckModel::find($modelId);
        if (!$model) {
            return response()->json(['status' => 'error', 'message' => 'Model not found.'], 404);
        }

        $model->is_active = ($status == 1 || $status === '1' || $status === 'active') ? 1 : 0;
        $model->save();

        return response()->json(['status' => 'success', 'message' => 'Model status updated to ' . ($model->is_active ? 'Active' : 'Inactive') . ' successfully!']);
    }

    /**
     * AJAX endpoint to retrieve models linked to a brand.
     */
    public function GetModelsByBrandAjax($brand_id)
    {
        $models = TruckModel::where('truck_brand_id', $brand_id)
                             ->where('is_active', 1)
                             ->orderBy('name_en', 'asc')
                             ->get(['id', 'name_en', 'name_ar']);

        return response()->json($models);
    }
}
