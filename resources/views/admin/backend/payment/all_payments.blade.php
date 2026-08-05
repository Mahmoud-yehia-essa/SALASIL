@extends('admin.master_admin')
@section('admin')

{{-- ========================
     FINANCIAL & BILLING — ALL PAYMENTS
======================== --}}

<style>
:root {
    --pay-cyan:       #06B6D4;
    --pay-cyan-light: #38BDF8;
    --pay-navy:       #0F172A;
    --pay-border:     rgba(255,255,255,0.08);
    --pay-success:    #10B981;
    --pay-warning:    #F59E0B;
    --pay-danger:     #F43F5E;
    --pay-purple:     #8B5CF6;
}

/* ─── KPI Cards ─── */
.kpi-card {
    background: linear-gradient(135deg, rgba(30,41,59,0.9) 0%, rgba(15,23,42,0.95) 100%);
    border: 1px solid var(--pay-border);
    border-radius: 16px;
    padding: 20px 24px;
    display: flex;
    align-items: center;
    gap: 18px;
    transition: all 0.3s cubic-bezier(0.34,1.56,0.64,1);
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}
.kpi-card:hover {
    transform: translateY(-4px);
    border-color: rgba(6,182,212,0.3);
    box-shadow: 0 15px 35px rgba(6,182,212,0.15);
}
.kpi-icon-wrap {
    width: 52px; height: 52px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: #fff;
    flex-shrink: 0;
}
.kpi-icon-wrap.total     { background: linear-gradient(135deg, #06B6D4, #38BDF8); box-shadow: 0 8px 20px rgba(6,182,212,0.3); }
.kpi-icon-wrap.completed { background: linear-gradient(135deg, #10B981, #34D399); box-shadow: 0 8px 20px rgba(16,185,129,0.3); }
.kpi-icon-wrap.pending   { background: linear-gradient(135deg, #F59E0B, #FCD34D); box-shadow: 0 8px 20px rgba(245,158,11,0.3); color:#0F172A; }
.kpi-icon-wrap.failed    { background: linear-gradient(135deg, #F43F5E, #FB7185); box-shadow: 0 8px 20px rgba(244,63,94,0.3); }
.kpi-icon-wrap.revenue   { background: linear-gradient(135deg, #8B5CF6, #A78BFA); box-shadow: 0 8px 20px rgba(139,92,246,0.3); }

.kpi-label { font-size: 0.78rem; font-weight: 700; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.5px; }
.kpi-value { font-size: 1.5rem; font-weight: 800; color: #F8FAFC; margin-top: 2px; }

/* ─── Filter Bar ─── */
.filter-card {
    background: rgba(30,41,59,0.7);
    border: 1px solid var(--pay-border);
    border-radius: 16px;
    padding: 20px 24px;
    margin-bottom: 24px;
    backdrop-filter: blur(10px);
}
.filter-card .form-control,
.filter-card .form-select {
    background: rgba(255,255,255,0.05) !important;
    border: 1px solid rgba(255,255,255,0.12) !important;
    color: #F8FAFC !important;
    border-radius: 10px !important;
    padding: 9px 14px !important;
    font-size: 0.88rem !important;
}
.filter-card .form-control:focus,
.filter-card .form-select:focus {
    border-color: var(--pay-cyan) !important;
    box-shadow: 0 0 0 3px rgba(6,182,212,0.15) !important;
}
.filter-card .form-select option { background: #1E293B; color: #F8FAFC; }

/* ─── Data Table ─── */
.pay-table-card {
    background: linear-gradient(135deg, rgba(30,41,59,0.95) 0%, rgba(15,23,42,0.98) 100%);
    border: 1px solid var(--pay-border);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0,0,0,0.35);
}
.pay-table {
    width: 100%;
    margin-bottom: 0;
    color: #CBD5E1;
    border-collapse: separate;
    border-spacing: 0;
}
.pay-table th {
    background: rgba(15,23,42,0.9);
    color: #94A3B8;
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    padding: 16px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    white-space: nowrap;
}
.pay-table td {
    padding: 16px 20px;
    vertical-align: middle;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    font-size: 0.88rem;
}
.pay-table tbody tr { transition: background 0.2s; }
.pay-table tbody tr:hover { background: rgba(6,182,212,0.05); }

/* Status Badges & Select */
.status-select-sm {
    background: rgba(255,255,255,0.06) !important;
    border: 1px solid rgba(255,255,255,0.15) !important;
    color: #F8FAFC !important;
    border-radius: 50px !important;
    padding: 4px 28px 4px 12px !important;
    font-size: 0.78rem !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    width: auto !important;
    display: inline-block !important;
}
.status-select-sm.status-completed{ border-color: rgba(16,185,129,0.5) !important; color: #34D399 !important; }
.status-select-sm.status-pending  { border-color: rgba(245,158,11,0.5) !important; color: #F59E0B !important; }
.status-select-sm.status-failed   { border-color: rgba(244,63,94,0.5) !important;  color: #F43F5E !important; }
.status-select-sm.status-refunded { border-color: rgba(139,92,246,0.5) !important; color: #A78BFA !important; }

/* Method Badges */
.method-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 12px; border-radius: 50px;
    font-size: 0.78rem; font-weight: 700;
}
.method-badge.knet       { background: rgba(6,182,212,0.15); color: #38BDF8; border: 1px solid rgba(6,182,212,0.3); }
.method-badge.credit_card{ background: rgba(59,130,246,0.15); color: #60A5FA; border: 1px solid rgba(59,130,246,0.3); }
.method-badge.apple_pay  { background: rgba(255,255,255,0.15); color: #F8FAFC; border: 1px solid rgba(255,255,255,0.3); }
.method-badge.bank_transfer{ background: rgba(139,92,246,0.15); color: #A78BFA; border: 1px solid rgba(139,92,246,0.3); }
.method-badge.cash       { background: rgba(16,185,129,0.15); color: #34D399; border: 1px solid rgba(16,185,129,0.3); }

.btn-action {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: inline-flex; align-items: center; justify-content: center;
    border: 1px solid var(--pay-border);
    background: rgba(255,255,255,0.04);
    color: #CBD5E1;
    transition: all 0.2s;
}
.btn-action:hover {
    background: rgba(6,182,212,0.15);
    border-color: var(--pay-cyan);
    color: #38BDF8;
    transform: translateY(-2px);
}
.btn-action.btn-del:hover {
    background: rgba(244,63,94,0.15);
    border-color: var(--pay-danger);
    color: #F43F5E;
}

/* Modal Drawer */
.modal-pay-detail .modal-dialog { max-width: 750px; }
.modal-pay-detail .modal-content {
    background: #0F172A;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0,0,0,0.6);
    color: #F8FAFC;
}
.modal-pay-detail .modal-header {
    background: linear-gradient(135deg, rgba(6,182,212,0.15) 0%, rgba(56,189,248,0.08) 100%);
    border-bottom: 1px solid rgba(6,182,212,0.2);
    padding: 24px 32px;
}
.modal-pay-detail .modal-body { padding: 32px; max-height: 80vh; overflow-y: auto; }
.modal-pay-detail .modal-footer { border-top: 1px solid rgba(255,255,255,0.08); padding: 20px 32px; background: rgba(0,0,0,0.15); }

.detail-sec-title {
    font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px;
    color: var(--pay-cyan-light); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;
}
.detail-sec-title::after { content: ''; flex: 1; height: 1px; background: linear-gradient(90deg, rgba(6,182,212,0.4), transparent); }
.detail-card-grid { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 14px; padding: 18px 20px; margin-bottom: 20px; }
.detail-row { display: flex; gap: 12px; margin-bottom: 10px; align-items: flex-start; }
.detail-row:last-child { margin-bottom: 0; }
.detail-row .d-label { font-size: 0.78rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px; min-width: 140px; flex-shrink: 0; }
.detail-row .d-value { font-size: 0.88rem; color: #E2E8F0; font-weight: 500; }

/* ═════════════════════════════════════════════════════════════
   LIGHT MODE OVERRIDES FOR ALL PAYMENTS PAGE
═════════════════════════════════════════════════════════════ */
html.light-theme .kpi-card {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.04) !important;
}
html.light-theme .kpi-label {
    color: #64748B !important;
}
html.light-theme .kpi-value {
    color: #0F172A !important;
}
html.light-theme .filter-card {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.04) !important;
}
html.light-theme .filter-card .form-control,
html.light-theme .filter-card .form-select {
    background: #F8FAFC !important;
    border-color: #CBD5E1 !important;
    color: #0F172A !important;
}
html.light-theme .filter-card .form-select option {
    background: #FFFFFF !important;
    color: #0F172A !important;
}
html.light-theme .pay-table-card {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 15px 45px rgba(0,0,0,0.05) !important;
}
html.light-theme .pay-table {
    color: #0F172A !important;
}
html.light-theme .pay-table th {
    background: #F1F5F9 !important;
    color: #334155 !important;
    border-bottom-color: #E2E8F0 !important;
}
html.light-theme .pay-table td {
    border-bottom-color: #F1F5F9 !important;
    color: #0F172A !important;
}
html.light-theme .pay-table tbody tr:hover {
    background: #F8FAFC !important;
}
html.light-theme .status-select-sm {
    background: #F8FAFC !important;
    color: #0F172A !important;
}
html.light-theme .status-select-sm option {
    background: #FFFFFF !important;
    color: #0F172A !important;
}
html.light-theme .status-select-sm.status-completed {
    background: #D1FAE5 !important;
    color: #059669 !important;
    border-color: #10B981 !important;
}
html.light-theme .status-select-sm.status-pending {
    background: #FEF3C7 !important;
    color: #D97706 !important;
    border-color: #F59E0B !important;
}
html.light-theme .status-select-sm.status-failed {
    background: #FFE4E6 !important;
    color: #E11D48 !important;
    border-color: #F43F5E !important;
}
html.light-theme .status-select-sm.status-refunded {
    background: #EDE9FE !important;
    color: #6D28D9 !important;
    border-color: #8B5CF6 !important;
}
html.light-theme .method-badge.apple_pay {
    background: #F1F5F9 !important;
    color: #0F172A !important;
    border-color: #CBD5E1 !important;
}
html.light-theme .btn-action {
    background: #F1F5F9 !important;
    border-color: #CBD5E1 !important;
    color: #475569 !important;
}
html.light-theme .btn-action:hover {
    background: #E0F2FE !important;
    border-color: #0284C7 !important;
    color: #0284C7 !important;
}
html.light-theme .btn-action.btn-del:hover {
    background: #FFE4E6 !important;
    border-color: #F43F5E !important;
    color: #E11D48 !important;
}
html.light-theme .modal-pay-detail .modal-content {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    color: #0F172A !important;
    box-shadow: 0 25px 60px rgba(0,0,0,0.15) !important;
}
html.light-theme .modal-pay-detail .modal-header {
    background: linear-gradient(135deg, rgba(2,132,199,0.08) 0%, rgba(3,105,161,0.04) 100%) !important;
    border-bottom-color: #E2E8F0 !important;
}
html.light-theme .modal-pay-detail .modal-header h5,
html.light-theme .modal-pay-detail .modal-header .modal-title {
    color: #0F172A !important;
}
html.light-theme .modal-pay-detail .modal-header .text-muted {
    color: #64748B !important;
}
html.light-theme .modal-pay-detail .btn-close-white {
    filter: invert(1) grayscale(100%) brightness(200%);
}
html.light-theme .modal-pay-detail .modal-footer {
    background: #F8FAFC !important;
    border-top-color: #E2E8F0 !important;
}
html.light-theme .detail-sec-title {
    color: #0284C7 !important;
}
html.light-theme .detail-sec-title::after {
    background: linear-gradient(90deg, rgba(2,132,199,0.3), transparent) !important;
}
html.light-theme .detail-card-grid {
    background: #F8FAFC !important;
    border-color: #E2E8F0 !important;
}
html.light-theme .detail-row .d-label {
    color: #475569 !important;
    font-weight: 800 !important;
}
html.light-theme .detail-row .d-value {
    color: #0F172A !important;
    font-weight: 700 !important;
}
</style>

{{-- Page Header --}}
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold mb-1 header-title">
            <i class="bx bx-credit-card text-info me-2"></i>Financial & Billing System — Payments
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Monitor, control, record, and manage invoice payment transactions.
        </p>
    </div>
    <div>
        <a href="{{ route('add.payment') }}" class="btn btn-info rounded-3 px-3 py-2 fw-bold d-inline-flex align-items-center gap-2" style="background:linear-gradient(135deg,#06B6D4,#38BDF8);border:none;color:#0F172A;">
            <i class="bx bx-plus-circle fs-5"></i>
            <span>Record Payment</span>
        </a>
    </div>
</div>

{{-- ─── KPI Stats Bar ─── --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap total"><i class="bx bx-credit-card"></i></div>
            <div>
                <div class="kpi-label">Total Transactions</div>
                <div class="kpi-value">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap completed"><i class="bx bx-check-circle"></i></div>
            <div>
                <div class="kpi-label">Completed</div>
                <div class="kpi-value">{{ number_format($stats['completed']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap pending"><i class="bx bx-time-five"></i></div>
            <div>
                <div class="kpi-label">Pending / Failed</div>
                <div class="kpi-value">{{ number_format($stats['pending'] + $stats['failed']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap revenue"><i class="bx bx-dollar-circle"></i></div>
            <div>
                <div class="kpi-label">Total Collected</div>
                <div class="kpi-value" style="font-size:1.3rem;">KWD {{ number_format($stats['total_collected'], 2) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ─── Filter & Search Bar ─── --}}
<div class="filter-card">
    <form method="GET" action="{{ route('all.payments') }}" id="filterForm">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-md-4">
                <div class="position-relative">
                    <input type="text" name="search" class="form-control ps-5" placeholder="Search txn #, invoice #, customer..." value="{{ request('search') }}">
                    <span class="position-absolute text-muted" style="left:14px;top:50%;transform:translateY(-50%);"><i class="bx bx-search fs-5"></i></span>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <select name="status" class="form-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Statuses</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="payment_method" class="form-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Methods</option>
                    <option value="knet" {{ request('payment_method') === 'knet' ? 'selected' : '' }}>KNET</option>
                    <option value="credit_card" {{ request('payment_method') === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                    <option value="apple_pay" {{ request('payment_method') === 'apple_pay' ? 'selected' : '' }}>Apple Pay</option>
                    <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <select name="user_id" class="form-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Customers</option>
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ request('user_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->fname }} {{ $c->lname }} ({{ $c->email }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-1 text-end">
                <a href="{{ route('all.payments') }}" class="btn btn-outline-secondary w-100 rounded-3" title="Reset Filters"><i class="bx bx-refresh fs-5"></i></a>
            </div>
        </div>
    </form>
</div>

{{-- ─── Data Table ─── --}}
<div class="pay-table-card">
    <div class="table-responsive">
        <table class="table pay-table">
            <thead>
                <tr>
                    <th>Txn Reference / ID</th>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Paid At</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                <tr>
                    {{-- Txn Reference / ID --}}
                    <td>
                        <div class="fw-bold text-info">{{ $p->transaction_id ?: ('TXN-#' . $p->id) }}</div>
                        <div class="text-muted" style="font-size:0.75rem;">Payment ID: #{{ $p->id }}</div>
                    </td>

                    {{-- Invoice --}}
                    <td>
                        @if($p->invoice)
                            <div class="fw-bold text-white">#{{ $p->invoice->invoice_number }}</div>
                            <div class="text-muted" style="font-size:0.75rem;">
                                {{ $p->invoice->shipment_id ? 'Shipment #' . $p->invoice->shipment_id : ($p->invoice->scheduled_trip_id ? 'Trip #' . $p->invoice->scheduled_trip_id : 'Invoice') }}
                            </div>
                        @else
                            <span class="text-muted">Invoice Removed</span>
                        @endif
                    </td>

                    {{-- Customer --}}
                    <td>
                        <div class="fw-bold text-white">
                            {{ $p->user ? ($p->user->fname . ' ' . $p->user->lname) : 'Guest' }}
                        </div>
                        <div class="text-muted" style="font-size:0.78rem;">
                            {{ $p->user ? $p->user->email : '—' }}
                        </div>
                    </td>

                    {{-- Amount --}}
                    <td>
                        <strong class="text-warning" style="font-size:0.95rem;">KWD {{ number_format($p->amount, 2) }}</strong>
                    </td>

                    {{-- Payment Method --}}
                    <td>
                        <span class="method-badge {{ $p->payment_method }}">
                            @if($p->payment_method==='knet') <i class="bx bx-credit-card"></i> KNET
                            @elseif($p->payment_method==='credit_card') <i class="bx bx-credit-card-front"></i> Credit Card
                            @elseif($p->payment_method==='apple_pay') <i class="bx bxl-apple"></i> Apple Pay
                            @elseif($p->payment_method==='bank_transfer') <i class="bx bx-buildings"></i> Bank Transfer
                            @else <i class="bx bx-money"></i> Cash
                            @endif
                        </span>
                    </td>

                    {{-- Status Dropdown --}}
                    <td>
                        <select class="form-select status-select-sm status-{{ $p->status }}" onchange="updatePaymentStatus({{ $p->id }}, this.value)">
                            <option value="pending" {{ $p->status==='pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ $p->status==='completed' ? 'selected' : '' }}>Completed</option>
                            <option value="failed" {{ $p->status==='failed' ? 'selected' : '' }}>Failed</option>
                            <option value="refunded" {{ $p->status==='refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </td>

                    {{-- Paid At --}}
                    <td class="text-muted" style="font-size:0.82rem;">
                        {{ $p->paid_at ? date('Y-m-d H:i', strtotime($p->paid_at)) : '—' }}
                    </td>

                    {{-- Actions --}}
                    <td class="text-end">
                        <div class="d-inline-flex gap-2">
                            <button type="button" class="btn-action" onclick="openPaymentModal({{ $p->id }})" title="View Details">
                                <i class="bx bx-show fs-5"></i>
                            </button>
                            <a href="{{ route('edit.payment', $p->id) }}" class="btn-action" title="Edit Payment">
                                <i class="bx bx-edit fs-5"></i>
                            </a>
                            <a href="{{ route('delete.payment', $p->id) }}" class="btn-action btn-del confirm-delete" title="Delete Payment">
                                <i class="bx bx-trash fs-5"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bx bx-credit-card fs-1 text-slate-500 mb-2 d-block"></i>
                        No payment transactions found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    // ── Update Payment Status via AJAX ──
    function updatePaymentStatus(id, newStatus) {
        $.ajax({
            url: '{{ route("payment.status.ajax") }}',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                payment_id: id,
                status: newStatus
            },
            success: function(res) {
                if (res.status === 'success') {
                    if (typeof toastr !== 'undefined') toastr.success(res.message);
                }
            },
            error: function() {
                if (typeof toastr !== 'undefined') toastr.error('Failed to update payment status.');
            }
        });
    }

    // ── Open Payment Details Modal via AJAX ──
    function openPaymentModal(id) {
        var modal = new bootstrap.Modal(document.getElementById('paymentDetailsModal'));
        $('#modalLoader').show();
        $('#modalDataWrap').hide().empty();
        modal.show();

        $.ajax({
            url: '{{ route("get.payment.details.ajax", ":id") }}'.replace(':id', id),
            type: 'GET',
            success: function(res) {
                $('#modalLoader').hide();
                if (res.status === 'success') {
                    renderPaymentModal(res.payment);
                    $('#modalDataWrap').show();
                } else {
                    $('#modalDataWrap').html('<div class="alert alert-danger">Failed to load details.</div>').show();
                }
            },
            error: function() {
                $('#modalLoader').hide();
                $('#modalDataWrap').html('<div class="alert alert-danger">Error fetching payment data.</div>').show();
            }
        });
    }

    function renderPaymentModal(p) {
        $('#modalTitle').text('Payment Txn ' + (p.transaction_id || ('#' + p.id)));

        var html = '';

        // Section 1: Transaction Info
        html += '<div class="detail-sec-title"><i class="bx bx-receipt me-1"></i>Transaction Overview</div>';
        html += '<div class="detail-card-grid"><div class="row g-2">';
        html += dRow('Payment ID', '#' + p.id);
        html += dRow('Transaction ID / Ref', p.transaction_id);
        html += dRow('Payment Method', p.payment_method);
        html += dRow('Payment Status', '<span class="badge bg-info text-dark fw-bold">' + p.status.toUpperCase() + '</span>');
        html += dRow('Amount Paid', '<strong class="text-warning">KWD ' + p.amount + '</strong>');
        html += dRow('Paid Timestamp', p.paid_at);
        html += '</div></div>';

        // Section 2: Customer & Invoice Link
        html += '<div class="detail-sec-title"><i class="bx bx-user me-1"></i>Customer & Invoice Reference</div>';
        html += '<div class="detail-card-grid"><div class="row g-2">';
        html += dRow('Customer Name', p.customer_name);
        html += dRow('Customer Email', p.customer_email);
        html += dRow('Invoice Reference', 'Invoice #' + p.invoice_number);
        if (p.receipt_url) {
            html += dRow('Receipt Attachment', '<a href="' + p.receipt_url + '" target="_blank" class="btn btn-sm btn-outline-info rounded-3 py-1 px-3"><i class="bx bx-file me-1"></i>View Receipt Attachment</a>');
        } else {
            html += dRow('Receipt Attachment', 'No receipt attached');
        }
        html += '</div></div>';

        $('#modalDataWrap').html(html);
    }

    function dRow(label, value) {
        return '<div class="col-12 col-md-6"><div class="detail-row"><div class="d-label">' + label + '</div><div class="d-value">' + (value || '—') + '</div></div></div>';
    }

    // ── SweetAlert Delete Confirmation ──
    $(document).on('click', '.confirm-delete', function(e) {
        e.preventDefault();
        var link = $(this).attr('href');
        Swal.fire({
            title: 'Delete Payment Transaction?',
            text: "Invoice payment status will be updated accordingly!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#F43F5E',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Yes, Delete Payment!',
            background: '#1E293B',
            color: '#F8FAFC'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = link;
            }
        });
    });
</script>

{{-- PAYMENT DETAILS MODAL DRAWER --}}
<div class="modal fade modal-pay-detail" id="paymentDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-info bg-opacity-10 p-2 border border-info border-opacity-25 text-info">
                        <i class="bx bx-credit-card fs-3"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="modalTitle">Payment Details</h5>
                        <small class="text-muted">Transaction audit and receipt specifications</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-5" id="modalLoader">
                    <div class="spinner-border text-info" role="status"></div>
                    <div class="text-info mt-2">Loading payment details...</div>
                </div>
                <div id="modalDataWrap" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-3 px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection
