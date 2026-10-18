<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyRequiredDocument;
use App\Models\UserCompanyDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class CompanyDocumentController extends Controller
{
    /**
     * Get company documents list for authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $isCompany = in_array($user->role, ['company_customer', 'company']);
        if (!$isCompany) {
            return response()->json([
                'status'  => true,
                'message' => 'Individual clients do not require company documents',
                'data'    => [
                    'is_company_account' => false,
                    'account_type'       => 'Individual Account',
                    'documents'          => [],
                    'summary'            => [
                        'total_required'    => 0,
                        'attached_required' => 0,
                        'total_attached'    => 0,
                        'total_count'       => 0,
                    ],
                ],
            ], 200);
        }

        // 1. Fetch all active required document configurations (global + user-specific)
        $requiredDocs = CompanyRequiredDocument::where('is_active', 1)
            ->where(function ($q) use ($user) {
                $q->whereNull('user_id')->orWhere('user_id', $user->id);
            })
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // 2. Fetch all uploaded documents by this user
        $userDocs = UserCompanyDocument::where('user_id', $user->id)->get();
        $uploadedByReqId = $userDocs->whereNotNull('required_document_id')->keyBy('required_document_id');
        $customDocs = $userDocs->whereNull('required_document_id');

        $docs = [];
        $totalRequired = 0;
        $attachedRequired = 0;
        $totalAttached = $userDocs->count();

        // Check if user has specific required documents configured by admin
        $companyProfile = $user->companyProfile;
        $userSpecificRequiredIds = ($companyProfile && is_array($companyProfile->required_documents))
            ? array_map('intval', $companyProfile->required_documents)
            : null;

        foreach ($requiredDocs as $req) {
            $userDoc = $uploadedByReqId->get($req->id);
            $isUploaded = ($userDoc !== null);

            $isRequired = ($userSpecificRequiredIds !== null)
                ? in_array((int)$req->id, $userSpecificRequiredIds, true)
                : (bool)$req->is_required;

            if ($isRequired) {
                $totalRequired++;
                if ($isUploaded) {
                    $attachedRequired++;
                }
            }

            $docs[] = [
                'id'                   => $userDoc ? (string)$userDoc->id : 'req-' . $req->id,
                'required_document_id' => $req->id,
                'title'                => $req->name_en,
                'title_ar'             => $req->name_ar,
                'description'          => $req->description_en ?: 'Required official document for company verification',
                'description_ar'       => $req->description_ar ?: 'مستند رسمي مطلوب للتحقق من هوية ونشاط الشركة',
                'is_required'          => (bool)$isRequired,
                'is_uploaded'          => $isUploaded,
                'file_url'             => $userDoc ? $userDoc->file_url : null,
                'file_name'            => $userDoc ? $userDoc->file_name : null,
                'file_size'            => $userDoc ? $userDoc->file_size : null,
                'file_type'            => $userDoc ? $userDoc->file_type : null,
                'status'               => $userDoc ? $userDoc->status : ($isRequired ? 'required' : 'optional'),
                'rejection_reason'     => $userDoc ? $userDoc->rejection_reason : null,
                'expiry_date'          => ($userDoc && $userDoc->expiry_date) ? $userDoc->expiry_date->format('Y-m-d') : null,
                'upload_date'          => $userDoc ? $userDoc->created_at->format('M d, Y') : null,
            ];
        }

        foreach ($customDocs as $custom) {
            $docs[] = [
                'id'                   => (string)$custom->id,
                'required_document_id' => null,
                'title'                => $custom->title,
                'title_ar'             => $custom->title,
                'description'          => 'Additional Company Document / Permit',
                'description_ar'       => 'مستند إضافي / تصريح للشركة',
                'is_required'          => false,
                'is_uploaded'          => true,
                'file_url'             => $custom->file_url,
                'file_name'            => $custom->file_name,
                'file_size'            => $custom->file_size,
                'file_type'            => $custom->file_type,
                'status'               => $custom->status,
                'rejection_reason'     => $custom->rejection_reason,
                'expiry_date'          => $custom->expiry_date ? $custom->expiry_date->format('Y-m-d') : null,
                'upload_date'          => $custom->created_at->format('M d, Y'),
            ];
        }

        return response()->json([
            'status'  => true,
            'message' => 'Company documents retrieved successfully',
            'data'    => [
                'documents' => $docs,
                'summary'   => [
                    'total_required'    => $totalRequired,
                    'attached_required' => $attachedRequired,
                    'total_attached'    => $totalAttached,
                    'total_count'       => count($docs),
                ],
            ],
        ], 200);
    }

    /**
     * Upload a company document file.
     */
    public function upload(Request $request)
    {
        $user = $request->user();

        $isCompany = in_array($user->role, ['company_customer', 'company']);
        if (!$isCompany) {
            return response()->json([
                'status'  => true,
                'message' => 'Individual clients do not require company documents',
                'data'    => [
                    'is_company_account' => false,
                    'account_type'       => 'Individual Account',
                    'documents'          => [],
                    'summary'            => [
                        'total_required'    => 0,
                        'attached_required' => 0,
                        'total_attached'    => 0,
                        'total_count'       => 0,
                    ],
                ],
            ], 200);
        }

        $validator = Validator::make($request->all(), [
            'file'                 => 'required|file|max:15360|mimes:pdf,docx,jpg,jpeg,png',
            'document_id'          => 'nullable|string',
            'required_document_id' => 'nullable|integer',
            'title'                => 'nullable|string|max:150',
            'expiry_date'          => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $origName = $file->getClientOriginalName();
        $sizeBytes = $file->getSize();

        // Format size
        if ($sizeBytes >= 1048576) {
            $formattedSize = round($sizeBytes / 1048576, 1) . ' MB';
        } else {
            $formattedSize = round($sizeBytes / 1024, 1) . ' KB';
        }

        // Destination folder
        $destinationPath = public_path('upload/company_documents');
        if (!File::isDirectory($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true, true);
        }

        $filename = 'comp_doc_' . $user->id . '_' . time() . '_' . uniqid() . '.' . $extension;
        $file->move($destinationPath, $filename);
        $relativeFilePath = 'upload/company_documents/' . $filename;

        // Determine required document ID if provided
        $reqDocId = $request->input('required_document_id');
        if (!$reqDocId && $request->filled('document_id')) {
            $rawId = $request->input('document_id');
            if (str_starts_with($rawId, 'req-')) {
                $reqDocId = (int)str_replace('req-', '', $rawId);
            } elseif (is_numeric($rawId)) {
                $candidate = UserCompanyDocument::where('id', $rawId)->where('user_id', $user->id)->first();
                if ($candidate && $candidate->required_document_id) {
                    $reqDocId = $candidate->required_document_id;
                }
            }
        }

        $title = $request->input('title');
        $isRequired = false;
        if ($reqDocId) {
            $reqConfig = CompanyRequiredDocument::find($reqDocId);
            if ($reqConfig) {
                if (empty($title)) {
                    $title = $reqConfig->name_en;
                }
                $userSpecificRequiredIds = ($user->companyProfile && is_array($user->companyProfile->required_documents))
                    ? array_map('intval', $user->companyProfile->required_documents)
                    : null;
                $isRequired = ($userSpecificRequiredIds !== null)
                    ? in_array((int)$reqDocId, $userSpecificRequiredIds, true)
                    : (bool)$reqConfig->is_required;
            }
        }

        if (empty($title)) {
            $title = pathinfo($origName, PATHINFO_FILENAME);
        }

        // Check if updating an existing record
        $existing = null;
        if ($reqDocId) {
            $existing = UserCompanyDocument::where('user_id', $user->id)
                ->where('required_document_id', $reqDocId)
                ->first();
        }

        if ($existing) {
            // Delete old file
            if ($existing->file_path && File::exists(public_path($existing->file_path))) {
                File::delete(public_path($existing->file_path));
            }

            $existing->update([
                'title'            => $title,
                'file_path'        => $relativeFilePath,
                'file_name'        => $origName,
                'file_size'        => $formattedSize,
                'file_type'        => $extension,
                'status'           => 'pending',
                'rejection_reason' => null,
                'expiry_date'      => $request->input('expiry_date'),
            ]);
            $saved = $existing;
        } else {
            $saved = UserCompanyDocument::create([
                'user_id'              => $user->id,
                'required_document_id' => $reqDocId,
                'title'                => $title,
                'file_path'            => $relativeFilePath,
                'file_name'            => $origName,
                'file_size'            => $formattedSize,
                'file_type'            => $extension,
                'status'               => 'pending',
                'expiry_date'          => $request->input('expiry_date'),
            ]);
        }

        $item = [
            'id'                   => (string)$saved->id,
            'required_document_id' => $saved->required_document_id,
            'title'                => $saved->title,
            'description'          => 'Official uploaded company document',
            'is_required'          => $isRequired,
            'is_uploaded'          => true,
            'file_url'             => $saved->file_url,
            'file_name'            => $saved->file_name,
            'file_size'            => $saved->file_size,
            'file_type'            => $saved->file_type,
            'status'               => $saved->status,
            'rejection_reason'     => $saved->rejection_reason,
            'expiry_date'          => $saved->expiry_date ? $saved->expiry_date->format('Y-m-d') : null,
            'upload_date'          => $saved->created_at->format('M d, Y'),
        ];

        return response()->json([
            'status'  => true,
            'message' => 'Document uploaded successfully',
            'data'    => $item,
        ], 200);
    }

    /**
     * Remove an uploaded company document.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $isCompany = in_array($user->role, ['company_customer', 'company']);
        if (!$isCompany) {
            return response()->json([
                'status'  => true,
                'message' => 'Individual clients do not require company documents',
                'data'    => [
                    'is_company_account' => false,
                    'account_type'       => 'Individual Account',
                    'documents'          => [],
                    'summary'            => [
                        'total_required'    => 0,
                        'attached_required' => 0,
                        'total_attached'    => 0,
                        'total_count'       => 0,
                    ],
                ],
            ], 200);
        }

        $doc = UserCompanyDocument::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$doc) {
            return response()->json([
                'status'  => false,
                'message' => 'Document not found',
            ], 404);
        }

        if ($doc->file_path && File::exists(public_path($doc->file_path))) {
            File::delete(public_path($doc->file_path));
        }

        $doc->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Document removed successfully',
        ], 200);
    }
}
