<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\DriverTruck;
use App\Models\User;
use App\Models\TruckType;
use App\Models\TruckSubType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DriverTruckController extends Controller
{
    /**
     * Display listing of all driver trucks assignments.
     */
    public function AllDriverTrucks()
    {
        $relations = ['driver', 'truckType', 'truckSubType'];
        if (Schema::hasColumn('driver_trucks', 'plate_country_id')) {
            $relations[] = 'plateCountry';
        }
        $driverTrucks = DriverTruck::with($relations)->latest()->get();
        $drivers = User::where('role', 'driver')->orderBy('fname', 'asc')->get();
        $truckTypes = TruckType::orderBy('name_en', 'asc')->get();
        $truckSubTypes = TruckSubType::where('is_active', 1)->orderBy('name_en', 'asc')->get();
        $countries = Country::where('is_active', 1)->orderBy('name_en', 'asc')->get();

        return view('admin.backend.driver_truck.all_driver_trucks', compact('driverTrucks', 'drivers', 'truckTypes', 'truckSubTypes', 'countries'));
    }

    /**
     * AJAX endpoint to filter driver trucks based on multiple criteria.
     */
    public function FilterDriverTrucksAjax(Request $request)
    {
        $relations = ['driver', 'truckType', 'truckSubType'];
        if (Schema::hasColumn('driver_trucks', 'plate_country_id')) {
            $relations[] = 'plateCountry';
        }
        $query = DriverTruck::with($relations);

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('truck_type_id')) {
            $query->where('truck_type_id', $request->truck_type_id);
        }

        if ($request->filled('truck_sub_type_id')) {
            if ($request->truck_sub_type_id === 'none') {
                $query->whereNull('truck_sub_type_id');
            } else {
                $query->where('truck_sub_type_id', $request->truck_sub_type_id);
            }
        }

        if ($request->filled('plate_country_id')) {
            $country = Country::find((int) $request->plate_country_id);
            if ($country) {
                $code = strtoupper(trim((string) $country->code));
                $hasCol = Schema::hasColumn('driver_trucks', 'plate_country_id');
                $hasCodeCol = Schema::hasColumn('driver_trucks', 'plate_country_code');
                $query->where(function ($q) use ($country, $code, $hasCol, $hasCodeCol) {
                    if ($hasCol) {
                        $q->orWhere('plate_country_id', $country->id);
                    }
                    if ($hasCodeCol && $code !== '') {
                        $q->orWhereRaw('UPPER(plate_country_code) = ?', [$code]);
                    }
                    if ($code !== '') {
                        $q->orWhere('plate_number', 'like', $code . ' -%')
                          ->orWhere('plate_number', 'like', $code . ' ·%')
                          ->orWhere('plate_number', 'like', $code . ' %');
                    }
                });
            }
        }

        if ($request->has('is_verified') && $request->is_verified !== '' && $request->is_verified !== 'all') {
            $query->where('is_verified', (int)$request->is_verified);
        }

        $driverTrucks = $query->latest()->get();

        $html = view('admin.backend.driver_truck.partials.table_rows', compact('driverTrucks'))->render();

        return response()->json([
            'status' => 'success',
            'html' => $html,
            'count' => count($driverTrucks)
        ]);
    }

    /**
     * Show form to assign a truck to a driver.
     */
    public function AddDriverTruck()
    {
        $drivers = User::where('role', 'driver')->orderBy('fname', 'asc')->get();
        $truckTypes = TruckType::orderBy('name_en', 'asc')->get();
        $truckBrands = \App\Models\TruckBrand::where('is_active', 1)->orderBy('name_en', 'asc')->get();

        return view('admin.backend.driver_truck.add_driver_truck', compact('drivers', 'truckTypes', 'truckBrands'));
    }

    /**
     * AJAX endpoint to retrieve sub-types for a selected truck type.
     */
    public function GetSubTypesAjax($truck_type_id)
    {
        $subTypes = TruckSubType::where('truck_type_id', $truck_type_id)
                                ->where('is_active', 1)
                                ->orderBy('name_en', 'asc')
                                ->get(['id', 'name_en', 'name_ar', 'max_payload']);

        return response()->json($subTypes);
    }

    /**
     * Store a new driver truck assignment.
     */
    public function StoreDriverTruck(Request $request)
    {
        $request->validate([
            'driver_id' => 'required|exists:users,id',
            'truck_type_id' => 'required|exists:truck_types,id',
            'truck_sub_type_id' => 'nullable|exists:truck_sub_types,id',
            'truck_brand_id' => 'nullable|exists:truck_brands,id',
            'truck_model_id' => 'nullable|exists:truck_models,id',
            'manufacturing_year' => 'nullable|integer|min:1980|max:2030',
            'axles_count' => 'nullable|integer|min:1|max:12',
            'plate_number' => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
            'is_verified' => 'nullable|boolean',
        ], [
            'driver_id.required' => 'Please select a driver.',
            'truck_type_id.required' => 'Please select a main truck type.',
        ]);

        $isDefault = $request->has('is_default') ? (int)$request->is_default : 0;

        // If marked as default active truck, reset default status for driver's other trucks
        if ($isDefault === 1) {
            DriverTruck::where('driver_id', $request->driver_id)->update(['is_default' => 0]);
        }

        $driverTruck = new DriverTruck();
        $driverTruck->driver_id = $request->driver_id;
        $driverTruck->truck_type_id = $request->truck_type_id;
        $driverTruck->truck_sub_type_id = $request->truck_sub_type_id ? $request->truck_sub_type_id : null;
        $driverTruck->truck_brand_id = $request->truck_brand_id ? $request->truck_brand_id : null;
        $driverTruck->truck_model_id = $request->truck_model_id ? $request->truck_model_id : null;
        $driverTruck->manufacturing_year = $request->manufacturing_year ? $request->manufacturing_year : null;
        $driverTruck->axles_count = $request->axles_count ? $request->axles_count : null;
        $driverTruck->plate_number = $request->plate_number ? trim($request->plate_number) : null;
        $driverTruck->is_default = $isDefault;
        $driverTruck->is_verified = $request->has('is_verified') ? (int)$request->is_verified : 0;
        $driverTruck->save();

        $notification = [
            'message' => 'Truck Assigned to Driver Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.driver.trucks')->with($notification);
    }

    /**
     * Show form to edit driver truck assignment.
     */
    public function EditDriverTruck($id)
    {
        $driverTruck = DriverTruck::with(['driver', 'truckType', 'truckSubType', 'truckBrand', 'truckModel'])->findOrFail($id);
        $drivers = User::where('role', 'driver')->orderBy('fname', 'asc')->get();
        $truckTypes = TruckType::orderBy('name_en', 'asc')->get();
        $truckBrands = \App\Models\TruckBrand::where('is_active', 1)->orderBy('name_en', 'asc')->get();

        $subTypes = collect();
        if ($driverTruck->truck_type_id) {
            $subTypes = TruckSubType::where('truck_type_id', $driverTruck->truck_type_id)
                                    ->where('is_active', 1)
                                    ->orderBy('name_en', 'asc')
                                    ->get();
        }

        $models = collect();
        if ($driverTruck->truck_brand_id) {
            $models = \App\Models\TruckModel::where('truck_brand_id', $driverTruck->truck_brand_id)
                                            ->where('is_active', 1)
                                            ->orderBy('name_en', 'asc')
                                            ->get();
        }

        return view('admin.backend.driver_truck.edit_driver_truck', compact('driverTruck', 'drivers', 'truckTypes', 'subTypes', 'truckBrands', 'models'));
    }

    /**
     * Update driver truck assignment.
     */
    public function UpdateDriverTruck(Request $request)
    {
        $id = $request->id;
        $driverTruck = DriverTruck::findOrFail($id);

        $request->validate([
            'driver_id' => 'required|exists:users,id',
            'truck_type_id' => 'required|exists:truck_types,id',
            'truck_sub_type_id' => 'nullable|exists:truck_sub_types,id',
            'truck_brand_id' => 'nullable|exists:truck_brands,id',
            'truck_model_id' => 'nullable|exists:truck_models,id',
            'manufacturing_year' => 'nullable|integer|min:1980|max:2030',
            'axles_count' => 'nullable|integer|min:1|max:12',
            'plate_number' => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
            'is_verified' => 'nullable|boolean',
        ], [
            'driver_id.required' => 'Please select a driver.',
            'truck_type_id.required' => 'Please select a main truck type.',
        ]);

        $isDefault = $request->has('is_default') ? (int)$request->is_default : 0;

        // If marked as default, reset other trucks of this driver
        if ($isDefault === 1) {
            DriverTruck::where('driver_id', $request->driver_id)
                        ->where('id', '!=', $id)
                        ->update(['is_default' => 0]);
        }

        $driverTruck->driver_id = $request->driver_id;
        $driverTruck->truck_type_id = $request->truck_type_id;
        $driverTruck->truck_sub_type_id = $request->truck_sub_type_id ? $request->truck_sub_type_id : null;
        $driverTruck->truck_brand_id = $request->truck_brand_id ? $request->truck_brand_id : null;
        $driverTruck->truck_model_id = $request->truck_model_id ? $request->truck_model_id : null;
        $driverTruck->manufacturing_year = $request->manufacturing_year ? $request->manufacturing_year : null;
        $driverTruck->axles_count = $request->axles_count ? $request->axles_count : null;
        $driverTruck->plate_number = $request->plate_number ? trim($request->plate_number) : null;
        $driverTruck->is_default = $isDefault;
        $driverTruck->is_verified = $request->has('is_verified') ? (int)$request->is_verified : 0;
        $driverTruck->save();

        $notification = [
            'message' => 'Driver Truck Assignment Updated Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.driver.trucks')->with($notification);
    }

    /**
     * Delete driver truck assignment.
     */
    public function DeleteDriverTruck($id)
    {
        $driverTruck = DriverTruck::findOrFail($id);
        $driverTruck->delete();

        $notification = [
            'message' => 'Driver Truck Record Deleted Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * AJAX endpoint to update verification status (is_verified).
     */
    public function ChangeVerifiedStatusAjax(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:driver_trucks,id',
            'is_verified' => 'required|in:0,1',
        ]);

        $driverTruck = DriverTruck::findOrFail($request->id);
        $driverTruck->is_verified = (int)$request->is_verified;
        $driverTruck->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Verification status updated successfully.'
        ]);
    }

    /**
     * AJAX endpoint to update default active truck status (is_default).
     */
    public function ChangeDefaultStatusAjax(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:driver_trucks,id',
            'is_default' => 'required|in:0,1',
        ]);

        $driverTruck = DriverTruck::findOrFail($request->id);
        $isDefault = (int)$request->is_default;

        if ($isDefault === 1) {
            // Unset default for other trucks belonging to the same driver
            DriverTruck::where('driver_id', $driverTruck->driver_id)
                        ->where('id', '!=', $driverTruck->id)
                        ->update(['is_default' => 0]);
        }

        $driverTruck->is_default = $isDefault;
        $driverTruck->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Default active truck updated successfully.'
        ]);
    }
}
