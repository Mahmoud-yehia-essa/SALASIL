<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
    /**
     * List all invoices with filters and relationship loading.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Invoice::with([
            'shipment.pickupCity',
            'shipment.dropoffCity',
            'scheduledTrip.route',
            'user:id,fname,lname,email,phone,client_code,photo',
            'payments'
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

        // Shipment filter
        if ($request->filled('shipment_id')) {
            $query->where('shipment_id', $request->shipment_id);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhere('shipment_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('fname', 'like', "%{$search}%")
                         ->orWhere('lname', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'status'  => true,
            'message' => 'Invoices retrieved successfully',
            'data'    => $invoices
        ], 200);
    }

    /**
     * Get invoices belonging to currently authenticated customer.
     */
    public function myInvoices(Request $request)
    {
        $user = $request->user();

        $query = Invoice::with([
            'shipment.pickupCity',
            'shipment.dropoffCity',
            'scheduledTrip.route',
            'payments'
        ])->where('user_id', $user->id)->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'status'  => true,
            'message' => 'My invoices retrieved successfully',
            'data'    => $invoices
        ], 200);
    }

    /**
     * Get overview KPI statistics for invoices.
     */
    public function statistics(Request $request)
    {
        $user = $request->user();
        $query = Invoice::query();

        if ($user && in_array($user->role, ['individual_customer', 'company_customer'])) {
            $query->where('user_id', $user->id);
        }

        $stats = [
            'total'          => (clone $query)->count(),
            'unpaid'         => (clone $query)->where('status', 'unpaid')->count(),
            'partially_paid' => (clone $query)->where('status', 'partially_paid')->count(),
            'paid'           => (clone $query)->where('status', 'paid')->count(),
            'canceled'       => (clone $query)->where('status', 'canceled')->count(),
            'total_revenue'  => (clone $query)->where('status', 'paid')->sum('total_amount') ?? 0,
            'pending_amount' => (clone $query)->where('status', 'unpaid')->sum('total_amount') ?? 0,
        ];

        return response()->json([
            'status'  => true,
            'message' => 'Invoice statistics retrieved successfully',
            'data'    => $stats
        ], 200);
    }

    /**
     * Show single invoice details.
     */
    public function show($id)
    {
        $invoice = Invoice::with([
            'shipment.pickupCountry',
            'shipment.pickupCity',
            'shipment.dropoffCountry',
            'shipment.dropoffCity',
            'shipment.truckType',
            'scheduledTrip.route',
            'user:id,fname,lname,email,phone,client_code,photo',
            'payments' => function ($q) {
                $q->latest();
            }
        ])->find($id);

        if (!$invoice) {
            return response()->json([
                'status'  => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Invoice details retrieved successfully',
            'data'    => $invoice
        ], 200);
    }

    /**
     * Create a new invoice.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'           => 'required|exists:users,id',
            'shipment_id'       => 'nullable|exists:shipments,id',
            'scheduled_trip_id' => 'nullable|exists:scheduled_trips,id',
            'base_amount'       => 'required|numeric|min:0',
            'tax_amount'        => 'nullable|numeric|min:0',
            'discount'          => 'nullable|numeric|min:0',
            'due_date'          => 'nullable|date',
            'status'            => 'nullable|in:unpaid,partially_paid,paid,canceled',
        ], [
            'user_id.required'     => 'Customer is required.',
            'base_amount.required' => 'Base amount is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $baseAmount  = (float) $request->base_amount;
        $taxAmount   = (float) ($request->tax_amount ?? 0);
        $discount    = (float) ($request->discount ?? 0);
        $totalAmount = max(0, ($baseAmount + $taxAmount) - $discount);

        $invoice = Invoice::create([
            'user_id'           => $request->user_id,
            'shipment_id'       => $request->shipment_id ?: null,
            'scheduled_trip_id' => $request->scheduled_trip_id ?: null,
            'invoice_number'    => 'INV-' . date('Ymd') . '-' . sprintf('%04d', rand(1, 9999)),
            'base_amount'       => $baseAmount,
            'tax_amount'        => $taxAmount,
            'discount'          => $discount,
            'total_amount'      => $totalAmount,
            'status'            => $request->status ?? 'unpaid',
            'issued_at'         => now(),
            'due_date'          => $request->due_date ?: now()->addDays(14)->format('Y-m-d'),
        ]);

        $invoice->load(['shipment', 'scheduledTrip', 'user']);

        return response()->json([
            'status'  => true,
            'message' => 'Invoice created successfully',
            'data'    => $invoice
        ], 201);
    }

    /**
     * Update invoice amounts and due date.
     */
    public function update(Request $request, $id)
    {
        $invoice = Invoice::find($id);
        if (!$invoice) {
            return response()->json([
                'status'  => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'base_amount' => 'required|numeric|min:0',
            'tax_amount'  => 'nullable|numeric|min:0',
            'discount'    => 'nullable|numeric|min:0',
            'due_date'    => 'nullable|date',
            'status'      => 'nullable|in:unpaid,partially_paid,paid,canceled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $baseAmount  = (float) $request->base_amount;
        $taxAmount   = (float) ($request->tax_amount ?? 0);
        $discount    = (float) ($request->discount ?? 0);
        $totalAmount = max(0, ($baseAmount + $taxAmount) - $discount);

        $invoice->update([
            'base_amount'  => $baseAmount,
            'tax_amount'   => $taxAmount,
            'discount'     => $discount,
            'total_amount' => $totalAmount,
            'status'       => $request->status ?? $invoice->status,
            'due_date'     => $request->due_date ?: $invoice->due_date,
        ]);

        $invoice->load(['shipment', 'scheduledTrip', 'user', 'payments']);

        return response()->json([
            'status'  => true,
            'message' => 'Invoice updated successfully',
            'data'    => $invoice
        ], 200);
    }

    /**
     * Change invoice status.
     */
    public function changeStatus(Request $request, $id)
    {
        $invoice = Invoice::find($id);
        if (!$invoice) {
            return response()->json([
                'status'  => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:unpaid,partially_paid,paid,canceled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $invoice->status = $request->status;
        $invoice->save();

        return response()->json([
            'status'  => true,
            'message' => 'Invoice status updated to ' . ucfirst(str_replace('_', ' ', $request->status)) . ' successfully',
            'data'    => $invoice
        ], 200);
    }

    /**
     * Delete invoice.
     */
    public function destroy($id)
    {
        $invoice = Invoice::find($id);
        if (!$invoice) {
            return response()->json([
                'status'  => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        $invoice->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Invoice deleted successfully'
        ], 200);
    }
}
