<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;

class CityController extends Controller
{
    /**
     * Display listing of all cities.
     */
    public function AllCities()
    {
        $cities = City::with('country')->latest()->get();
        $countries = Country::where('is_active', 1)->orderBy('name_en', 'asc')->get();

        return view('admin.backend.city.all_cities', compact('cities', 'countries'));
    }

    /**
     * Show form to add a new city.
     */
    public function AddCity()
    {
        $countries = Country::where('is_active', 1)->orderBy('name_en', 'asc')->get();
        return view('admin.backend.city.add_city', compact('countries'));
    }

    /**
     * Store a new city record.
     */
    public function StoreCity(Request $request)
    {
        $request->validate([
            'country_id' => 'required|exists:countries,id',
            'name_ar' => 'required|string|max:100',
            'name_en' => 'required|string|max:100',
            'is_active' => 'nullable|boolean',
        ], [
            'country_id.required' => 'Please select a country.',
            'name_ar.required' => 'Please enter the Arabic name for the city.',
            'name_en.required' => 'Please enter the English name for the city.',
        ]);

        $city = new City();
        $city->country_id = $request->country_id;
        $city->name_ar = trim($request->name_ar);
        $city->name_en = trim($request->name_en);
        $city->is_active = $request->has('is_active') ? (int)$request->is_active : 1;
        $city->save();

        $notification = [
            'message' => 'City Created Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.cities')->with($notification);
    }

    /**
     * Show form to edit a city.
     */
    public function EditCity($id)
    {
        $city = City::with('country')->findOrFail($id);
        $countries = Country::where('is_active', 1)->orderBy('name_en', 'asc')->get();

        return view('admin.backend.city.edit_city', compact('city', 'countries'));
    }

    /**
     * Update city record.
     */
    public function UpdateCity(Request $request)
    {
        $id = $request->id;
        $city = City::findOrFail($id);

        $request->validate([
            'country_id' => 'required|exists:countries,id',
            'name_ar' => 'required|string|max:100',
            'name_en' => 'required|string|max:100',
            'is_active' => 'nullable|boolean',
        ], [
            'country_id.required' => 'Please select a country.',
            'name_ar.required' => 'Please enter the Arabic name for the city.',
            'name_en.required' => 'Please enter the English name for the city.',
        ]);

        $city->country_id = $request->country_id;
        $city->name_ar = trim($request->name_ar);
        $city->name_en = trim($request->name_en);
        $city->is_active = $request->has('is_active') ? (int)$request->is_active : 1;
        $city->save();

        $notification = [
            'message' => 'City Updated Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.cities')->with($notification);
    }

    /**
     * Delete city record.
     */
    public function DeleteCity($id)
    {
        $city = City::findOrFail($id);
        $city->delete();

        $notification = [
            'message' => 'City Deleted Successfully!',
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
            'city_id' => 'required|exists:cities,id',
            'is_active' => 'required|in:0,1',
        ]);

        $city = City::findOrFail($request->city_id);
        $city->is_active = (int)$request->is_active;
        $city->save();

        return response()->json([
            'status' => 'success',
            'message' => 'City status updated successfully.'
        ]);
    }

    /**
     * AJAX endpoint to fetch active cities for a specific country.
     */
    public function GetCitiesByCountryAjax($country_id)
    {
        $cities = City::where('country_id', $country_id)
                      ->where('is_active', 1)
                      ->orderBy('name_en', 'asc')
                      ->get(['id', 'name_en', 'name_ar']);

        return response()->json($cities);
    }
}
