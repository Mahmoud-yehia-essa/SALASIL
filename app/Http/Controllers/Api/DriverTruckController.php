<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DriverTruck;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DriverTruckController extends Controller
{
    /**
     * List all driver trucks (with multi-criteria filter).
     */
    public function index(Request $request)
    {
        $query = DriverTruck::with([
            'driver:id,fname,lname,email,phone,client_code,photo',
            'truckType',
            'truckSubType',
            'truckBrand',
            'truckModel'
        ]);

        $user = $request->user();

        // If the authenticated user is a driver and no driver_id specified, show only their trucks
        if ($user && $user->role === 'driver' && !$request->filled('driver_id')) {
            $query->where('driver_id', $user->id);
        } elseif ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('truck_type_id')) {
            $query->where('truck_type_id', $request->truck_type_id);
        }

        if ($request->filled('truck_sub_type_id')) {
            $query->where('truck_sub_type_id', $request->truck_sub_type_id);
        }

        if ($request->filled('truck_brand_id')) {
            $query->where('truck_brand_id', $request->truck_brand_id);
        }

        if ($request->filled('truck_model_id')) {
            $query->where('truck_model_id', $request->truck_model_id);
        }

        if ($request->has('is_default')) {
            $query->where('is_default', (int)$request->is_default);
        }

        if ($request->has('is_verified')) {
            $query->where('is_verified', (int)$request->is_verified);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('plate_number', 'like', "%{$search}%")
                  ->orWhereHas('driver', function ($dq) use ($search) {
                      $dq->where('fname', 'like', "%{$search}%")
                         ->orWhere('lname', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('client_code', 'like', "%{$search}%");
                  });
            });
        }

        $trucks = $query->latest()->get();

        return response()->json([
            'status'  => true,
            'message' => 'Driver trucks retrieved successfully',
            'count'   => $trucks->count(),
            'data'    => $trucks
        ], 200);
    }

    /**
     * Get trucks belonging to the currently authenticated driver.
     */
    public function myTrucks(Request $request)
    {
        $user = $request->user();

        $trucks = DriverTruck::with(['truckType', 'truckSubType', 'truckBrand', 'truckModel'])
            ->where('driver_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'status'  => true,
            'message' => 'My trucks retrieved successfully',
            'data'    => $trucks
        ], 200);
    }

    /**
     * Show single driver truck assignment.
     */
    public function show($id)
    {
        $truck = DriverTruck::with([
            'driver:id,fname,lname,email,phone,client_code,photo',
            'truckType',
            'truckSubType',
            'truckBrand',
            'truckModel'
        ])->find($id);

        if (!$truck) {
            return response()->json([
                'status'  => false,
                'message' => 'Driver truck assignment not found'
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Driver truck retrieved successfully',
            'data'    => $truck
        ], 200);
    }

    /**
     * Store new driver truck assignment.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $rules = [
            'driver_id'          => ($user && $user->role === 'driver') ? 'nullable|exists:users,id' : 'required|exists:users,id',
            'truck_type_id'      => 'required|exists:truck_types,id',
            'truck_sub_type_id'  => 'nullable|exists:truck_sub_types,id',
            'truck_brand_id'     => 'nullable|exists:truck_brands,id',
            'truck_model_id'     => 'nullable|exists:truck_models,id',
            'manufacturing_year' => 'nullable|integer|min:1980|max:2030',
            'axles_count'        => 'nullable|integer|min:1|max:12',
            'plate_number'       => 'nullable|string|max:255',
            'is_default'         => 'nullable|boolean',
            'is_verified'        => 'nullable|boolean',
        ];

        $validator = Validator::make($request->all(), $rules, [
            'driver_id.required'     => 'Please select a driver.',
            'truck_type_id.required' => 'Please select a main truck type.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $driverId = ($user && $user->role === 'driver' && !$request->filled('driver_id'))
            ? $user->id
            : $request->driver_id;

        $isDefault = $request->has('is_default') ? (int)$request->is_default : 0;

        // If marked as default, unset other default trucks for this driver
        if ($isDefault === 1) {
            DriverTruck::where('driver_id', $driverId)->update(['is_default' => 0]);
        }

        $driverTruck = new DriverTruck();
        $driverTruck->driver_id          = $driverId;
        $driverTruck->truck_type_id      = $request->truck_type_id;
        $driverTruck->truck_sub_type_id  = $request->filled('truck_sub_type_id') ? $request->truck_sub_type_id : null;
        $driverTruck->truck_brand_id     = $request->filled('truck_brand_id') ? $request->truck_brand_id : null;
        $driverTruck->truck_model_id     = $request->filled('truck_model_id') ? $request->truck_model_id : null;
        $driverTruck->manufacturing_year = $request->filled('manufacturing_year') ? $request->manufacturing_year : null;
        $driverTruck->axles_count        = $request->filled('axles_count') ? $request->axles_count : null;
        $driverTruck->plate_number       = $request->filled('plate_number') ? trim($request->plate_number) : null;
        $driverTruck->is_default         = $isDefault;
        $driverTruck->is_verified        = $request->has('is_verified') ? (int)$request->is_verified : 0;
        $driverTruck->save();

        $driverTruck->load(['driver:id,fname,lname,email,phone,client_code', 'truckType', 'truckSubType', 'truckBrand', 'truckModel']);

        return response()->json([
            'status'  => true,
            'message' => 'Truck assigned to driver successfully',
            'data'    => $driverTruck
        ], 201);
    }

    /**
     * Update driver truck assignment.
     */
    public function update(Request $request, $id)
    {
        $driverTruck = DriverTruck::find($id);
        if (!$driverTruck) {
            return response()->json([
                'status'  => false,
                'message' => 'Driver truck assignment not found'
            ], 404);
        }

        $user = $request->user();

        // Check ownership if driver
        if ($user && $user->role === 'driver' && $driverTruck->driver_id !== $user->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $rules = [
            'driver_id'          => 'nullable|exists:users,id',
            'truck_type_id'      => 'required|exists:truck_types,id',
            'truck_sub_type_id'  => 'nullable|exists:truck_sub_types,id',
            'truck_brand_id'     => 'nullable|exists:truck_brands,id',
            'truck_model_id'     => 'nullable|exists:truck_models,id',
            'manufacturing_year' => 'nullable|integer|min:1980|max:2030',
            'axles_count'        => 'nullable|integer|min:1|max:12',
            'plate_number'       => 'nullable|string|max:255',
            'is_default'         => 'nullable|boolean',
            'is_verified'        => 'nullable|boolean',
        ];

        $validator = Validator::make($request->all(), $rules, [
            'truck_type_id.required' => 'Please select a main truck type.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        if ($request->filled('driver_id') && ($user && $user->role !== 'driver')) {
            $driverTruck->driver_id = $request->driver_id;
        }

        $isDefault = $request->has('is_default') ? (int)$request->is_default : $driverTruck->is_default;

        if ($isDefault === 1) {
            DriverTruck::where('driver_id', $driverTruck->driver_id)
                ->where('id', '!=', $id)
                ->update(['is_default' => 0]);
        }

        $driverTruck->truck_type_id      = $request->truck_type_id;
        $driverTruck->truck_sub_type_id  = $request->filled('truck_sub_type_id') ? $request->truck_sub_type_id : null;
        $driverTruck->truck_brand_id     = $request->filled('truck_brand_id') ? $request->truck_brand_id : null;
        $driverTruck->truck_model_id     = $request->filled('truck_model_id') ? $request->truck_model_id : null;
        $driverTruck->manufacturing_year = $request->filled('manufacturing_year') ? $request->manufacturing_year : null;
        $driverTruck->axles_count        = $request->filled('axles_count') ? $request->axles_count : null;
        $driverTruck->plate_number       = $request->filled('plate_number') ? trim($request->plate_number) : null;
        $driverTruck->is_default         = $isDefault;

        if ($request->has('is_verified')) {
            $driverTruck->is_verified = (int)$request->is_verified;
        }

        $driverTruck->save();

        $driverTruck->load(['driver:id,fname,lname,email,phone,client_code', 'truckType', 'truckSubType', 'truckBrand', 'truckModel']);

        return response()->json([
            'status'  => true,
            'message' => 'Driver truck assignment updated successfully',
            'data'    => $driverTruck
        ], 200);
    }

    /**
     * Set truck as default for the driver.
     */
    public function setDefault(Request $request, $id)
    {
        $driverTruck = DriverTruck::find($id);
        if (!$driverTruck) {
            return response()->json([
                'status'  => false,
                'message' => 'Driver truck assignment not found'
            ], 404);
        }

        $user = $request->user();
        if ($user && $user->role === 'driver' && $driverTruck->driver_id !== $user->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        // Unset other trucks of this driver
        DriverTruck::where('driver_id', $driverTruck->driver_id)
            ->where('id', '!=', $id)
            ->update(['is_default' => 0]);

        $driverTruck->is_default = 1;
        $driverTruck->save();

        return response()->json([
            'status'  => true,
            'message' => 'Default active truck updated successfully',
            'data'    => $driverTruck
        ], 200);
    }

    /**
     * Change verification status.
     */
    public function changeVerifiedStatus(Request $request, $id)
    {
        $driverTruck = DriverTruck::find($id);
        if (!$driverTruck) {
            return response()->json([
                'status'  => false,
                'message' => 'Driver truck assignment not found'
            ], 404);
        }

        if ($request->has('is_verified')) {
            $driverTruck->is_verified = (int)$request->is_verified;
        } else {
            $driverTruck->is_verified = $driverTruck->is_verified ? 0 : 1;
        }

        $driverTruck->save();

        return response()->json([
            'status'  => true,
            'message' => 'Verification status updated successfully',
            'data'    => $driverTruck
        ], 200);
    }

    /**
     * Delete driver truck assignment.
     */
    public function destroy(Request $request, $id)
    {
        $driverTruck = DriverTruck::find($id);
        if (!$driverTruck) {
            return response()->json([
                'status'  => false,
                'message' => 'Driver truck assignment not found'
            ], 404);
        }

        $user = $request->user();
        if ($user && $user->role === 'driver' && $driverTruck->driver_id !== $user->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $driverTruck->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Driver truck assignment deleted successfully'
        ], 200);
    }
}
