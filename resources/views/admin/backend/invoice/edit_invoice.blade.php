@extends('admin.master_admin')
@section('admin')

{{-- ========================
     EDIT INVOICE
======================== --}}

<style>
:root {
    --inv-cyan:       #06B6D4;
    --inv-cyan-light: #38BDF8;
    --inv-navy:       #0F172A;
    --inv-border:     rgba(255,255,255,0.08);
}

.inv-card {
    background: linear-gradient(135deg, rgba(30,41,59,0.95) 0%, rgba(15,23,42,0.98) 100%);
    border: 1px solid var(--inv-border);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0,0,0,0.4);
}
.inv-card-header {
    background: linear-gradient(135deg, rgba(6,182,212,0.15) 0%, rgba(56,189,248,0.08) 100%);
    border-bottom: 1px solid rgba(6,182,212,0.2);
    padding: 24px 32px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.inv-card-header .icon-wrap {
    width: 52px; height: 52px;
    background: linear-gradient(135deg, var(--inv-cyan), var(--inv-cyan-light));
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: #fff;
    box-shadow: 0 8px 20px rgba(6,182,212,0.35);
    flex-shrink: 0;
}
.inv-card-body { padding: 32px; }

.inv-card .form-control,
.inv-card .form-select {
    background: rgba(255,255,255,0.05) !important;
    border: 1px solid rgba(255,255,255,0.12) !important;
    color: #F8FAFC !important;
    border-radius: 12px !important;
    padding: 12px 16px !important;
    transition: all 0.25s !important;
    font-size: 0.92rem !important;
}
.inv-card .form-control:focus,
.inv-card .form-select:focus {
    background: rgba(6,182,212,0.08) !important;
    border-color: var(--inv-cyan) !important;
    box-shadow: 0 0 0 3px rgba(6,182,212,0.15) !important;
    color: #F8FAFC !important;
}
.inv-card .form-select option { background: #1E293B; color: #F8FAFC; }
.inv-card .form-label { color: #CBD5E1 !important; font-weight: 700 !important; font-size: 0.88rem !important; margin-bottom: 8px; }

.inv-section-title {
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--inv-cyan-light);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.inv-section-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, rgba(6,182,212,0.4), transparent);
}

.btn-save-inv {
    background: linear-gradient(135deg, var(--inv-cyan), var(--inv-cyan-light));
    border: none;
    color: #0F172A;
    font-weight: 800;
    font-size: 1.05rem;
    padding: 14px 44px;
    border-radius: 14px;
    transition: all 0.25s;
    box-shadow: 0 6px 25px rgba(6,182,212,0.35);
}
.btn-save-inv:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(6,182,212,0.5);
    color: #0F172A;
}

/* ═════════════════════════════════════════════════════════════
   LIGHT MODE OVERRIDES FOR EDIT INVOICE PAGE
═════════════════════════════════════════════════════════════ */
html.light-theme .inv-card {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 15px 45px rgba(0,0,0,0.06) !important;
}
html.light-theme .inv-card-header {
    background: linear-gradient(135deg, rgba(2,132,199,0.08) 0%, rgba(3,105,161,0.04) 100%) !important;
    border-bottom-color: #E2E8F0 !important;
}
html.light-theme .inv-card-header h4 {
    color: #0F172A !important;
}
html.light-theme .inv-card-header small,
html.light-theme .inv-card-header p {
    color: #64748B !important;
}
html.light-theme .inv-card .form-control,
html.light-theme .inv-card .form-select {
    background: #FFFFFF !important;
    border-color: #CBD5E1 !important;
    color: #0F172A !important;
}
html.light-theme .inv-card .form-control:disabled,
html.light-theme .inv-card .form-control[readonly] {
    background: #F1F5F9 !important;
    border-color: #CBD5E1 !important;
    color: #475569 !important;
}
html.light-theme .inv-card .form-control:focus,
html.light-theme .inv-card .form-select:focus {
    background: #FFFFFF !important;
    border-color: #0284C7 !important;
    box-shadow: 0 0 0 3px rgba(2,132,199,0.15) !important;
    color: #0F172A !important;
}
html.light-theme .inv-card .form-select option {
    background: #FFFFFF !important;
    color: #0F172A !important;
}
html.light-theme .inv-card .form-label {
    color: #334155 !important;
}
html.light-theme .inv-section-title {
    color: #0284C7 !important;
}
html.light-theme .inv-section-title::after {
    background: linear-gradient(90deg, rgba(2,132,199,0.3), transparent) !important;
}
</style>

{{-- Page Header --}}
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold mb-1 header-title">
            <i class="bx bx-edit text-info me-2"></i>Edit Invoice #{{ $invoice->invoice_number }}
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Update breakdown amounts, tax, discount, total, due date or payment status.
        </p>
    </div>
    <div>
        <a href="{{ route('all.invoices') }}" class="btn btn-outline-info rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-left-arrow-alt fs-5"></i>
            <span>Back to All Invoices</span>
        </a>
    </div>
