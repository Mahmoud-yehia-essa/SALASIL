<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Shipment;
use App\Models\ScheduledTrip;
use App\Models\User;

class InvoiceController extends Controller
{
    /**
     * Display all invoices with stats, filters & pagination/search.
     */
    public function AllInvoices(Request $request)
    {
        $query = Invoice::with([
            'shipment',
            'scheduledTrip',
            'user'
        ])->latest();

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Customer filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhere('shipment_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('fname', 'like', "%{$search}%")
                         ->orWhere('lname', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->get();

        // Statistics
        $stats = [
            'total'          => Invoice::count(),
            'unpaid'         => Invoice::where('status', 'unpaid')->count(),
            'partially_paid' => Invoice::where('status', 'partially_paid')->count(),
            'paid'           => Invoice::where('status', 'paid')->count(),
            'canceled'       => Invoice::where('status', 'canceled')->count(),
            'total_revenue'  => Invoice::where('status', 'paid')->sum('total_amount'),
            'pending_amount' => Invoice::where('status', 'unpaid')->sum('total_amount'),
        ];

        $customers = User::whereIn('role', ['user', 'customer'])->orderBy('fname', 'asc')->get(['id', 'fname', 'lname', 'email', 'phone']);

        return view('admin.backend.invoice.all_invoices', compact('invoices', 'stats', 'customers'));
    }

    /**
     * Show form to edit an invoice.
     */
    public function EditInvoice($id)
    {
        $invoice = Invoice::with(['shipment', 'scheduledTrip', 'user'])->findOrFail($id);
        return view('admin.backend.invoice.edit_invoice', compact('invoice'));
    }

    /**
     * Update invoice breakdown, amounts, due date, status.
     */
    public function UpdateInvoice(Request $request)
    {
        $id      = $request->id;
        $invoice = Invoice::findOrFail($id);

        $request->validate([
            'base_amount'  => 'required|numeric|min:0',
            'tax_amount'   => 'nullable|numeric|min:0',
            'discount'     => 'nullable|numeric|min:0',
            'status'       => 'required|in:unpaid,partially_paid,paid,canceled',
            'due_date'     => 'nullable|date',
        ]);

        $baseAmount = (float) $request->base_amount;
        $taxAmount  = (float) ($request->tax_amount ?? 0);
        $discount   = (float) ($request->discount ?? 0);
        $totalAmount = max(0, ($baseAmount + $taxAmount) - $discount);

        $invoice->update([
            'base_amount'  => $baseAmount,
            'tax_amount'   => $taxAmount,
            'discount'     => $discount,
            'total_amount' => $totalAmount,
            'status'       => $request->status,
            'due_date'     => $request->due_date ?: null,
        ]);

        $notification = [
            'message'    => 'Invoice #' . $invoice->invoice_number . ' Updated Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.invoices')->with($notification);
    }

    /**
     * Delete an invoice.
     */
    public function DeleteInvoice($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();

        $notification = [
            'message'    => 'Invoice Deleted Successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * Change invoice status via AJAX directly from table.
     */
    public function ChangeStatusAjax(Request $request)
    {
        $invoice = Invoice::findOrFail($request->invoice_id);
        $invoice->status = $request->status;
        $invoice->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Invoice status updated to ' . ucfirst(str_replace('_', ' ', $request->status)) . '.'
        ]);
    }

    /**
     * AJAX: Fetch invoice details for printable view / drawer.
     */
    public function GetInvoiceDetailsAjax($id)
    {
        $invoice = Invoice::with(['shipment.pickupCity', 'shipment.dropoffCity', 'scheduledTrip', 'user'])->find($id);

        if (!$invoice) {
            return response()->json(['status' => 'error', 'message' => 'Invoice not found.'], 404);
        }

        return response()->json([
            'status'  => 'success',
            'invoice' => [
                'id'             => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_name'  => $invoice->user ? ($invoice->user->fname . ' ' . ($invoice->user->lname ?? '')) : 'Guest',
                'customer_email' => $invoice->user ? $invoice->user->email : '—',
                'customer_phone' => $invoice->user ? $invoice->user->phone : '—',
                'shipment_id'    => $invoice->shipment_id ? '#' . $invoice->shipment_id : 'N/A',
                'shipment_name'  => $invoice->shipment ? ($invoice->shipment->shipment_name ?: 'Shipment #' . $invoice->shipment->id) : '—',
                'pickup_location'=> $invoice->shipment && $invoice->shipment->pickupCity ? $invoice->shipment->pickupCity->name_en : '—',
                'dropoff_location'=> $invoice->shipment && $invoice->shipment->dropoffCity ? $invoice->shipment->dropoffCity->name_en : '—',
                'base_amount'    => number_format($invoice->base_amount, 2),
                'tax_amount'     => number_format($invoice->tax_amount, 2),
                'discount'       => number_format($invoice->discount, 2),
                'total_amount'   => number_format($invoice->total_amount, 2),
                'status'         => $invoice->status,
                'issued_at'      => $invoice->issued_at ? date('Y-m-d H:i', strtotime($invoice->issued_at)) : ($invoice->created_at ? $invoice->created_at->format('Y-m-d H:i') : '—'),
                'due_date'       => $invoice->due_date ? date('Y-m-d', strtotime($invoice->due_date)) : '—',
            ]
        ]);
    }
}
