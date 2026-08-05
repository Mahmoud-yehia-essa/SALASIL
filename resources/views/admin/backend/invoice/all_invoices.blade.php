@extends('admin.master_admin')
@section('admin')

{{-- ========================
     FINANCIAL & BILLING — ALL INVOICES
======================== --}}

<style>
:root {
    --inv-cyan:       #06B6D4;
    --inv-cyan-light: #38BDF8;
    --inv-navy:       #0F172A;
    --inv-border:     rgba(255,255,255,0.08);
    --inv-success:    #10B981;
    --inv-warning:    #F59E0B;
    --inv-danger:     #F43F5E;
    --inv-purple:     #8B5CF6;
}

/* ─── KPI Cards ─── */
.kpi-card {
    background: linear-gradient(135deg, rgba(30,41,59,0.9) 0%, rgba(15,23,42,0.95) 100%);
    border: 1px solid var(--inv-border);
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
.kpi-icon-wrap.total    { background: linear-gradient(135deg, #06B6D4, #38BDF8); box-shadow: 0 8px 20px rgba(6,182,212,0.3); }
.kpi-icon-wrap.unpaid   { background: linear-gradient(135deg, #F43F5E, #FB7185); box-shadow: 0 8px 20px rgba(244,63,94,0.3); }
.kpi-icon-wrap.partial  { background: linear-gradient(135deg, #F59E0B, #FCD34D); box-shadow: 0 8px 20px rgba(245,158,11,0.3); color:#0F172A; }
.kpi-icon-wrap.paid     { background: linear-gradient(135deg, #10B981, #34D399); box-shadow: 0 8px 20px rgba(16,185,129,0.3); }
.kpi-icon-wrap.revenue  { background: linear-gradient(135deg, #8B5CF6, #A78BFA); box-shadow: 0 8px 20px rgba(139,92,246,0.3); }

.kpi-label { font-size: 0.78rem; font-weight: 700; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.5px; }
.kpi-value { font-size: 1.5rem; font-weight: 800; color: #F8FAFC; margin-top: 2px; }

/* ─── Filter Bar ─── */
.filter-card {
    background: rgba(30,41,59,0.7);
    border: 1px solid var(--inv-border);
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
    border-color: var(--inv-cyan) !important;
    box-shadow: 0 0 0 3px rgba(6,182,212,0.15) !important;
}
.filter-card .form-select option { background: #1E293B; color: #F8FAFC; }

/* ─── Data Table ─── */
.inv-table-card {
    background: linear-gradient(135deg, rgba(30,41,59,0.95) 0%, rgba(15,23,42,0.98) 100%);
    border: 1px solid var(--inv-border);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0,0,0,0.35);
}
.inv-table {
    width: 100%;
    margin-bottom: 0;
    color: #CBD5E1;
    border-collapse: separate;
    border-spacing: 0;
}
.inv-table th {
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
.inv-table td {
    padding: 16px 20px;
    vertical-align: middle;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    font-size: 0.88rem;
}
.inv-table tbody tr { transition: background 0.2s; }
.inv-table tbody tr:hover { background: rgba(6,182,212,0.05); }

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
.status-select-sm.status-unpaid       { border-color: rgba(244,63,94,0.5) !important;  color: #F43F5E !important; }
.status-select-sm.status-partially_paid{ border-color: rgba(245,158,11,0.5) !important; color: #F59E0B !important; }
.status-select-sm.status-paid         { border-color: rgba(16,185,129,0.5) !important; color: #34D399 !important; }
.status-select-sm.status-canceled     { border-color: rgba(148,163,184,0.5) !important;color: #94A3B8 !important; }

.btn-action {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: inline-flex; align-items: center; justify-content: center;
    border: 1px solid var(--inv-border);
    background: rgba(255,255,255,0.04);
    color: #CBD5E1;
    transition: all 0.2s;
}
.btn-action:hover {
    background: rgba(6,182,212,0.15);
    border-color: var(--inv-cyan);
    color: #38BDF8;
    transform: translateY(-2px);
}
.btn-action.btn-del:hover {
    background: rgba(244,63,94,0.15);
    border-color: var(--inv-danger);
    color: #F43F5E;
}

/* ─── Modal Drawer Styling ─── */
.modal-inv-detail .modal-dialog { max-width: 800px; }
.modal-inv-detail .modal-content {
    background: #0F172A;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0,0,0,0.6);
    color: #F8FAFC;
}
.modal-inv-detail .modal-header {
    background: linear-gradient(135deg, rgba(6,182,212,0.15) 0%, rgba(56,189,248,0.08) 100%);
    border-bottom: 1px solid rgba(6,182,212,0.2);
    padding: 24px 32px;
}
.modal-inv-detail .modal-body { padding: 32px; max-height: 80vh; overflow-y: auto; }
.modal-inv-detail .modal-footer { border-top: 1px solid rgba(255,255,255,0.08); padding: 20px 32px; background: rgba(0,0,0,0.15); }

/* Invoice Printable Template inside Modal */
.inv-print-wrap {
    background: #1E293B;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 18px;
    padding: 32px;
    color: #E2E8F0;
}
.inv-print-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; }
.inv-brand { font-size: 1.6rem; font-weight: 900; color: #38BDF8; letter-spacing: -0.5px; }
.inv-meta { text-align: right; }
.inv-bill-box { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; padding: 18px; margin-bottom: 24px; }
.inv-items-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
.inv-items-table th { background: rgba(0,0,0,0.3); padding: 12px 16px; font-size: 0.8rem; text-transform: uppercase; color: #94A3B8; text-align: left; }
.inv-items-table td { padding: 14px 16px; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.9rem; }
.inv-summary-box { max-width: 320px; ms-auto; margin-left: auto; text-align: right; }
.inv-summary-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 0.9rem; }
.inv-summary-row.total-row { border-top: 2px solid var(--inv-cyan); font-weight: 800; font-size: 1.1rem; color: #38BDF8; margin-top: 8px; padding-top: 10px; }

/* ═════════════════════════════════════════════════════════════
   LIGHT MODE OVERRIDES FOR ALL INVOICES PAGE
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
html.light-theme .inv-table-card {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 15px 45px rgba(0,0,0,0.05) !important;
}
html.light-theme .inv-table {
    color: #0F172A !important;
}
html.light-theme .inv-table th {
    background: #F1F5F9 !important;
    color: #334155 !important;
    border-bottom-color: #E2E8F0 !important;
}
html.light-theme .inv-table td {
    border-bottom-color: #F1F5F9 !important;
    color: #0F172A !important;
}
html.light-theme .inv-table tbody tr:hover {
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
html.light-theme .status-select-sm.status-unpaid {
    background: #FFE4E6 !important;
    color: #E11D48 !important;
    border-color: #F43F5E !important;
}
html.light-theme .status-select-sm.status-partially_paid {
    background: #FEF3C7 !important;
    color: #D97706 !important;
    border-color: #F59E0B !important;
}
html.light-theme .status-select-sm.status-paid {
    background: #D1FAE5 !important;
    color: #059669 !important;
    border-color: #10B981 !important;
}
html.light-theme .status-select-sm.status-canceled {
    background: #F1F5F9 !important;
    color: #64748B !important;
    border-color: #94A3B8 !important;
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
html.light-theme .modal-inv-detail .modal-content {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    color: #0F172A !important;
    box-shadow: 0 25px 60px rgba(0,0,0,0.15) !important;
}
html.light-theme .modal-inv-detail .modal-header {
    background: linear-gradient(135deg, rgba(2,132,199,0.08) 0%, rgba(3,105,161,0.04) 100%) !important;
    border-bottom-color: #E2E8F0 !important;
}
html.light-theme .modal-inv-detail .modal-header h5,
html.light-theme .modal-inv-detail .modal-header .modal-title {
    color: #0F172A !important;
}
html.light-theme .modal-inv-detail .modal-header .text-muted {
    color: #64748B !important;
}
html.light-theme .modal-inv-detail .btn-close-white {
    filter: invert(1) grayscale(100%) brightness(200%);
}
html.light-theme .modal-inv-detail .modal-footer {
    background: #F8FAFC !important;
    border-top-color: #E2E8F0 !important;
}
html.light-theme .inv-print-wrap {
    background: #F8FAFC !important;
    border-color: #E2E8F0 !important;
    color: #0F172A !important;
}
html.light-theme .inv-brand {
    color: #0284C7 !important;
}
html.light-theme .inv-bill-box {
    background: #FFFFFF !important;
    border-color: #CBD5E1 !important;
}
html.light-theme .inv-items-table th {
    background: #E2E8F0 !important;
    color: #334155 !important;
}
html.light-theme .inv-items-table td {
    border-bottom-color: #E2E8F0 !important;
    color: #0F172A !important;
}
html.light-theme .inv-summary-row.total-row {
    border-top-color: #0284C7 !important;
    color: #0284C7 !important;
}
</style>

{{-- Page Header --}}
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold mb-1 header-title">
            <i class="bx bx-receipt text-info me-2"></i>Financial & Billing System — Invoices
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Manage and track issued invoices for shipment orders and scheduled trips.
        </p>
    </div>
</div>

{{-- ─── KPI Stats Bar ─── --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap total"><i class="bx bx-receipt"></i></div>
            <div>
                <div class="kpi-label">Total Invoices</div>
                <div class="kpi-value">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap unpaid"><i class="bx bx-error-circle"></i></div>
            <div>
                <div class="kpi-label">Unpaid Invoices</div>
                <div class="kpi-value">{{ number_format($stats['unpaid']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap paid"><i class="bx bx-check-circle"></i></div>
            <div>
                <div class="kpi-label">Paid Invoices</div>
                <div class="kpi-value">{{ number_format($stats['paid']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap revenue"><i class="bx bx-dollar-circle"></i></div>
            <div>
                <div class="kpi-label">Total Paid Revenue</div>
                <div class="kpi-value" style="font-size:1.3rem;">KWD {{ number_format($stats['total_revenue'], 2) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ─── Filter & Search Bar ─── --}}
<div class="filter-card">
    <form method="GET" action="{{ route('all.invoices') }}" id="filterForm">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-md-5">
                <div class="position-relative">
                    <input type="text" name="search" class="form-control ps-5" placeholder="Search invoice #, shipment #, customer name..." value="{{ request('search') }}">
                    <span class="position-absolute text-muted" style="left:14px;top:50%;transform:translateY(-50%);"><i class="bx bx-search fs-5"></i></span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Statuses</option>
                    <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="partially_paid" {{ request('status') === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="canceled" {{ request('status') === 'canceled' ? 'selected' : '' }}>Canceled</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
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
                <a href="{{ route('all.invoices') }}" class="btn btn-outline-secondary w-100 rounded-3" title="Reset Filters"><i class="bx bx-refresh fs-5"></i></a>
            </div>
        </div>
    </form>
</div>

{{-- ─── Data Table ─── --}}
<div class="inv-table-card">
    <div class="table-responsive">
        <table class="table inv-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>Linked Resource</th>
                    <th>Base Amount</th>
                    <th>Tax / Discount</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Issued Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                <tr>
                    {{-- Invoice Number --}}
                    <td>
                        <div class="fw-bold text-info">#{{ $inv->invoice_number }}</div>
                        <div class="text-muted" style="font-size:0.75rem;">ID: #{{ $inv->id }}</div>
                    </td>

                    {{-- Customer --}}
                    <td>
                        <div class="fw-bold text-white">
                            {{ $inv->user ? ($inv->user->fname . ' ' . $inv->user->lname) : 'Guest' }}
                        </div>
                        <div class="text-muted" style="font-size:0.78rem;">
                            {{ $inv->user ? $inv->user->email : '—' }}
                        </div>
                    </td>

                    {{-- Linked Resource --}}
                    <td>
                        @if($inv->shipment_id)
                            <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25">
                                <i class="bx bx-package me-1"></i>Shipment #{{ $inv->shipment_id }}
                            </span>
                        @elseif($inv->scheduled_trip_id)
                            <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-25">
                                <i class="bx bx-calendar me-1"></i>Trip #{{ $inv->scheduled_trip_id }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    {{-- Base Amount --}}
                    <td>KWD {{ number_format($inv->base_amount, 2) }}</td>

                    {{-- Tax / Discount --}}
                    <td class="text-muted" style="font-size:0.82rem;">
                        <div>Tax: KWD {{ number_format($inv->tax_amount, 2) }}</div>
                        <div>Disc: KWD {{ number_format($inv->discount, 2) }}</div>
                    </td>

                    {{-- Total Amount --}}
                    <td>
                        <strong class="text-warning" style="font-size:0.95rem;">KWD {{ number_format($inv->total_amount, 2) }}</strong>
                    </td>

                    {{-- Status Dropdown --}}
                    <td>
                        <select class="form-select status-select-sm status-{{ $inv->status }}" onchange="updateInvoiceStatus({{ $inv->id }}, this.value)">
                            <option value="unpaid" {{ $inv->status==='unpaid' ? 'selected' : '' }}>Unpaid</option>
                            <option value="partially_paid" {{ $inv->status==='partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                            <option value="paid" {{ $inv->status==='paid' ? 'selected' : '' }}>Paid</option>
                            <option value="canceled" {{ $inv->status==='canceled' ? 'selected' : '' }}>Canceled</option>
                        </select>
                    </td>

                    {{-- Issued Date --}}
                    <td class="text-muted" style="font-size:0.82rem;">
                        {{ $inv->issued_at ? date('Y-m-d', strtotime($inv->issued_at)) : ($inv->created_at ? $inv->created_at->format('Y-m-d') : '—') }}
                    </td>

                    {{-- Actions --}}
                    <td class="text-end">
                        <div class="d-inline-flex gap-2">
                            <button type="button" class="btn-action" onclick="openInvoiceModal({{ $inv->id }})" title="View Invoice">
                                <i class="bx bx-show fs-5"></i>
                            </button>
                            <a href="{{ route('edit.invoice', $inv->id) }}" class="btn-action" title="Edit Invoice">
                                <i class="bx bx-edit fs-5"></i>
                            </a>
                            <a href="{{ route('delete.invoice', $inv->id) }}" class="btn-action btn-del confirm-delete" title="Delete Invoice">
                                <i class="bx bx-trash fs-5"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="bx bx-receipt fs-1 text-slate-500 mb-2 d-block"></i>
                        No invoices found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    // ── Update Invoice Status via AJAX ──
    function updateInvoiceStatus(id, newStatus) {
        $.ajax({
            url: '{{ route("invoice.status.ajax") }}',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                invoice_id: id,
                status: newStatus
            },
            success: function(res) {
                if (res.status === 'success') {
                    if (typeof toastr !== 'undefined') toastr.success(res.message);
                }
            },
            error: function() {
                if (typeof toastr !== 'undefined') toastr.error('Failed to update invoice status.');
            }
        });
    }

    // ── Open Invoice Details Modal via AJAX ──
    function openInvoiceModal(id) {
        var modal = new bootstrap.Modal(document.getElementById('invoiceDetailsModal'));
        $('#modalLoader').show();
        $('#modalDataWrap').hide().empty();
        modal.show();

        $.ajax({
            url: '{{ route("get.invoice.details.ajax", ":id") }}'.replace(':id', id),
            type: 'GET',
            success: function(res) {
                $('#modalLoader').hide();
                if (res.status === 'success') {
                    renderInvoiceTemplate(res.invoice);
                    $('#modalDataWrap').show();
                } else {
                    $('#modalDataWrap').html('<div class="alert alert-danger">Failed to load invoice.</div>').show();
                }
            },
            error: function() {
                $('#modalLoader').hide();
                $('#modalDataWrap').html('<div class="alert alert-danger">Error fetching invoice data.</div>').show();
            }
        });
    }

    function renderInvoiceTemplate(inv) {
        $('#modalInvoiceTitle').text('Invoice ' + inv.invoice_number);

        var html = '';
        html += '<div class="inv-print-wrap" id="printableArea">';

        // Header
        html += '<div class="inv-print-header">';
        html += '<div><div class="inv-brand">SALASIL LOGISTICS</div><div class="text-muted" style="font-size:0.82rem;">Official Commercial Invoice</div></div>';
        html += '<div class="inv-meta"><h4 class="fw-bold text-white mb-1">' + inv.invoice_number + '</h4>';
        html += '<div class="text-muted" style="font-size:0.82rem;">Date: ' + inv.issued_at + '</div>';
        html += '<div class="text-muted" style="font-size:0.82rem;">Due Date: ' + inv.due_date + '</div>';
        html += '</div></div>';

        // Customer & Order Box
        html += '<div class="inv-bill-box"><div class="row g-3">';
        html += '<div class="col-12 col-md-6"><div class="text-uppercase text-muted fw-bold" style="font-size:0.75rem;">Billed To</div>';
        html += '<div class="fw-bold text-white fs-6">' + inv.customer_name + '</div>';
        html += '<div class="text-slate-300" style="font-size:0.85rem;">' + inv.customer_email + '</div>';
        html += '<div class="text-slate-300" style="font-size:0.85rem;">' + inv.customer_phone + '</div></div>';

        html += '<div class="col-12 col-md-6"><div class="text-uppercase text-muted fw-bold" style="font-size:0.75rem;">Order Reference</div>';
        html += '<div class="fw-bold text-info">Shipment Order ' + inv.shipment_id + '</div>';
        html += '<div class="text-slate-300" style="font-size:0.85rem;">Route: ' + inv.pickup_location + ' ➔ ' + inv.dropoff_location + '</div>';
        html += '<div class="mt-1"><span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-25">' + inv.status.toUpperCase() + '</span></div></div>';
        html += '</div></div>';

        // Table
        html += '<table class="inv-items-table"><thead><tr><th>Item Description</th><th class="text-end">Amount</th></tr></thead><tbody>';
        html += '<tr><td>Logistics & Freight Services for Shipment ' + inv.shipment_id + ' (' + inv.shipment_name + ')</td><td class="text-end fw-bold text-white">KWD ' + inv.base_amount + '</td></tr>';
        html += '</tbody></table>';

        // Summary
        html += '<div class="inv-summary-box">';
        html += '<div class="inv-summary-row"><span class="text-muted">Base Amount:</span><span class="text-white">KWD ' + inv.base_amount + '</span></div>';
        html += '<div class="inv-summary-row"><span class="text-muted">Tax:</span><span class="text-white">KWD ' + inv.tax_amount + '</span></div>';
        html += '<div class="inv-summary-row"><span class="text-muted">Discount:</span><span class="text-white">- KWD ' + inv.discount + '</span></div>';
        html += '<div class="inv-summary-row total-row"><span>Total Due:</span><span>KWD ' + inv.total_amount + '</span></div>';
        html += '</div>';

        html += '</div>';

        $('#modalDataWrap').html(html);
    }

    // ── SweetAlert Delete Confirmation ──
    $(document).on('click', '.confirm-delete', function(e) {
        e.preventDefault();
        var link = $(this).attr('href');
        Swal.fire({
            title: 'Delete Invoice?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#F43F5E',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Yes, Delete Invoice!',
            background: '#1E293B',
            color: '#F8FAFC'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = link;
            }
        });
    });
</script>

{{-- INVOICE DETAILS & PRINT MODAL DRAWER --}}
<div class="modal fade modal-inv-detail" id="invoiceDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-info bg-opacity-10 p-2 border border-info border-opacity-25 text-info">
                        <i class="bx bx-receipt fs-3"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="modalInvoiceTitle">Invoice Details</h5>
                        <small class="text-muted">Formatted commercial invoice statement</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-5" id="modalLoader">
                    <div class="spinner-border text-info" role="status"></div>
                    <div class="text-info mt-2">Loading invoice details...</div>
                </div>
                <div id="modalDataWrap" style="display:none;"></div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary rounded-3 px-4" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-info rounded-3 px-4 fw-bold" onclick="window.print()" style="background:linear-gradient(135deg,#06B6D4,#38BDF8);border:none;color:#0F172A;">
                    <i class="bx bx-printer me-1"></i>Print Invoice
                </button>
            </div>
        </div>
    </div>
</div>

@endsection
