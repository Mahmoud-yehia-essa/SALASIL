<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserAddressController extends Controller
{
    /**
     * Display a listing of user addresses.
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $addresses = UserAddress::where('user_id', $userId)
            ->orderBy('is_default', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status'  => true,
            'message' => 'Addresses retrieved successfully',
            'data'    => $addresses,
        ], 200);
    }

    /**
     * Store a newly created address for user.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'type'                => 'nullable|string|in:home,work,other',
            'title'               => 'nullable|string|max:100',
            'country'             => 'required|string|max:100',
            'city'                => 'required|string|max:100',
            'area'                => 'required|string|max:100',
            'street_and_building' => 'required|string|max:255',
            'block'               => 'nullable|string|max:50',
            'floor_flat'          => 'nullable|string|max:100',
            'phone'               => 'nullable|string|max:50',
            'instructions'        => 'nullable|string',
            'latitude'            => 'nullable|numeric',
            'longitude'           => 'nullable|numeric',
            'is_default'          => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $type = strtolower($request->input('type', 'home'));
            $title = $request->input('title');
            if (empty($title)) {
                $title = ucfirst($type);
            }

            $count = UserAddress::where('user_id', $user->id)->count();
            $isDefault = $request->boolean('is_default') || $count === 0;

            if ($isDefault) {
                UserAddress::where('user_id', $user->id)->update(['is_default' => false]);
            }

            $address = UserAddress::create([
                'user_id'             => $user->id,
                'type'                => $type,
                'title'               => $title,
                'country'             => trim($request->country),
                'city'                => trim($request->city),
                'area'                => trim($request->area),
                'street_and_building' => trim($request->street_and_building),
                'block'               => $request->filled('block') ? trim($request->block) : null,
                'floor_flat'          => $request->filled('floor_flat') ? trim($request->floor_flat) : null,
                'phone'               => $request->filled('phone') ? trim($request->phone) : null,
                'instructions'        => $request->filled('instructions') ? trim($request->instructions) : null,
                'latitude'            => $request->filled('latitude') ? (float) $request->latitude : null,
                'longitude'           => $request->filled('longitude') ? (float) $request->longitude : null,
                'is_default'          => $isDefault,
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Address added successfully',
                'data'    => $address,
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Failed to save address: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing address.
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();

        $address = UserAddress::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$address) {
            return response()->json([
                'status'  => false,
                'message' => 'Address not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'type'                => 'nullable|string|in:home,work,other',
            'title'               => 'nullable|string|max:100',
            'country'             => 'required|string|max:100',
            'city'                => 'required|string|max:100',
            'area'                => 'required|string|max:100',
            'street_and_building' => 'required|string|max:255',
            'block'               => 'nullable|string|max:50',
            'floor_flat'          => 'nullable|string|max:100',
            'phone'               => 'nullable|string|max:50',
            'instructions'        => 'nullable|string',
            'latitude'            => 'nullable|numeric',
            'longitude'           => 'nullable|numeric',
            'is_default'          => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $isDefault = $request->boolean('is_default');
            if ($isDefault && !$address->is_default) {
                UserAddress::where('user_id', $user->id)->update(['is_default' => false]);
            }

            $type = $request->filled('type') ? strtolower($request->type) : $address->type;
            $title = $request->filled('title') ? trim($request->title) : $address->title;

            $address->update([
                'type'                => $type,
                'title'               => $title,
                'country'             => trim($request->country),
                'city'                => trim($request->city),
                'area'                => trim($request->area),
                'street_and_building' => trim($request->street_and_building),
                'block'               => $request->filled('block') ? trim($request->block) : null,
                'floor_flat'          => $request->filled('floor_flat') ? trim($request->floor_flat) : null,
                'phone'               => $request->filled('phone') ? trim($request->phone) : null,
                'instructions'        => $request->filled('instructions') ? trim($request->instructions) : null,
                'latitude'            => $request->filled('latitude') ? (float) $request->latitude : $address->latitude,
                'longitude'           => $request->filled('longitude') ? (float) $request->longitude : $address->longitude,
                'is_default'          => $isDefault,
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Address updated successfully',
                'data'    => $address->fresh(),
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update address: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified address.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $address = UserAddress::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$address) {
            return response()->json([
                'status'  => false,
                'message' => 'Address not found',
            ], 404);
        }

        try {
            DB::beginTransaction();

            $wasDefault = $address->is_default;
            $address->delete();

            // If we deleted the default address, designate the latest remaining address as default
            if ($wasDefault) {
                $next = UserAddress::where('user_id', $user->id)
                    ->orderBy('id', 'desc')
                    ->first();
                if ($next) {
                    $next->update(['is_default' => true]);
                }
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Address deleted successfully',
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete address: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set the specified address as default.
     */
    public function setDefault(Request $request, $id)
    {
        $user = $request->user();

        $address = UserAddress::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$address) {
            return response()->json([
                'status'  => false,
                'message' => 'Address not found',
            ], 404);
        }

        try {
            DB::beginTransaction();

            UserAddress::where('user_id', $user->id)->update(['is_default' => false]);
            $address->update(['is_default' => true]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Default address set successfully',
                'data'    => $address->fresh(),
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Failed to set default address: ' . $e->getMessage(),
            ], 500);
        }
    }
}
