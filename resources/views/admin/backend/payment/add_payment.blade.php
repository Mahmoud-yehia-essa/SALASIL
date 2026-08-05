@extends('admin.master_admin')
@section('admin')

{{-- ========================
     RECORD NEW PAYMENT
======================== --}}

<style>
:root {
    --pay-cyan:       #06B6D4;
    --pay-cyan-light: #38BDF8;
    --pay-navy:       #0F172A;
    --pay-border:     rgba(255,255,255,0.08);
}

.pay-card {
    background: linear-gradient(135deg, rgba(30,41,59,0.95) 0%, rgba(15,23,42,0.98) 100%);
    border: 1px solid var(--pay-border);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0,0,0,0.4);
}
.pay-card-header {
    background: linear-gradient(135deg, rgba(6,182,212,0.15) 0%, rgba(56,189,248,0.08) 100%);
    border-bottom: 1px solid rgba(6,182,212,0.2);
    padding: 24px 32px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.pay-card-header .icon-wrap {
    width: 52px; height: 52px;
    background: linear-gradient(135deg, var(--pay-cyan), var(--pay-cyan-light));
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: #fff;
    box-shadow: 0 8px 20px rgba(6,182,212,0.35);
    flex-shrink: 0;
}
.pay-card-body { padding: 32px; }

.pay-card .form-control,
.pay-card .form-select {
    background: rgba(255,255,255,0.05) !important;
    border: 1px solid rgba(255,255,255,0.12) !important;
    color: #F8FAFC !important;
    border-radius: 12px !important;
    padding: 12px 16px !important;
    transition: all 0.25s !important;
    font-size: 0.92rem !important;
}
.pay-card .form-control:focus,
.pay-card .form-select:focus {
    background: rgba(6,182,212,0.08) !important;
    border-color: var(--pay-cyan) !important;
    box-shadow: 0 0 0 3px rgba(6,182,212,0.15) !important;
    color: #F8FAFC !important;
}
.pay-card .form-select option { background: #1E293B; color: #F8FAFC; }
.pay-card .form-label { color: #CBD5E1 !important; font-weight: 700 !important; font-size: 0.88rem !important; margin-bottom: 8px; }

.pay-section-title {
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--pay-cyan-light);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.pay-section-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, rgba(6,182,212,0.4), transparent);
}

.btn-save-pay {
    background: linear-gradient(135deg, var(--pay-cyan), var(--pay-cyan-light));
    border: none;
    color: #0F172A;
    font-weight: 800;
    font-size: 1.05rem;
    padding: 14px 44px;
    border-radius: 14px;
    transition: all 0.25s;
    box-shadow: 0 6px 25px rgba(6,182,212,0.35);
}
.btn-save-pay:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(6,182,212,0.5);
    color: #0F172A;
}

/* ═════════════════════════════════════════════════════════════
   LIGHT MODE OVERRIDES FOR ADD PAYMENT PAGE
═════════════════════════════════════════════════════════════ */
html.light-theme .pay-card {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 15px 45px rgba(0,0,0,0.06) !important;
}
html.light-theme .pay-card-header {
    background: linear-gradient(135deg, rgba(2,132,199,0.08) 0%, rgba(3,105,161,0.04) 100%) !important;
    border-bottom-color: #E2E8F0 !important;
}
html.light-theme .pay-card-header h4 {
    color: #0F172A !important;
}
html.light-theme .pay-card-header small,
html.light-theme .pay-card-header p {
    color: #64748B !important;
}
html.light-theme .pay-card .form-control,
html.light-theme .pay-card .form-select {
    background: #FFFFFF !important;
    border-color: #CBD5E1 !important;
    color: #0F172A !important;
}
html.light-theme .pay-card .form-control:focus,
html.light-theme .pay-card .form-select:focus {
    background: #FFFFFF !important;
    border-color: #0284C7 !important;
    box-shadow: 0 0 0 3px rgba(2,132,199,0.15) !important;
    color: #0F172A !important;
}
html.light-theme .pay-card .form-select option {
    background: #FFFFFF !important;
    color: #0F172A !important;
}
html.light-theme .pay-card .form-label {
    color: #334155 !important;
}
html.light-theme .pay-section-title {
    color: #0284C7 !important;
}
html.light-theme .pay-section-title::after {
    background: linear-gradient(90deg, rgba(2,132,199,0.3), transparent) !important;
}
</style>

{{-- Page Header --}}
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold mb-1 header-title">
            <i class="bx bx-plus-circle text-info me-2"></i>Record Invoice Payment
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Register payment transaction for an existing invoice order.
        </p>
    </div>
    <div>
        <a href="{{ route('all.payments') }}" class="btn btn-outline-info rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-list-ul fs-5"></i>
            <span>All Payments</span>
        </a>
    </div>
