<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Faq;

class FaqController extends Controller
{
    /**
     * Display all FAQs.
     */
    public function AllFaqs()
    {
        $faqs = Faq::orderBy('order_sequence', 'asc')->latest('id')->get();
        return view('admin.backend.faq.all_faqs', compact('faqs'));
    }

    /**
     * Show form to add a new FAQ.
     */
    public function AddFaq()
    {
        $categories = ['General', 'Shipping', 'Payment', 'Account'];
        $nextOrder = (Faq::max('order_sequence') ?? 0) + 1;
        return view('admin.backend.faq.add_faq', compact('categories', 'nextOrder'));
    }

    /**
     * Store newly created FAQ.
     */
    public function StoreFaq(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'category' => 'required|string|max:50',
            'order_sequence' => 'nullable|integer|min:0',
        ]);

        Faq::create([
            'question' => $request->question,
            'answer' => $request->answer,
            'category' => $request->category,
            'order_sequence' => $request->order_sequence ?? 0,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        $notification = [
            'message' => 'FAQ created successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.faqs')->with($notification);
    }

    /**
     * Show form to edit an existing FAQ.
     */
    public function EditFaq($id)
    {
        $faq = Faq::findOrFail($id);
        $categories = ['General', 'Shipping', 'Payment', 'Account'];
        return view('admin.backend.faq.edit_faq', compact('faq', 'categories'));
    }

    /**
     * Update existing FAQ.
     */
    public function UpdateFaq(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:faqs,id',
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'category' => 'required|string|max:50',
            'order_sequence' => 'nullable|integer|min:0',
        ]);

        $faq = Faq::findOrFail($request->id);
        $faq->update([
            'question' => $request->question,
            'answer' => $request->answer,
            'category' => $request->category,
            'order_sequence' => $request->order_sequence ?? 0,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        $notification = [
            'message' => 'FAQ updated successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.faqs')->with($notification);
    }

    /**
     * Change FAQ Status via AJAX.
     */
    public function ChangeStatusAjax(Request $request)
    {
        $faq = Faq::findOrFail($request->id);
        $faq->status = $request->status ? 1 : 0;
        $faq->save();

        return response()->json([
            'status' => true,
            'message' => 'FAQ status updated successfully!'
        ]);
    }

    /**
     * Delete an FAQ.
     */
    public function DeleteFaq($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->delete();

        $notification = [
            'message' => 'FAQ deleted successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }
}
