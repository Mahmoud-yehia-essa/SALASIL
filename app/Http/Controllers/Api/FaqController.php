<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Faq;

class FaqController extends Controller
{
    /**
     * Get list of active FAQs, with optional category filtering.
     */
    public function index(Request $request)
    {
        $query = Faq::where('status', 1)->orderBy('order_sequence', 'asc')->latest('id');

        if ($request->has('category') && !empty($request->category) && strtolower($request->category) !== 'all') {
            $query->where('category', $request->category);
        }

        $faqs = $query->get()->map(function ($faq) {
            return [
                'id' => (string) $faq->id,
                'question' => (string) $faq->question,
                'answer' => (string) $faq->answer,
                'category' => (string) $faq->category,
                'order_sequence' => (int) $faq->order_sequence,
            ];
        });

        // Also fetch unique categories
        $categories = Faq::where('status', 1)->distinct()->pluck('category')->filter()->values();

        return response()->json([
            'status' => true,
            'message' => 'FAQs retrieved successfully',
            'categories' => $categories,
            'data' => $faqs,
        ], 200);
    }
}