</div>

{{-- Main Form Card --}}
<div class="pay-card">
    <div class="pay-card-header">
        <div class="icon-wrap"><i class="bx bx-credit-card"></i></div>
        <div>
            <h4 class="fw-bold text-white mb-0">New Payment Entry</h4>
            <small class="text-muted">Fill in invoice, payment method, amount and reference</small>
        </div>
    </div>
    <div class="pay-card-body">
        <form method="POST" action="{{ route('store.payment') }}" id="payForm" enctype="multipart/form-data" autocomplete="off">
            @csrf

            {{-- ════ 1. INVOICE SELECTION ════ --}}
            <div class="pay-section-title"><i class="bx bx-receipt me-1"></i>Select Invoice</div>
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-8">
                    <label class="form-label">Invoice <span class="text-danger">*</span></label>
                    <select name="invoice_id" id="invoice_id" class="form-select">
                        <option value="">-- Select Invoice --</option>
                        @foreach($invoices as $inv)
                            <option value="{{ $inv->id }}" {{ (old('invoice_id') == $inv->id || $selectedInvoiceId == $inv->id) ? 'selected' : '' }} data-amount="{{ $inv->total_amount }}">
                                Invoice #{{ $inv->invoice_number }} — Billed to: {{ $inv->user ? ($inv->user->fname . ' ' . $inv->user->lname) : 'Guest' }} [KWD {{ number_format($inv->total_amount, 2) }} | Status: {{ strtoupper($inv->status) }}]
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Payment Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select">
                        <option value="completed" {{ old('status', 'completed') === 'completed' ? 'selected' : '' }}>Completed (Paid)</option>
                        <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="failed" {{ old('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="refunded" {{ old('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>
            </div>

            <div class="my-4" style="height:1px;background:rgba(255,255,255,0.06);"></div>

            {{-- ════ 2. PAYMENT METHOD & AMOUNT ════ --}}
            <div class="pay-section-title"><i class="bx bx-dollar-circle me-1"></i>Amount & Gateway Specifications</div>
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-4">
                    <label class="form-label">Amount Paid (KWD) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="amount" id="amount" class="form-control" placeholder="0.00" value="{{ old('amount', $selectedInvoice ? $selectedInvoice->total_amount : '') }}" required>
                        <span class="input-group-text bg-dark border-secondary border-opacity-25 text-warning fw-bold">KWD</span>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                    <select name="payment_method" id="payment_method" class="form-select">
                        <option value="knet" {{ old('payment_method') === 'knet' ? 'selected' : '' }}>KNET</option>
                        <option value="credit_card" {{ old('payment_method') === 'credit_card' ? 'selected' : '' }}>Credit Card (Visa / Mastercard)</option>
                        <option value="apple_pay" {{ old('payment_method') === 'apple_pay' ? 'selected' : '' }}>Apple Pay</option>
                        <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Transaction Reference / ID</label>
                    <input type="text" name="transaction_id" class="form-control" placeholder="e.g. TXN-98402948" value="{{ old('transaction_id') }}">
                </div>
            </div>

            <div class="my-4" style="height:1px;background:rgba(255,255,255,0.06);"></div>

            {{-- ════ 3. RECEIPT & TIMESTAMP ════ --}}
            <div class="pay-section-title"><i class="bx bx-paperclip me-1"></i>Receipt Attachment & Date</div>
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6">
                    <label class="form-label">Upload Receipt (PDF / Image)</label>
                    <input type="file" name="receipt" class="form-control">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Paid Timestamp</label>
                    <input type="datetime-local" name="paid_at" class="form-control" value="{{ old('paid_at', date('Y-m-d\TH:i')) }}">
                </div>
            </div>

            {{-- Submit Action --}}
            <div class="text-end pt-3">
                <button type="submit" class="btn btn-save-pay d-inline-flex align-items-center gap-2" id="submitBtn">
                    <i class="bx bx-save fs-5"></i>
                    <span>Record Payment Transaction</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {

    // Auto populate amount when invoice changes if amount is empty
    $('#invoice_id').on('change', function() {
        var selectedOpt = $(this).find('option:selected');
        var amt = selectedOpt.data('amount');
        if (amt && ($('#amount').val() === '' || $('#amount').val() == 0)) {
            $('#amount').val(parseFloat(amt).toFixed(2));
        }
    });

    $('#payForm').on('submit', function() {
        $('#submitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Recording Payment...');
    });

});
</script>

@endsection
