<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * List all payment transactions with filters.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Payment::with([
            'invoice.shipment',
            'invoice.scheduledTrip.route',
            'user:id,fname,lname,email,phone,client_code,photo'
        ])->latest();

        // Customer scoping
        if ($user && in_array($user->role, ['individual_customer', 'company_customer']) && !$request->filled('user_id')) {
            $query->where('user_id', $user->id);
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Method filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Invoice filter
        if ($request->filled('invoice_id')) {
            $query->where('invoice_id', $request->invoice_id);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhereHas('invoice', function ($iq) use ($search) {
                      $iq->where('invoice_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('fname', 'like', "%{$search}%")
                         ->orWhere('lname', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $payments = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'status'  => true,
            'message' => 'Payments retrieved successfully',
            'data'    => $payments
        ], 200);
    }

    /**
     * Get payments belonging to currently authenticated customer.
     */
    public function myPayments(Request $request)
    {
        $user = $request->user();

        $query = Payment::with([
            'invoice.shipment',
            'invoice.scheduledTrip.route'
        ])->where('user_id', $user->id)->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'status'  => true,
            'message' => 'My payments retrieved successfully',
            'data'    => $payments
        ], 200);
    }

    /**
     * Get overview KPI statistics for payments.
     */
    public function statistics(Request $request)
    {
        $user = $request->user();
        $query = Payment::query();

        if ($user && in_array($user->role, ['individual_customer', 'company_customer'])) {
            $query->where('user_id', $user->id);
        }

        $stats = [
            'total'           => (clone $query)->count(),
            'completed'       => (clone $query)->where('status', 'completed')->count(),
            'pending'         => (clone $query)->where('status', 'pending')->count(),
            'failed'          => (clone $query)->where('status', 'failed')->count(),
            'refunded'        => (clone $query)->where('status', 'refunded')->count(),
            'total_collected' => (clone $query)->where('status', 'completed')->sum('amount') ?? 0,
        ];

        return response()->json([
            'status'  => true,
            'message' => 'Payment statistics retrieved successfully',
            'data'    => $stats
        ], 200);
    }

    /**
     * Show single payment transaction details.
     */
    public function show($id)
    {
        $payment = Payment::with([
            'invoice.shipment',
            'invoice.scheduledTrip.route',
            'user:id,fname,lname,email,phone,client_code,photo'
        ])->find($id);

        if (!$payment) {
            return response()->json([
                'status'  => false,
                'message' => 'Payment transaction not found'
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Payment transaction retrieved successfully',
            'data'    => $payment
        ], 200);
    }

    /**
     * Record a new payment transaction and synchronize invoice status.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_id'     => 'required|exists:invoices,id',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:knet,credit_card,apple_pay,bank_transfer,cash',
            'transaction_id' => 'nullable|string|max:255',
            'receipt'        => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:4096',
            'receipt_url'    => 'nullable|string|max:255',
            'status'         => 'required|in:pending,completed,failed,refunded',
            'paid_at'        => 'nullable|date',
        ], [
            'invoice_id.required'     => 'Invoice ID is required.',
            'amount.required'         => 'Payment amount is required.',
            'payment_method.required' => 'Payment method is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $invoice = Invoice::findOrFail($request->invoice_id);

        $receiptUrl = $request->receipt_url ?: null;
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $filename = 'receipt_' . time() . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/receipts'), $filename);
            $receiptUrl = 'upload/receipts/' . $filename;
        }

        $payment = Payment::create([
            'invoice_id'     => $invoice->id,
            'user_id'        => $invoice->user_id,
            'amount'         => $request->amount,
            'payment_method' => $request->payment_method,
            'transaction_id' => $request->transaction_id ?: ('TXN-' . strtoupper(bin2hex(random_bytes(4)))),
            'receipt_url'    => $receiptUrl,
            'status'         => $request->status,
            'paid_at'        => $request->paid_at ? date('Y-m-d H:i:s', strtotime($request->paid_at)) : ($request->status === 'completed' ? now() : null),
        ]);

        // Auto-sync invoice & shipment status
        $this->syncInvoiceStatus($invoice->id);

        $payment->load(['invoice', 'user']);

        return response()->json([
            'status'  => true,
            'message' => 'Payment transaction recorded and invoice status updated successfully',
            'data'    => $payment
        ], 201);
    }

    /**
     * Update payment transaction.
     */
    public function update(Request $request, $id)
    {
        $payment = Payment::find($id);
        if (!$payment) {
            return response()->json([
                'status'  => false,
                'message' => 'Payment transaction not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'invoice_id'     => 'required|exists:invoices,id',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:knet,credit_card,apple_pay,bank_transfer,cash',
            'transaction_id' => 'nullable|string|max:255',
            'receipt'        => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:4096',
            'receipt_url'    => 'nullable|string|max:255',
            'status'         => 'required|in:pending,completed,failed,refunded',
            'paid_at'        => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $receiptUrl = $payment->receipt_url;
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $filename = 'receipt_' . time() . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/receipts'), $filename);
            $receiptUrl = 'upload/receipts/' . $filename;
        } elseif ($request->filled('receipt_url')) {
            $receiptUrl = $request->receipt_url;
        }

        $oldInvoiceId = $payment->invoice_id;

        $payment->update([
            'invoice_id'     => $request->invoice_id,
            'amount'         => $request->amount,
            'payment_method' => $request->payment_method,
            'transaction_id' => $request->transaction_id ?: $payment->transaction_id,
            'receipt_url'    => $receiptUrl,
            'status'         => $request->status,
            'paid_at'        => $request->paid_at ? date('Y-m-d H:i:s', strtotime($request->paid_at)) : ($request->status === 'completed' ? ($payment->paid_at ?: now()) : null),
        ]);

        $this->syncInvoiceStatus($oldInvoiceId);
        if ($oldInvoiceId != $request->invoice_id) {
            $this->syncInvoiceStatus($request->invoice_id);
        }

        $payment->load(['invoice', 'user']);

        return response()->json([
            'status'  => true,
            'message' => 'Payment transaction updated successfully',
            'data'    => $payment
        ], 200);
    }

    /**
     * Change payment status and resync invoice.
     */
    public function changeStatus(Request $request, $id)
    {
        $payment = Payment::find($id);
        if (!$payment) {
            return response()->json([
                'status'  => false,
                'message' => 'Payment transaction not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,completed,failed,refunded',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $payment->status = $request->status;
        $payment->paid_at = ($request->status === 'completed' && !$payment->paid_at) ? now() : $payment->paid_at;
        $payment->save();

        $this->syncInvoiceStatus($payment->invoice_id);

        return response()->json([
            'status'  => true,
            'message' => 'Payment status updated to ' . ucfirst($request->status) . ' successfully',
            'data'    => $payment
        ], 200);
    }

    /**
     * Delete payment and resync invoice.
     */
    public function destroy($id)
    {
        $payment = Payment::find($id);
        if (!$payment) {
            return response()->json([
                'status'  => false,
                'message' => 'Payment transaction not found'
            ], 404);
        }

        $invoiceId = $payment->invoice_id;
        $payment->delete();

        $this->syncInvoiceStatus($invoiceId);

        return response()->json([
            'status'  => true,
            'message' => 'Payment transaction deleted successfully'
        ], 200);
    }

    /**
     * Sync invoice and shipment status based on payments.
     */
    private function syncInvoiceStatus($invoiceId)
    {
        $invoice = Invoice::find($invoiceId);
        if (!$invoice) return;

        $completedPaid = (float) Payment::where('invoice_id', $invoiceId)
                                        ->where('status', 'completed')
                                        ->sum('amount');

        if ($completedPaid >= (float)$invoice->total_amount && (float)$invoice->total_amount > 0) {
            $invoice->status = 'paid';
        } elseif ($completedPaid > 0) {
            $invoice->status = 'partially_paid';
        } else {
            $invoice->status = 'unpaid';
        }

        $invoice->save();

        // If this invoice belongs to a shipment, sync shipment's payment_status
        if ($invoice->shipment_id) {
            $shipment = Shipment::find($invoice->shipment_id);
            if ($shipment) {
                $shipment->payment_status = $invoice->status;
                $shipment->save();
            }
        }
    }
}
