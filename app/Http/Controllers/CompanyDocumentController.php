<?php

namespace App\Http\Controllers;

use App\Models\CompanyRequiredDocument;
use Illuminate\Http\Request;

class CompanyDocumentController extends Controller
{
    /**
     * Display listing of all company document requirements.
     */
    public function AllCompanyDocuments()
    {
        $documents = CompanyRequiredDocument::whereNull('user_id')
            ->withCount('userDocuments')
            ->orderBy('sort_order', 'asc')
            ->get();

        return view('admin.backend.company_document.all_documents', compact('documents'));
    }

    /**
     * Show form to add a new document requirement.
     */
    public function AddCompanyDocument()
    {
        return view('admin.backend.company_document.add_document');
    }

    /**
     * Store newly created document requirement.
     */
    public function StoreCompanyDocument(Request $request)
    {
        $request->validate([
            'name_ar'        => 'required|string|max:150',
            'name_en'        => 'required|string|max:150',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'is_required'    => 'nullable|boolean',
            'is_active'      => 'nullable|boolean',
            'sort_order'     => 'nullable|integer',
        ], [
            'name_ar.required' => 'اسم المستند بالعربية مطلوب.',
            'name_en.required' => 'Document name in English is required.',
        ]);

        $doc = new CompanyRequiredDocument();
        $doc->name_ar        = trim($request->name_ar);
        $doc->name_en        = trim($request->name_en);
        $doc->description_ar = $request->description_ar ? trim($request->description_ar) : null;
        $doc->description_en = $request->description_en ? trim($request->description_en) : null;
        $doc->is_required    = $request->has('is_required') ? (bool)$request->is_required : true;
        $doc->is_active      = $request->has('is_active') ? (bool)$request->is_active : true;
        $doc->sort_order     = $request->sort_order ? (int)$request->sort_order : 0;
        $doc->save();

        $notification = [
            'message'    => 'تم إضافة متطلب المستند بنجاح',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.company.documents')->with($notification);
    }

    /**
     * Show form to edit document requirement.
     */
    public function EditCompanyDocument($id)
    {
        $document = CompanyRequiredDocument::findOrFail($id);
        return view('admin.backend.company_document.edit_document', compact('document'));
    }

    /**
     * Update document requirement.
     */
    public function UpdateCompanyDocument(Request $request)
    {
        $docId = $request->id;
        $doc = CompanyRequiredDocument::findOrFail($docId);

        $request->validate([
            'name_ar'        => 'required|string|max:150',
            'name_en'        => 'required|string|max:150',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'is_required'    => 'nullable|boolean',
            'is_active'      => 'nullable|boolean',
            'sort_order'     => 'nullable|integer',
        ]);

        $doc->name_ar        = trim($request->name_ar);
        $doc->name_en        = trim($request->name_en);
        $doc->description_ar = $request->description_ar ? trim($request->description_ar) : null;
        $doc->description_en = $request->description_en ? trim($request->description_en) : null;
        $doc->is_required    = $request->has('is_required') ? (bool)$request->is_required : false;
        $doc->is_active      = $request->has('is_active') ? (bool)$request->is_active : false;
        $doc->sort_order     = $request->sort_order ? (int)$request->sort_order : 0;
        $doc->save();

        $notification = [
            'message'    => 'تم تحديث بيانات المستند بنجاح',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.company.documents')->with($notification);
    }

    /**
     * Quick AJAX toggle for Required (مطلوب) vs Optional (اختياري).
     */
    public function ToggleRequiredAjax(Request $request)
    {
        $doc = CompanyRequiredDocument::find($request->id);
        if (!$doc) {
            return response()->json(['status' => false, 'message' => 'Document not found'], 404);
        }

        $doc->is_required = !$doc->is_required;
        $doc->save();

        return response()->json([
            'status'      => true,
            'is_required' => $doc->is_required,
            'message'     => $doc->is_required ? 'تم تغيير المستند إلى مطلوب' : 'تم تغيير المستند إلى اختياري',
        ]);
    }

    /**
     * Quick AJAX toggle for Active status.
     */
    public function ChangeStatusAjax(Request $request)
    {
        $doc = CompanyRequiredDocument::find($request->id);
        if (!$doc) {
            return response()->json(['status' => false, 'message' => 'Document not found'], 404);
        }

        $doc->is_active = !$doc->is_active;
        $doc->save();

        return response()->json([
            'status'    => true,
            'is_active' => $doc->is_active,
            'message'   => $doc->is_active ? 'تم تفعيل المستند' : 'تم تعطيل المستند',
        ]);
    }

    /**
     * Delete document requirement.
     */
    public function DeleteCompanyDocument($id)
    {
        $doc = CompanyRequiredDocument::findOrFail($id);
        $doc->delete();

        $notification = [
            'message'    => 'تم حذف متطلب المستند بنجاح',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.company.documents')->with($notification);
    }
}
