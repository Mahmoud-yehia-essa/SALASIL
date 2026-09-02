<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HsCodeLookupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class HsCodeController extends Controller
{
    protected HsCodeLookupService $hsCodeService;

    public function __construct(HsCodeLookupService $hsCodeService)
    {
        $this->hsCodeService = $hsCodeService;
    }

    /**
     * Search and retrieve HS Code details, customs duties, restrictions, and AI freight analysis.
     */
    public function lookup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hs_code' => 'required|string|max:50',
        ], [
            'hs_code.required' => 'Please enter a valid HS Code.',
            'hs_code.max'      => 'HS Code cannot exceed 50 characters.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $code = trim($request->hs_code);

        try {
            $data = $this->hsCodeService->lookup($code);

            return response()->json([
                'status'  => true,
                'message' => 'HS Code details and freight analysis retrieved successfully',
                'data'    => $data
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage() ?: 'An error occurred while fetching HS Code details.',
            ], 422);
        }
    }
}
