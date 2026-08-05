@extends('admin.master_admin')
@section('admin')

{{-- ========================
     ALL SHIPMENTS MANAGEMENT
======================== --}}

<style>
:root {
    --sh-cyan:       #06B6D4;
    --sh-cyan-light: #38BDF8;
    --sh-navy:       #0F172A;
    --sh-card:       #1E293B;
    --sh-border:     rgba(255,255,255,0.08);
    --sh-success:    #10B981;
    --sh-warning:    #F59E0B;
    --sh-danger:     #F43F5E;
    --sh-purple:     #8B5CF6;
}

/* ─── KPI Cards ─── */
.kpi-card {
    background: linear-gradient(135deg, rgba(30,41,59,0.9) 0%, rgba(15,23,42,0.95) 100%);
    border: 1px solid var(--sh-border);
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
.kpi-icon-wrap.new      { background: linear-gradient(135deg, #F59E0B, #FCD34D); box-shadow: 0 8px 20px rgba(245,158,11,0.3); color:#0F172A; }
.kpi-icon-wrap.approved { background: linear-gradient(135deg, #10B981, #34D399); box-shadow: 0 8px 20px rgba(16,185,129,0.3); }
.kpi-icon-wrap.revenue  { background: linear-gradient(135deg, #8B5CF6, #A78BFA); box-shadow: 0 8px 20px rgba(139,92,246,0.3); }

.kpi-label { font-size: 0.8rem; font-weight: 700; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.5px; }
.kpi-value { font-size: 1.6rem; font-weight: 800; color: #F8FAFC; margin-top: 2px; }

/* ─── Filter Bar ─── */
.filter-card {
    background: rgba(30,41,59,0.7);
    border: 1px solid var(--sh-border);
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
    border-color: var(--sh-cyan) !important;
    box-shadow: 0 0 0 3px rgba(6,182,212,0.15) !important;
}
.filter-card .form-select option { background: #1E293B; color: #F8FAFC; }

/* ─── Data Table Card ─── */
.sh-table-card {
    background: linear-gradient(135deg, rgba(30,41,59,0.95) 0%, rgba(15,23,42,0.98) 100%);
    border: 1px solid var(--sh-border);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0,0,0,0.35);
}
.sh-table {
    width: 100%;
    margin-bottom: 0;
    color: #CBD5E1;
    border-collapse: separate;
    border-spacing: 0;
}
.sh-table th {
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
.sh-table td {
    padding: 16px 20px;
    vertical-align: middle;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    font-size: 0.88rem;
}
.sh-table tbody tr {
    transition: background 0.2s;
}
.sh-table tbody tr:hover {
    background: rgba(6,182,212,0.05);
}

/* ─── Badges & Status Pill ─── */
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
.status-select-sm.status-new               { border-color: rgba(6,182,212,0.5) !important; color: #38BDF8 !important; }
.status-select-sm.status-under_review      { border-color: rgba(245,158,11,0.5) !important; color: #F59E0B !important; }
.status-select-sm.status-pending_approval  { border-color: rgba(139,92,246,0.5) !important; color: #A78BFA !important; }
.status-select-sm.status-approved          { border-color: rgba(16,185,129,0.5) !important; color: #34D399 !important; }
.status-select-sm.status-rejected          { border-color: rgba(244,63,94,0.5) !important;  color: #F43F5E !important; }
.status-select-sm.status-canceled          { border-color: rgba(100,116,139,0.5) !important; color: #94A3B8 !important; }

.badge-pay-paid   { background: rgba(16,185,129,0.15); color: #34D399; border: 1px solid rgba(16,185,129,0.3); border-radius: 50px; padding: 3px 10px; font-size: 0.75rem; font-weight: 700; }
.badge-pay-unpaid { background: rgba(244,63,94,0.15);  color: #F43F5E; border: 1px solid rgba(244,63,94,0.3);  border-radius: 50px; padding: 3px 10px; font-size: 0.75rem; font-weight: 700; }

.fragile-badge {
    background: rgba(244,63,94,0.15);
    color: #F43F5E;
    border: 1px solid rgba(244,63,94,0.3);
    border-radius: 6px;
    padding: 2px 6px;
    font-size: 0.7rem;
    font-weight: 700;
}

/* ─── Route Indicator ─── */
.route-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
}
.route-city { font-weight: 700; color: #F8FAFC; }
.route-arrow { color: var(--sh-cyan); font-size: 16px; }

/* ─── Action Buttons ─── */
.btn-action {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: inline-flex; align-items: center; justify-content: center;
    border: 1px solid var(--sh-border);
    background: rgba(255,255,255,0.04);
    color: #CBD5E1;
    transition: all 0.2s;
}
.btn-action:hover {
    background: rgba(6,182,212,0.15);
    border-color: var(--sh-cyan);
    color: #38BDF8;
    transform: translateY(-2px);
}
.btn-action.btn-del:hover {
    background: rgba(244,63,94,0.15);
    border-color: var(--sh-danger);
    color: #F43F5E;
}

/* ─── Full Details Modal / Drawer ─── */
.modal-sh-detail .modal-dialog {
    max-width: 900px;
}
.modal-sh-detail .modal-content {
    background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%);
    border: 1px solid var(--sh-border);
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0,0,0,0.6);
    color: #F8FAFC;
}
.modal-sh-detail .modal-header {
    background: linear-gradient(135deg, rgba(6,182,212,0.15) 0%, rgba(56,189,248,0.08) 100%);
    border-bottom: 1px solid rgba(6,182,212,0.2);
    padding: 24px 32px;
}
.modal-sh-detail .modal-body {
    padding: 32px;
    max-height: 80vh;
    overflow-y: auto;
}
.modal-sh-detail .modal-footer {
    border-top: 1px solid var(--sh-border);
    padding: 20px 32px;
    background: rgba(0,0,0,0.15);
}

.detail-sec-title {
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: var(--sh-cyan-light);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.detail-sec-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, rgba(6,182,212,0.4), transparent);
}

.detail-card-grid {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 14px;
    padding: 18px 20px;
    margin-bottom: 20px;
    overflow: hidden;
}
.detail-row {
    display: flex;
    gap: 12px;
    margin-bottom: 10px;
    align-items: flex-start;
    min-width: 0;
}
.detail-row:last-child { margin-bottom: 0; }
.detail-row .d-label {
    font-size: 0.78rem;
    font-weight: 700;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    min-width: 140px;
    flex-shrink: 0;
}
.detail-row .d-value {
    font-size: 0.88rem;
    color: #E2E8F0;
    font-weight: 500;
    flex: 1;
    min-width: 0;
    word-break: break-word;
    overflow-wrap: anywhere;
}

.user-avatar-sm {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: rgba(6,182,212,0.15);
    border: 1px solid rgba(6,182,212,0.3);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; color: var(--sh-cyan-light);
    font-size: 14px;
    flex-shrink: 0;
}

/* ═════════════════════════════════════════════════════════════
   LIGHT MODE OVERRIDES FOR ALL SHIPMENTS MANAGEMENT
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
html.light-theme .filter-card .form-select option,
html.light-theme .status-select-sm option {
    background: #FFFFFF !important;
    color: #0F172A !important;
}
html.light-theme .sh-table-card {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 15px 45px rgba(0,0,0,0.05) !important;
}
html.light-theme .sh-table {
    color: #0F172A !important;
}
html.light-theme .sh-table th {
    background: #F1F5F9 !important;
    color: #334155 !important;
    border-bottom-color: #E2E8F0 !important;
}
html.light-theme .sh-table td {
    border-bottom-color: #E2E8F0 !important;
    color: #0F172A !important;
}
html.light-theme .sh-table tbody tr:hover {
    background: #F8FAFC !important;
}
html.light-theme .route-city {
    color: #0F172A !important;
}
html.light-theme .btn-action {
    background: #F1F5F9 !important;
    border-color: #CBD5E1 !important;
    color: #475569 !important;
}
html.light-theme .btn-action:hover {
    background: #E0F2FE !important;
    color: #0284C7 !important;
    border-color: #0284C7 !important;
}
html.light-theme .modal-sh-detail .modal-content {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    color: #0F172A !important;
    box-shadow: 0 25px 60px rgba(0,0,0,0.15) !important;
}
html.light-theme .modal-sh-detail .modal-header {
    background: linear-gradient(135deg, rgba(2,132,199,0.08) 0%, rgba(3,105,161,0.04) 100%) !important;
    border-bottom-color: #E2E8F0 !important;
}
html.light-theme .modal-sh-detail .modal-header .modal-title,
html.light-theme .modal-sh-detail .modal-header h5 {
    color: #0F172A !important;
}
html.light-theme .modal-sh-detail .modal-footer {
    background: #F8FAFC !important;
    border-top-color: #E2E8F0 !important;
}
html.light-theme .detail-card-grid {
    background: #F8FAFC !important;
    border-color: #E2E8F0 !important;
}
html.light-theme .detail-row .d-label {
    color: #475569 !important;
}
html.light-theme .detail-row .d-value {
    color: #0F172A !important;
}
html.light-theme .status-select-sm {
    background: #F8FAFC !important;
}

/* ─── Responsive Mobile View Fixes ─── */
@media (max-width: 767.98px) {
    .modal-sh-detail .modal-dialog {
        margin: 0.5rem;
    }
    .modal-sh-detail .modal-header {
        padding: 16px 20px;
    }
    .modal-sh-detail .modal-body {
        padding: 20px 14px;
        max-height: 82vh;
    }
    .modal-sh-detail .modal-footer {
        padding: 14px 20px;
    }
    .modal-sh-detail .border-end {
        border-right: none !important;
        border-bottom: 1px solid rgba(255,255,255,0.1) !important;
        padding-bottom: 1rem !important;
        padding-right: 0 !important;
        margin-bottom: 1rem !important;
    }
    html.light-theme .modal-sh-detail .border-end {
        border-bottom-color: #E2E8F0 !important;
    }
    .modal-sh-detail .ps-3 {
        padding-left: 0 !important;
    }
    .detail-card-grid {
        padding: 14px 12px;
        margin-bottom: 16px;
    }
    .detail-sec-title {
        font-size: 0.75rem;
        margin-bottom: 12px;
    }
    .detail-row {
        flex-direction: column;
        gap: 3px;
        margin-bottom: 12px;
    }
    .detail-row .d-label {
        min-width: auto;
        font-size: 0.72rem;
    }
    .detail-row .d-value {
        font-size: 0.84rem;
        width: 100%;
    }
}
</style>

{{-- Page Header --}}
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold mb-1 header-title">
            <i class="bx bx-package text-info me-2"></i>All Shipment Orders
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Manage and inspect all 40+ attributes for created shipment orders.
        </p>
    </div>
    <div>
        <a href="{{ route('add.shipment') }}" class="btn btn-info rounded-3 px-3 py-2 fw-bold d-inline-flex align-items-center gap-2" style="background:linear-gradient(135deg,#06B6D4,#38BDF8);border:none;color:#0F172A;">
            <i class="bx bx-plus-circle fs-5"></i>
            <span>Add New Shipment</span>
        </a>
    </div>
</div>

{{-- ─── KPI Stats Bar ─── --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap total"><i class="bx bx-package"></i></div>
            <div>
                <div class="kpi-label">Total Shipments</div>
                <div class="kpi-value">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap new"><i class="bx bx-pulse"></i></div>
            <div>
                <div class="kpi-label">New Orders</div>
                <div class="kpi-value">{{ number_format($stats['new']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap approved"><i class="bx bx-check-double"></i></div>
            <div>
                <div class="kpi-label">Approved</div>
                <div class="kpi-value">{{ number_format($stats['approved']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap revenue"><i class="bx bx-dollar-circle"></i></div>
            <div>
                <div class="kpi-label">Total Revenue</div>
                <div class="kpi-value"><small style="font-size:1rem;color:#A78BFA;">KWD</small> {{ number_format($stats['revenue'], 2) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ─── Filter & Search Bar ─── --}}
<div class="filter-card">
    <form method="GET" action="{{ route('all.shipments') }}" id="filterForm">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-md-4">
                <div class="position-relative">
                    <input type="text" name="search" class="form-control ps-5" placeholder="Search by name, ID, phone, address..." value="{{ request('search') }}">
                    <span class="position-absolute text-muted" style="left:14px;top:50%;transform:translateY(-50%);"><i class="bx bx-search fs-5"></i></span>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <select name="status" class="form-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Statuses</option>
                    <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>New</option>
                    <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
                    <option value="pending_approval" {{ request('status') === 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="canceled" {{ request('status') === 'canceled' ? 'selected' : '' }}>Canceled</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="payment_status" class="form-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Payment</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <select name="customer_id" class="form-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Customers</option>
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->fname }} {{ $c->lname }} ({{ $c->role === 'company_customer' ? 'Company' : 'Individual' }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-1 text-end">
                <a href="{{ route('all.shipments') }}" class="btn btn-outline-secondary w-100 rounded-3" title="Reset Filters"><i class="bx bx-refresh fs-5"></i></a>
            </div>
        </div>
    </form>
</div>

{{-- ─── Data Table Card ─── --}}
<div class="sh-table-card">
    <div class="table-responsive">
        <table class="table sh-table">
            <thead>
                <tr>
                    <th># ID</th>
                    <th>Shipment & Customer</th>
                    <th>Route</th>
                    <th>Truck Specifications</th>
                    <th>Price & Payment</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shipments as $sh)
                <tr>
                    {{-- ID & Fragile tag --}}
                    <td>
                        <span class="fw-bold text-white">#{{ $sh->id }}</span>
                        @if($sh->is_fragile)
                        <div><span class="fragile-badge mt-1 d-inline-block"><i class="bx bx-error-circle me-1"></i>Fragile</span></div>
                        @endif
                    </td>

                    {{-- Shipment & Customer --}}
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="user-avatar-sm">
                                {{ strtoupper(substr($sh->customer->fname ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-bold text-white" style="font-size:0.92rem;">{{ $sh->shipment_name ?: 'Shipment #' . $sh->id }}</div>
                                <div class="text-muted" style="font-size:0.78rem;">
                                    <i class="bx bx-user me-1 text-info"></i>{{ $sh->customer->fname ?? '—' }} {{ $sh->customer->lname ?? '' }}
                                    @if($sh->customer && $sh->customer->companyProfile)
                                        <span class="text-warning ms-1">({{ $sh->customer->companyProfile->company_name }})</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Route --}}
                    <td>
                        <div class="route-indicator">
                            <span class="route-city"><i class="bx bx-map-pin me-1 text-info"></i>{{ $sh->pickupCity->name_en ?? ($sh->pickup_area ?: 'Pickup') }}</span>
                            <i class="bx bx-right-arrow-alt route-arrow"></i>
                            <span class="route-city"><i class="bx bx-navigation me-1 text-success"></i>{{ $sh->dropoffCity->name_en ?? ($sh->dropoff_area ?: 'Dropoff') }}</span>
                        </div>
                        <div class="text-muted text-truncate" style="font-size:0.75rem;max-width:220px;" title="{{ $sh->pickup_address }} ➔ {{ $sh->dropoff_address }}">
                            {{ $sh->pickup_address }}
                        </div>
                    </td>

                    {{-- Truck Specs --}}
                    <td>
                        <div class="fw-semibold text-slate-200">
                            <i class="bx bx-truck me-1 text-info"></i>{{ $sh->truckType->name_en ?? 'Any Truck' }}
                        </div>
                        <div class="text-muted" style="font-size:0.78rem;">
                            @if($sh->weight) {{ number_format($sh->weight, 1) }} kg @endif
                            @if($sh->packages_count) • {{ $sh->packages_count }} Pcs @endif
                        </div>
                    </td>

                    {{-- Price & Payment --}}
                    <td>
                        <div class="fw-bold text-white">KWD {{ number_format($sh->initial_price ?? 0, 2) }}</div>
                        <div class="mt-1">
                            <span class="{{ $sh->payment_status === 'paid' ? 'badge-pay-paid' : 'badge-pay-unpaid' }}">
                                {{ ucfirst($sh->payment_status ?? 'unpaid') }}
                            </span>
                        </div>
                    </td>

                    {{-- Status Dropdown --}}
                    <td>
                        <select class="form-select status-select-sm status-{{ $sh->status }}" onchange="updateShipmentStatus({{ $sh->id }}, this.value)">
                            <option value="new" {{ $sh->status==='new' ? 'selected' : '' }}>New</option>
                            <option value="under_review" {{ $sh->status==='under_review' ? 'selected' : '' }}>Under Review</option>
                            <option value="pending_approval" {{ $sh->status==='pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                            <option value="approved" {{ $sh->status==='approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ $sh->status==='rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="canceled" {{ $sh->status==='canceled' ? 'selected' : '' }}>Canceled</option>
                        </select>
                    </td>

                    {{-- Actions --}}
                    <td class="text-end">
                        <div class="d-inline-flex gap-2">
                            <button type="button" class="btn-action" onclick="openShipmentDetails({{ $sh->id }})" title="View Full Details (40+ Fields)">
                                <i class="bx bx-show fs-5"></i>
                            </button>
                            <a href="{{ route('edit.shipment', $sh->id) }}" class="btn-action" title="Edit Shipment">
                                <i class="bx bx-edit fs-5"></i>
                            </a>
                            <a href="{{ route('delete.shipment', $sh->id) }}" class="btn-action btn-del confirm-delete" title="Delete Shipment">
                                <i class="bx bx-trash fs-5"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bx bx-package fs-1 text-slate-500 mb-2 d-block"></i>
                        No shipment orders found matching your criteria.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ─── FULL DETAILS MODAL DRAWER (40+ Fields) ─── --}}
<div class="modal fade modal-sh-detail" id="shipmentDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-info bg-opacity-10 p-2 border border-info border-opacity-25 text-info">
                        <i class="bx bx-package fs-3"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="modalTitle">Shipment Details</h5>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBodyContent">
                <div class="text-center py-5" id="modalLoader">
                    <div class="spinner-border text-info" role="status"></div>
                    <div class="text-info mt-2">Loading full shipment specification...</div>
                </div>
                <div id="modalDataWrap" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-3 px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript Helpers --}}
<script>
    // ── Open Shipment Details Modal via AJAX ──
    function openShipmentDetails(id) {
        var modal = new bootstrap.Modal(document.getElementById('shipmentDetailsModal'));
        $('#modalLoader').show();
        $('#modalDataWrap').hide().empty();
        modal.show();

        $.ajax({
            url: '{{ route("get.shipment.details.ajax", ":id") }}'.replace(':id', id),
            type: 'GET',
            success: function(res) {
                $('#modalLoader').hide();
                if (res.status === 'success') {
                    renderDetailsModal(res.shipment);
                    $('#modalDataWrap').show();
                } else {
                    $('#modalDataWrap').html('<div class="alert alert-danger">Failed to load details.</div>').show();
                }
            },
            error: function() {
                $('#modalLoader').hide();
                $('#modalDataWrap').html('<div class="alert alert-danger">Error fetching shipment data.</div>').show();
            }
        });
    }

    // ── Render All 40+ Fields cleanly in modal ──
    function renderDetailsModal(s) {
        $('#modalTitle').text((s.shipment_name && s.shipment_name !== '—' ? s.shipment_name : 'Shipment Order') + ' #' + s.id);

        var html = '';

        // SECTION 1: General & Classification
        html += '<div class="detail-sec-title"><i class="bx bx-info-circle me-1"></i>General Specification & Classification</div>';
        html += '<div class="detail-card-grid"><div class="row g-2">';
        html += dRow('Shipment ID', '#' + s.id);
        html += dRow('Shipment Name', s.shipment_name);
        html += dRow('Status', '<span class="badge bg-info text-dark fw-bold">' + formatStatus(s.status) + '</span>');
        html += dRow('Shipment Type', s.shipment_type);
        html += dRow('Shipment Nature', s.shipment_nature);
        if (s.hs_code) {
            html += dRow('HS Tariff Code', '<span class="badge bg-info text-dark fw-bold me-1">HS: ' + s.hs_code + '</span>' + (s.hs_code_description ? '<small class="text-slate-300 d-block mt-0.5">' + s.hs_code_description + '</small>' : ''));
        }
        html += dRow('Fragile Cargo', s.is_fragile ? '<span class="text-danger fw-bold"><i class="bx bx-error-circle me-1"></i>YES</span>' : 'NO');
        html += dRow('Description', s.shipment_description);
        html += dRow('Created At', s.created_at);
        html += '</div></div>';

        // SECTION 2: Customer & Driver Information
        html += '<div class="detail-sec-title"><i class="bx bx-user me-1"></i>Customer & Driver Profiles</div>';
        html += '<div class="detail-card-grid"><div class="row g-2">';
        if (s.customer) {
            html += dRow('Customer Name', s.customer.name);
            html += dRow('Customer Phone', s.customer.phone);
            html += dRow('Customer Email', s.customer.email);
            html += dRow('Customer Type', s.customer.role === 'company_customer' ? 'Company (' + (s.customer.company_name || '—') + ')' : 'Individual');
        } else {
            html += dRow('Customer', '—');
        }
        if (s.driver) {
            html += dRow('Assigned Driver', s.driver.name + ' (' + s.driver.phone + ')');
        } else {
            html += dRow('Assigned Driver', '<span class="text-warning">Unassigned</span>');
        }
        html += '</div></div>';

        // SECTION 3: Route & Locations
        html += '<div class="detail-sec-title"><i class="bx bx-map me-1"></i>Pickup & Delivery Locations</div>';
        html += '<div class="detail-card-grid"><div class="row g-3">';
        html += '<div class="col-12 col-md-6 border-end border-secondary border-opacity-25 pe-3">';
        html += '<h6 class="text-info font-bold mb-2"><i class="bx bx-map-pin me-1"></i>Pickup Location</h6>';
        html += dRow('Country / City', s.pickup.country + ' / ' + s.pickup.city);
        html += dRow('Area', s.pickup.area);
        html += dRow('Address', s.pickup.address);
        if (s.pickup.lat && s.pickup.lng) {
            html += dRow('Coordinates', s.pickup.lat + ', ' + s.pickup.lng + ' <a href="https://maps.google.com/?q=' + s.pickup.lat + ',' + s.pickup.lng + '" target="_blank" class="text-info ms-1"><i class="bx bx-link-external"></i> Map</a>');
        }
        html += '</div>';

        html += '<div class="col-12 col-md-6 ps-3">';
        html += '<h6 class="text-success font-bold mb-2"><i class="bx bx-navigation me-1"></i>Delivery Location</h6>';
        html += dRow('Country / City', s.dropoff.country + ' / ' + s.dropoff.city);
        html += dRow('Area', s.dropoff.area);
        html += dRow('Address', s.dropoff.address);
        if (s.dropoff.lat && s.dropoff.lng) {
            html += dRow('Coordinates', s.dropoff.lat + ', ' + s.dropoff.lng + ' <a href="https://maps.google.com/?q=' + s.dropoff.lat + ',' + s.dropoff.lng + '" target="_blank" class="text-success ms-1"><i class="bx bx-link-external"></i> Map</a>');
        }
        html += '</div>';
        html += '</div></div>';

        // SECTION 4: Loading & Receiving Contacts
        html += '<div class="detail-sec-title"><i class="bx bx-phone me-1"></i>On-Site Contacts</div>';
        html += '<div class="detail-card-grid"><div class="row g-2">';
        html += dRow('Loading Contact Name', s.loading_contact_name);
        html += dRow('Loading Contact Phone', s.loading_contact_phone);
        html += dRow('Receiving Contact Name', s.arrival_contact_name);
        html += dRow('Receiving Contact Phone', s.arrival_contact_phone);
        html += '</div></div>';

        // SECTION 5: Cargo Specs & Pricing
        html += '<div class="detail-sec-title"><i class="bx bx-box me-1"></i>Cargo Specs & Pricing</div>';
        html += '<div class="detail-card-grid"><div class="row g-2">';
        html += dRow('Truck Type', s.truck_type);
        html += dRow('Truck Sub-Type', s.truck_sub_type);
        html += dRow('Dimensions (L×W×H)', s.dimensions);
        html += dRow('Weight', s.weight !== '—' ? s.weight + ' kg' : '—');
        html += dRow('No. of Pieces', s.packages_count);
        html += dRow('Goods Description', s.goods_description);
        html += dRow('Estimated Price', '<strong class="text-warning">KWD ' + s.initial_price + '</strong>');
        html += dRow('Payment Status', '<span class="badge bg-secondary">' + s.payment_status + '</span>');
        html += dRow('Payment Method', s.payment_method);
        html += '</div></div>';

        // SECTION 6: Timestamps & Delays
        html += '<div class="detail-sec-title"><i class="bx bx-time me-1"></i>Timestamps & Tracking Events</div>';
        html += '<div class="detail-card-grid"><div class="row g-2">';
        html += dRow('Scheduled Date', s.scheduled_date);
        html += dRow('Driver Arrival at Loading', s.driver_arrival_at_loading);
        html += dRow('Loading Start / End', s.loading_start_at + ' / ' + s.loading_end_at);
        html += dRow('Trip Start', s.trip_start_at);
        html += dRow('Unloading Start / End', s.unloading_start_at + ' / ' + s.unloading_end_at);
        html += dRow('Delay Reason', s.delay_reason);
        html += '</div></div>';

        $('#modalDataWrap').html(html);
    }

    function dRow(label, value) {
        return '<div class="col-12 col-md-6"><div class="detail-row"><div class="d-label">' + label + '</div><div class="d-value">' + (value || '—') + '</div></div></div>';
    }

    function formatStatus(st) {
        if (!st) return '—';
        return st.replace(/_/g, ' ').toUpperCase();
    }

    // ── Update Shipment Status via AJAX ──
    function updateShipmentStatus(id, newStatus) {
        $.ajax({
            url: '{{ route("shipment.status.ajax") }}',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                shipment_id: id,
                status: newStatus
            },
            success: function(res) {
                if (res.status === 'success') {
                    if (typeof toastr !== 'undefined') toastr.success(res.message);
                }
            },
            error: function() {
                if (typeof toastr !== 'undefined') toastr.error('Failed to update status.');
            }
        });
    }

    // ── SweetAlert Delete Confirmation ──
    $(document).on('click', '.confirm-delete', function(e) {
        e.preventDefault();
        var link = $(this).attr('href');
        Swal.fire({
            title: 'Delete Shipment Order?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#F43F5E',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Yes, Delete Order!',
            background: '#1E293B',
            color: '#F8FAFC'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = link;
            }
        });
    });
</script>

@endsection
