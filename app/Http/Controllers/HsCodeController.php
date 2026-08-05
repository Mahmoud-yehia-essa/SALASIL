<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\HsCodeLookupService;
use Illuminate\Http\Request;
use Exception;

class HsCodeController extends Controller
{
    protected HsCodeLookupService $hsCodeService;

    public function __construct(HsCodeLookupService $hsCodeService)
    {
        $this->hsCodeService = $hsCodeService;
    }

    /**
     * Show HS Code Lookup Interface.
     */
    public function Index()
    {
        return view('admin.backend.shipment.hscode_lookup');
    }

    /**
     * AJAX Search for HS Code details.
     */
    public function Lookup(Request $request)
    {
        $request->validate([
            'hs_code' => 'required|string|max:50',
        ], [
            'hs_code.required' => 'Please enter a valid HS Code.',
            'hs_code.max' => 'HS Code cannot exceed 50 characters.',
        ]);

        $code = trim($request->hs_code);

        try {
            $data = $this->hsCodeService->lookup($code);

            return response()->json([
                'status' => 'success',
                'message' => 'HS Code details retrieved successfully.',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage() ?: 'An error occurred while fetching HS Code details.',
            ], 422);
        }
    }
}
