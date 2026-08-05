<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    /**
     * Display listing of all countries.
     */
    public function AllCountries()
    {
        $countries = Country::withCount('cities')->latest()->get();
        return view('admin.backend.country.all_countries', compact('countries'));
    }

    /**
     * Show form to add a new country.
     */
    public function AddCountry()
    {
        return view('admin.backend.country.add_country');
    }

    /**
     * Store a new country record.
     */
    public function StoreCountry(Request $request)
    {
        $request->validate([
            'name_ar' => 'required|string|max:100',
            'name_en' => 'required|string|max:100',
            'code' => 'nullable|string|max:10',
            'is_active' => 'nullable|boolean',
        ], [
            'name_ar.required' => 'Please enter the Arabic name for the country.',
            'name_en.required' => 'Please enter the English name for the country.',
        ]);

        $country = new Country();
        $country->name_ar = trim($request->name_ar);
        $country->name_en = trim($request->name_en);
        $country->code = $request->code ? strtoupper(trim($request->code)) : null;
        $country->is_active = $request->has('is_active') ? (int)$request->is_active : 1;
        $country->save();

        $notification = [
            'message' => 'Country Created Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.countries')->with($notification);
    }

    /**
     * Show form to edit a country record.
     */
    public function EditCountry($id)
    {
        $country = Country::findOrFail($id);
        return view('admin.backend.country.edit_country', compact('country'));
    }

    /**
     * Update country record.
     */
    public function UpdateCountry(Request $request)
    {
        $id = $request->id;
        $country = Country::findOrFail($id);

        $request->validate([
            'name_ar' => 'required|string|max:100',
            'name_en' => 'required|string|max:100',
            'code' => 'nullable|string|max:10',
            'is_active' => 'nullable|boolean',
        ], [
            'name_ar.required' => 'Please enter the Arabic name for the country.',
            'name_en.required' => 'Please enter the English name for the country.',
        ]);

        $country->name_ar = trim($request->name_ar);
        $country->name_en = trim($request->name_en);
        $country->code = $request->code ? strtoupper(trim($request->code)) : null;
        $country->is_active = $request->has('is_active') ? (int)$request->is_active : 1;
        $country->save();

        $notification = [
            'message' => 'Country Updated Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.countries')->with($notification);
    }

    /**
     * Delete country record.
     */
    public function DeleteCountry($id)
    {
        $country = Country::findOrFail($id);
        $country->delete();

        $notification = [
            'message' => 'Country and Associated Cities Deleted Successfully!',
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
            'country_id' => 'required|exists:countries,id',
            'is_active' => 'required|in:0,1',
        ]);

        $country = Country::findOrFail($request->country_id);
        $country->is_active = (int)$request->is_active;
        $country->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Country status updated successfully.'
        ]);
    }
}
