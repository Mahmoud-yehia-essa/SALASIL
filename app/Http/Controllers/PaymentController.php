<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\User;

class PaymentController extends Controller
{
    /**
     * Display all payment transactions with KPI stats & search/filter bar.
     */
    public function AllPayments(Request $request)
    {
        $query = Payment::with([
            'invoice.shipment',
            'invoice.scheduledTrip',
            'user'
        ])->latest();

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Method filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // User filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhereHas('invoice', function($iq) use ($search) {
                      $iq->where('invoice_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('fname', 'like', "%{$search}%")
                         ->orWhere('lname', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $payments = $query->get();

        // Statistics
        $stats = [
            'total'          => Payment::count(),
            'completed'      => Payment::where('status', 'completed')->count(),
            'pending'        => Payment::where('status', 'pending')->count(),
            'failed'         => Payment::where('status', 'failed')->count(),
            'refunded'       => Payment::where('status', 'refunded')->count(),
            'total_collected'=> Payment::where('status', 'completed')->sum('amount'),
        ];

        $customers = User::whereIn('role', ['user', 'customer'])->orderBy('fname', 'asc')->get(['id', 'fname', 'lname', 'email']);

        return view('admin.backend.payment.all_payments', compact('payments', 'stats', 'customers'));
    }

    /**
     * Show form to record a new payment transaction.
     */
    public function AddPayment(Request $request)
    {
        $invoices = Invoice::with('user')->orderBy('id', 'desc')->get();

        $selectedInvoiceId = $request->invoice_id ?: null;
        $selectedInvoice   = $selectedInvoiceId ? Invoice::find($selectedInvoiceId) : null;

        return view('admin.backend.payment.add_payment', compact('invoices', 'selectedInvoiceId', 'selectedInvoice'));
    }

    /**
     * Store new payment transaction and sync invoice status.
     */
    public function StorePayment(Request $request)
    {
        $request->validate([
            'invoice_id'     => 'required|exists:invoices,id',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:knet,credit_card,apple_pay,bank_transfer,cash',
            'transaction_id' => 'nullable|string|max:255',
            'receipt'        => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:4096',
            'receipt_url'    => 'nullable|string|max:255',
            'status'         => 'required|in:pending,completed,failed,refunded',
            'paid_at'        => 'nullable|date',
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);

        $receiptUrl = $request->receipt_url ?: null;
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $filename = 'receipt_' . time() . '_' . rand(100,999) . '.' . $file->getClientOriginalExtension();
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

        // Auto-sync invoice status
        $this->syncInvoiceStatus($invoice->id);

        $notification = [
            'message'    => 'Payment Transaction recorded successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.payments')->with($notification);
    }

    /**
     * Show form to edit payment.
     */
    public function EditPayment($id)
    {
        $payment  = Payment::with(['invoice.user', 'user'])->findOrFail($id);
        $invoices = Invoice::with('user')->orderBy('id', 'desc')->get();

        return view('admin.backend.payment.edit_payment', compact('payment', 'invoices'));
    }

    /**
     * Update payment and resync invoice status.
     */
    public function UpdatePayment(Request $request)
    {
        $id      = $request->id;
        $payment = Payment::findOrFail($id);

        $request->validate([
            'invoice_id'     => 'required|exists:invoices,id',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:knet,credit_card,apple_pay,bank_transfer,cash',
            'transaction_id' => 'nullable|string|max:255',
            'receipt'        => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:4096',
            'receipt_url'    => 'nullable|string|max:255',
            'status'         => 'required|in:pending,completed,failed,refunded',
            'paid_at'        => 'nullable|date',
        ]);

        $receiptUrl = $payment->receipt_url;
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $filename = 'receipt_' . time() . '_' . rand(100,999) . '.' . $file->getClientOriginalExtension();
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

        // Resync old and new invoice status
        $this->syncInvoiceStatus($oldInvoiceId);
        if ($oldInvoiceId != $request->invoice_id) {
            $this->syncInvoiceStatus($request->invoice_id);
        }

        $notification = [
            'message'    => 'Payment Transaction #' . $payment->id . ' Updated Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.payments')->with($notification);
    }

    /**
     * Delete payment transaction.
     */
    public function DeletePayment($id)
    {
        $payment   = Payment::findOrFail($id);
        $invoiceId = $payment->invoice_id;
        $payment->delete();

        // Resync invoice status
        $this->syncInvoiceStatus($invoiceId);

        $notification = [
            'message'    => 'Payment Transaction Deleted Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * AJAX: Toggle status of payment and resync invoice.
     */
    public function ChangeStatusAjax(Request $request)
    {
        $payment = Payment::findOrFail($request->payment_id);
        $payment->status  = $request->status;
        $payment->paid_at = ($request->status === 'completed' && !$payment->paid_at) ? now() : $payment->paid_at;
        $payment->save();

        $this->syncInvoiceStatus($payment->invoice_id);

        return response()->json([
            'status'  => 'success',
            'message' => 'Payment status updated to ' . ucfirst($request->status) . '.'
        ]);
    }

    /**
     * AJAX: Fetch JSON details for Modal Drawer preview.
     */
    public function GetPaymentDetailsAjax($id)
    {
        $payment = Payment::with(['invoice.shipment', 'invoice.user', 'user'])->find($id);

        if (!$payment) {
            return response()->json(['status' => 'error', 'message' => 'Payment not found.'], 404);
        }

        return response()->json([
            'status'  => 'success',
            'payment' => [
                'id'             => $payment->id,
                'invoice_id'     => $payment->invoice_id,
                'invoice_number' => $payment->invoice ? $payment->invoice->invoice_number : 'N/A',
                'customer_name'  => $payment->user ? ($payment->user->fname . ' ' . ($payment->user->lname ?? '')) : 'Guest',
                'customer_email' => $payment->user ? $payment->user->email : '—',
                'amount'         => number_format($payment->amount, 2),
                'payment_method' => strtoupper(str_replace('_', ' ', $payment->payment_method)),
                'transaction_id' => $payment->transaction_id ?: '—',
                'receipt_url'    => $payment->receipt_url ? asset($payment->receipt_url) : null,
                'status'         => $payment->status,
                'paid_at'        => $payment->paid_at ? date('Y-m-d H:i', strtotime($payment->paid_at)) : '—',
                'created_at'     => $payment->created_at ? $payment->created_at->format('Y-m-d H:i') : '—',
            ]
        ]);
    }

    /**
     * Helper: Sync invoice status based on completed payments sum.
     */
    private function syncInvoiceStatus($invoiceId)
    {
        $invoice = Invoice::find($invoiceId);
        if (!$invoice) return;

        $completedPaid = Payment::where('invoice_id', $invoiceId)
                                ->where('status', 'completed')
                                ->sum('amount');

        if ($completedPaid >= $invoice->total_amount && $invoice->total_amount > 0) {
            $invoice->status = 'paid';
        } elseif ($completedPaid > 0) {
            $invoice->status = 'partially_paid';
        } else {
            $invoice->status = 'unpaid';
        }

        $invoice->save();
    }
}