</div>

{{-- Main Form Card --}}
<div class="inv-card">
    <div class="inv-card-header">
        <div class="icon-wrap"><i class="bx bx-receipt"></i></div>
        <div>
            <h4 class="fw-bold text-white mb-0">Invoice Breakdown & Financial Details</h4>
            <small class="text-muted">Modify amount values and payment state</small>
        </div>
    </div>
    <div class="inv-card-body">
        <form method="POST" action="{{ route('update.invoice') }}" id="invForm" autocomplete="off">
            @csrf
            <input type="hidden" name="id" value="{{ $invoice->id }}">

            {{-- ════ 1. LINKED REFERENCE INFO ════ --}}
            <div class="inv-section-title"><i class="bx bx-link me-1"></i>Reference Information</div>
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-4">
                    <label class="form-label">Invoice Number</label>
                    <input type="text" class="form-control" value="{{ $invoice->invoice_number }}" disabled readonly>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Customer Billed</label>
                    <input type="text" class="form-control" value="{{ $invoice->user ? ($invoice->user->fname . ' ' . $invoice->user->lname . ' (' . $invoice->user->email . ')') : 'Guest' }}" disabled readonly>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Linked Resource</label>
                    <input type="text" class="form-control" value="{{ $invoice->shipment_id ? 'Shipment Order #' . $invoice->shipment_id : ($invoice->scheduled_trip_id ? 'Scheduled Trip #' . $invoice->scheduled_trip_id : 'Direct Invoice') }}" disabled readonly>
                </div>
            </div>

            <div class="my-4" style="height:1px;background:rgba(255,255,255,0.06);"></div>

            {{-- ════ 2. FINANCIAL BREAKDOWN ════ --}}
            <div class="inv-section-title"><i class="bx bx-dollar-circle me-1"></i>Financial Amounts (KWD)</div>
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-3">
                    <label class="form-label">Base Amount (KWD) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="base_amount" id="base_amount" class="form-control" placeholder="0.00" value="{{ old('base_amount', $invoice->base_amount) }}">
                        <span class="input-group-text bg-dark border-secondary border-opacity-25 text-info fw-bold">KWD</span>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Tax Amount (KWD)</label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="tax_amount" id="tax_amount" class="form-control" placeholder="0.00" value="{{ old('tax_amount', $invoice->tax_amount) }}">
                        <span class="input-group-text bg-dark border-secondary border-opacity-25 text-info fw-bold">KWD</span>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Discount Amount (KWD)</label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="discount" id="discount" class="form-control" placeholder="0.00" value="{{ old('discount', $invoice->discount) }}">
                        <span class="input-group-text bg-dark border-secondary border-opacity-25 text-info fw-bold">KWD</span>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Calculated Total Due (KWD)</label>
                    <div class="input-group">
                        <input type="number" step="0.01" id="total_amount_preview" class="form-control fw-bold text-warning" placeholder="0.00" value="{{ number_format($invoice->total_amount, 2, '.', '') }}" readonly disabled>
                        <span class="input-group-text bg-dark border-secondary border-opacity-25 text-warning fw-bold">KWD</span>
                    </div>
                </div>
            </div>

            <div class="my-4" style="height:1px;background:rgba(255,255,255,0.06);"></div>

            {{-- ════ 3. STATUS & DUE DATE ════ --}}
            <div class="inv-section-title"><i class="bx bx-calendar-event me-1"></i>Status & Due Date</div>
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6">
                    <label class="form-label">Payment Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select">
                        <option value="unpaid" {{ $invoice->status === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="partially_paid" {{ $invoice->status === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="paid" {{ $invoice->status === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="canceled" {{ $invoice->status === 'canceled' ? 'selected' : '' }}>Canceled</option>
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $invoice->due_date ? date('Y-m-d', strtotime($invoice->due_date)) : '') }}">
                </div>
            </div>

            {{-- Submit Action --}}
            <div class="text-end pt-3">
                <button type="submit" class="btn btn-save-inv d-inline-flex align-items-center gap-2" id="submitBtn">
                    <i class="bx bx-save fs-5"></i>
                    <span>Update Invoice</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {

    function calcTotal() {
        var base = parseFloat($('#base_amount').val()) || 0;
        var tax  = parseFloat($('#tax_amount').val()) || 0;
        var disc = parseFloat($('#discount').val()) || 0;

        var total = Math.max(0, (base + tax) - disc);
        $('#total_amount_preview').val(total.toFixed(2));
    }

    $('#base_amount, #tax_amount, #discount').on('keyup change input', function() {
        calcTotal();
    });

    $('#invForm').on('submit', function() {
        $('#submitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Updating Invoice...');
    });

});
</script>

@endsection
