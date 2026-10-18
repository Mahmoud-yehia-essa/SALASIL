@extends('admin.master_admin')
@section('admin')

{{-- ============================================================
     ALL SCHEDULED TRIP ORDERS MANAGEMENT SCREEN
============================================================ --}}

<style>
:root {
    --sto-cyan:       #06B6D4;
    --sto-cyan-light: #38BDF8;
    --sto-navy:       #0F172A;
    --sto-card:       #1E293B;
    --sto-border:     rgba(255,255,255,0.08);
    --sto-success:    #10B981;
    --sto-warning:    #F59E0B;
    --sto-danger:     #F43F5E;
    --sto-purple:     #8B5CF6;
}

/* ─── KPI Cards ─── */
.kpi-card {
    background: linear-gradient(135deg, rgba(30,41,59,0.9) 0%, rgba(15,23,42,0.95) 100%);
    border: 1px solid var(--sto-border);
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
.kpi-icon-wrap.new       { background: linear-gradient(135deg, #F59E0B, #FCD34D); box-shadow: 0 8px 20px rgba(245,158,11,0.3); color:#0F172A; }
.kpi-icon-wrap.transit   { background: linear-gradient(135deg, #3B82F6, #60A5FA); box-shadow: 0 8px 20px rgba(59,130,246,0.3); }
.kpi-icon-wrap.delivered { background: linear-gradient(135deg, #10B981, #34D399); box-shadow: 0 8px 20px rgba(16,185,129,0.3); }
.kpi-icon-wrap.revenue   { background: linear-gradient(135deg, #8B5CF6, #A78BFA); box-shadow: 0 8px 20px rgba(139,92,246,0.3); }

.kpi-label { font-size: 0.8rem; font-weight: 700; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.5px; }
.kpi-value { font-size: 1.6rem; font-weight: 800; color: #F8FAFC; margin-top: 2px; }

/* ─── Filter Bar ─── */
.filter-card {
    background: rgba(30,41,59,0.7);
    border: 1px solid var(--sto-border);
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
    border-color: var(--sto-cyan) !important;
    box-shadow: 0 0 0 3px rgba(6,182,212,0.15) !important;
}
.filter-card .form-select option { background: #1E293B; color: #F8FAFC; }

/* ─── Data Table Card ─── */
.sto-table-card {
    background: linear-gradient(135deg, rgba(30,41,59,0.95) 0%, rgba(15,23,42,0.98) 100%);
    border: 1px solid var(--sto-border);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0,0,0,0.35);
}
.sto-table {
    width: 100%;
    margin-bottom: 0;
    color: #CBD5E1;
    border-collapse: separate;
    border-spacing: 0;
}
.sto-table th {
    background: rgba(15,23,42,0.9);
    color: #94A3B8;
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    padding: 16px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}
.sto-table td {
    padding: 16px 20px;
    vertical-align: middle;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    font-size: 0.88rem;
}
.sto-table tbody tr:hover { background: rgba(6,182,212,0.04); }

/* Status dropdown in table */
.status-select-sm {
    font-size: 0.78rem !important;
    font-weight: 700 !important;
    border-radius: 20px !important;
    padding: 4px 10px 4px 10px !important;
    width: auto !important;
    display: inline-block !important;
    border: 1px solid transparent !important;
}
.status-new        { background: rgba(245,158,11,0.15) !important; color: #FBBF24 !important; border-color: rgba(245,158,11,0.3) !important; }
.status-confirmed  { background: rgba(59,130,246,0.15) !important; color: #60A5FA !important; border-color: rgba(59,130,246,0.3) !important; }
.status-loaded     { background: rgba(139,92,246,0.15) !important; color: #C084FC !important; border-color: rgba(139,92,246,0.3) !important; }
.status-in_transit { background: rgba(6,182,212,0.15) !important; color: #38BDF8 !important; border-color: rgba(6,182,212,0.3) !important; }
.status-delivered  { background: rgba(16,185,129,0.15) !important; color: #34D399 !important; border-color: rgba(16,185,129,0.3) !important; }
.status-canceled   { background: rgba(244,63,94,0.15) !important; color: #FB7185 !important; border-color: rgba(244,63,94,0.3) !important; }

/* Action buttons */
.btn-action {
    width: 34px; height: 34px; border-radius: 10px;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
    color: #94A3B8; transition: all 0.2s;
}
.btn-action:hover { background: rgba(6,182,212,0.15); border-color: var(--sto-cyan); color: var(--sto-cyan-light); }
.btn-action.btn-del:hover { background: rgba(244,63,94,0.15); border-color: var(--sto-danger); color: #F43F5E; }

/* ─── Modal Details Drawer ─── */
.modal-sto-detail .modal-dialog { max-width: 850px; }
.modal-sto-detail .modal-content {
    background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 24px;
    overflow: hidden;
    color: #F8FAFC;
}
.modal-sto-detail .modal-header {
    background: linear-gradient(135deg, rgba(6,182,212,0.15) 0%, rgba(56,189,248,0.08) 100%);
    border-bottom: 1px solid rgba(6,182,212,0.2);
    padding: 22px 30px;
}
.modal-sto-detail .modal-body { padding: 30px; max-height: 80vh; overflow-y: auto; }
.modal-sto-detail .modal-footer { border-top: 1px solid rgba(255,255,255,0.08); padding: 18px 30px; background: rgba(0,0,0,0.15); }

.detail-sec-title {
    font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px;
    color: var(--sto-cyan-light); margin-bottom: 14px; display: flex; align-items: center; gap: 8px;
}
.detail-sec-title::after { content: ''; flex: 1; height: 1px; background: linear-gradient(90deg, rgba(6,182,212,0.4), transparent); }
.detail-grid-box { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 14px; padding: 18px; margin-bottom: 18px; }
.detail-item { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.88rem; }
.detail-item:last-child { margin-bottom: 0; }
.detail-item .lbl { color: #94A3B8; font-weight: 600; }
.detail-item .val { color: #F8FAFC; font-weight: 700; }

/* ─── Light Mode Overrides ─── */
html.light-theme .kpi-card,
html.light-theme .filter-card,
html.light-theme .sto-table-card {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.04) !important;
}
html.light-theme .kpi-value,
html.light-theme .sto-table {
    color: #0F172A !important;
}
html.light-theme .sto-table th {
    background: #F1F5F9 !important;
    color: #334155 !important;
    border-bottom-color: #E2E8F0 !important;
}
html.light-theme .sto-table td {
    border-bottom-color: #F1F5F9 !important;
    color: #0F172A !important;
}
html.light-theme .sto-table tbody tr:hover {
    background: #F8FAFC !important;
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

/* Modal details in light mode */
html.light-theme .modal-sto-detail .modal-content {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    color: #0F172A !important;
    box-shadow: 0 25px 60px rgba(0,0,0,0.15) !important;
}
html.light-theme .modal-sto-detail .modal-header {
    background: #F8FAFC !important;
    border-color: #E2E8F0 !important;
}
html.light-theme .modal-sto-detail .modal-header h5,
html.light-theme .modal-sto-detail .modal-header .modal-title {
    color: #0F172A !important;
}
html.light-theme .modal-sto-detail .modal-header .text-muted {
    color: #64748B !important;
}
html.light-theme .modal-sto-detail .btn-close-white {
    filter: invert(1) grayscale(100%) brightness(20%) !important;
}
html.light-theme .modal-sto-detail .modal-footer {
    background: #F8FAFC !important;
    border-color: #E2E8F0 !important;
}
html.light-theme .detail-grid-box {
    background: #F8FAFC !important;
    border-color: #E2E8F0 !important;
}
html.light-theme .detail-item .lbl {
    color: #64748B !important;
}
html.light-theme .detail-item .val {
    color: #0F172A !important;
}
html.light-theme .detail-item .val.text-white {
    color: #0F172A !important;
}
html.light-theme .doc-card-item {
    background: #F8FAFC !important;
    border-color: #E2E8F0 !important;
}
html.light-theme .doc-card-title {
    color: #0F172A !important;
}
</style>

{{-- Page Header --}}
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold mb-1 header-title">
            <i class="bx bx-calendar-check text-info me-2"></i>All Scheduled Trip Orders
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Overview of customer cargo bookings placed on scheduled trips with 8-step specs, status lifecycle and invoice tracking.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('add.scheduled.trip.order') }}" class="btn btn-info rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2 text-dark shadow-sm">
            <i class="bx bx-plus-circle fs-5"></i>
            <span>Add Trip Order</span>
        </a>
        <a href="{{ route('all.scheduled.trips') }}" class="btn btn-outline-info rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-calendar fs-5"></i>
            <span>All Scheduled Trips</span>
        </a>
    </div>
</div>

{{-- ═════════════════════════════════════════════════════════════
     KPI STATS CARDS
═════════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon-wrap total"><i class="bx bx-calendar-event"></i></div>
            <div>
                <div class="kpi-label">Total Orders</div>
                <div class="kpi-value">{{ $stats['total'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon-wrap new"><i class="bx bx-bell"></i></div>
            <div>
                <div class="kpi-label">New Orders</div>
                <div class="kpi-value text-warning">{{ $stats['new'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon-wrap" style="background:linear-gradient(135deg,#3B82F6,#60A5FA);"><i class="bx bx-check-circle"></i></div>
            <div>
                <div class="kpi-label">Confirmed</div>
                <div class="kpi-value text-info">{{ $stats['confirmed'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon-wrap transit"><i class="bx bx-navigation"></i></div>
            <div>
                <div class="kpi-label">In Transit</div>
                <div class="kpi-value text-primary">{{ $stats['in_transit'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon-wrap delivered"><i class="bx bx-badge-check"></i></div>
            <div>
                <div class="kpi-label">Delivered</div>
                <div class="kpi-value text-success">{{ $stats['delivered'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon-wrap revenue"><i class="bx bx-dollar-circle"></i></div>
            <div>
                <div class="kpi-label">Total Revenue</div>
                <div class="kpi-value text-purple" style="font-size:1.3rem;">KWD {{ number_format($stats['total_sales'], 1) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ═════════════════════════════════════════════════════════════
     FILTER & SEARCH BAR
═════════════════════════════════════════════════════════════ --}}
<div class="filter-card">
    <form method="GET" action="{{ route('all.scheduled.trip.orders') }}" id="filterForm">
        <div class="row g-3 align-items-end">
            {{-- Search --}}
            <div class="col-12 col-md-3">
                <label class="form-label text-muted small fw-bold mb-1">Search Orders</label>
                <input type="text" name="search" class="form-control" placeholder="Order #, Customer, Cargo..." value="{{ request('search') }}">
            </div>

            {{-- Status --}}
            <div class="col-6 col-md-2">
                <label class="form-label text-muted small fw-bold mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="new" {{ request('status')==='new' ? 'selected' : '' }}>New</option>
                    <option value="confirmed" {{ request('status')==='confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="loaded" {{ request('status')==='loaded' ? 'selected' : '' }}>Loaded</option>
                    <option value="in_transit" {{ request('status')==='in_transit' ? 'selected' : '' }}>In Transit</option>
                    <option value="delivered" {{ request('status')==='delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="canceled" {{ request('status')==='canceled' ? 'selected' : '' }}>Canceled</option>
                </select>
            </div>

            {{-- Trip --}}
            <div class="col-6 col-md-3">
                <label class="form-label text-muted small fw-bold mb-1">Filter by Trip</label>
                <select name="trip_id" class="form-select">
                    <option value="">All Scheduled Trips</option>
                    @foreach($trips as $tr)
                        <option value="{{ $tr->id }}" {{ request('trip_id') == $tr->id ? 'selected' : '' }}>
                            Trip #{{ $tr->id }}: {{ $tr->route->originCity->name_en ?? '' }} ➔ {{ $tr->route->destinationCity->name_en ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Customer --}}
            <div class="col-6 col-md-2">
                <label class="form-label text-muted small fw-bold mb-1">Customer</label>
                <select name="customer_id" class="form-select">
                    <option value="">All Customers</option>
                    @foreach($customers as $cust)
                        <option value="{{ $cust->id }}" {{ request('customer_id') == $cust->id ? 'selected' : '' }}>
                            {{ $cust->fname }} {{ $cust->lname }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Buttons --}}
            <div class="col-6 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-info w-100 rounded-3 text-dark fw-bold">
                    <i class="bx bx-filter-alt me-1"></i>Filter
                </button>
                <a href="{{ route('all.scheduled.trip.orders') }}" class="btn btn-outline-secondary rounded-3" title="Reset Filters">
                    <i class="bx bx-refresh fs-5"></i>
                </a>
            </div>
        </div>
    </form>
</div>

{{-- ═════════════════════════════════════════════════════════════
     DATA TABLE
═════════════════════════════════════════════════════════════ --}}
<div class="sto-table-card">
    <div class="table-responsive">
        <table class="table sto-table mb-0">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Linked Scheduled Trip</th>
                    <th>Customer</th>
                    <th>Cargo Details</th>
                    <th>Truck & Units</th>
                    <th>Total Price</th>
                    <th>Order Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $o)
                <tr>
                    {{-- Order ID --}}
                    <td>
                        <span class="badge bg-dark border border-info border-opacity-50 text-info fw-bold py-1 px-2" style="font-size:0.85rem;">
                            {{ $o->order_number ?: ('STO-#' . $o->id) }}
                        </span>
                        <div class="text-muted small mt-1" style="font-size:0.75rem;">
                            {{ $o->created_at->format('Y-m-d h:i A') }}
                        </div>
                    </td>

                    {{-- Linked Trip & Route --}}
                    <td>
                        @if($o->scheduledTrip)
                            <div class="fw-bold text-white">
                                <a href="javascript:;" onclick="openOrderDetails({{ $o->id }})" class="text-info text-decoration-none">
                                    Trip #{{ $o->scheduledTrip->id }}
                                </a>
                            </div>
                            <div class="text-muted" style="font-size:0.8rem;">
                                {{ $o->scheduledTrip->route->originCity->name_en ?? 'Origin' }}
                                <i class="bx bx-right-arrow-alt text-info"></i>
                                {{ $o->scheduledTrip->route->destinationCity->name_en ?? 'Dest' }}
                            </div>
                            <div class="text-slate-400 small">
                                <i class="bx bx-calendar me-1"></i>{{ $o->scheduledTrip->trip_date }}
                            </div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    {{-- Customer --}}
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar" style="width:34px;height:34px;border-radius:50%;background:rgba(6,182,212,0.15);color:#38BDF8;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.85rem;">
                                {{ strtoupper(substr($o->customer->fname ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-bold text-white">{{ $o->customer->fname ?? '—' }} {{ $o->customer->lname ?? '' }}</div>
                                <div class="text-muted small">{{ $o->customer->phone ?? '—' }}</div>
                            </div>
                        </div>
                    </td>

                    {{-- Cargo --}}
                    <td>
                        <div class="fw-bold text-slate-200 text-truncate" style="max-width:180px;">{{ $o->shipment_name ?: 'Cargo' }}</div>
                        <div class="text-muted small">
                            <span class="badge bg-secondary bg-opacity-25 text-slate-300 me-1">{{ $o->packages_count }} Pcs</span>
                            @if($o->weight) {{ $o->weight }} kg @endif
                        </div>
                        <div class="mt-1 d-flex flex-wrap gap-1">
                            @if($o->customs_clearance)
                            <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25" style="font-size:0.68rem; color:#34D399 !important;">
                                <i class="bx bx-check-shield me-1"></i>Customs: Yes
                            </span>
                            @endif
                            @if(!empty($o->documents) && is_array($o->documents) && count($o->documents) > 0)
                            <span class="badge bg-primary bg-opacity-25 text-cyan border border-info border-opacity-25" style="font-size:0.68rem; color: #38BDF8 !important;">
                                <i class="bx bx-paperclip me-1"></i>{{ count($o->documents) }} Docs
                            </span>
                            @endif
                        </div>
                    </td>

                    {{-- Truck & Capacity Units --}}
                    <td>
                        <div class="fw-bold text-white">{{ $o->truckType->name_en ?? 'Truck' }}</div>
                        <div>
                            <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-25">
                                {{ $o->booked_capacity }} {{ $o->booked_capacity > 1 ? 'Units' : 'Unit' }}
                            </span>
                        </div>
                    </td>

                    {{-- Price --}}
                    <td>
                        <div class="fw-bold text-warning" style="font-size:0.95rem;">
                            KWD {{ number_format($o->total_price, 2) }}
                        </div>
                        @php
                            $isPaid = ($o->payment_status === 'paid') || ($o->invoice && strtolower($o->invoice->status) === 'paid');
                        @endphp
                        @if($o->invoice)
                            <a href="{{ route('all.invoices', ['search' => $o->invoice->invoice_number]) }}" class="badge {{ $isPaid ? 'bg-success bg-opacity-25 text-success border border-success' : 'bg-danger bg-opacity-25 text-danger border border-danger' }} border-opacity-25 text-decoration-none mt-1" style="{{ $isPaid ? 'color:#10B981 !important;' : 'color:#EF4444 !important;' }}">
                                <i class="bx {{ $isPaid ? 'bx-check-circle' : 'bx-receipt' }} me-1"></i>{{ $isPaid ? 'paid' : 'unpaid' }}
                            </a>
                        @else
                            <span class="badge {{ $isPaid ? 'bg-success bg-opacity-25 text-success border border-success' : 'bg-danger bg-opacity-25 text-danger border border-danger' }} border-opacity-25 mt-1" style="{{ $isPaid ? 'color:#10B981 !important;' : 'color:#EF4444 !important;' }}">
                                <i class="bx {{ $isPaid ? 'bx-check-circle' : 'bx-time-five' }} me-1"></i>{{ $isPaid ? 'paid' : 'unpaid' }}
                            </span>
                        @endif
                    </td>

                    {{-- Status Dropdown --}}
                    <td>
                        <select class="form-select status-select-sm status-{{ $o->status }}" onchange="updateOrderStatus({{ $o->id }}, this.value)">
                            <option value="new" {{ $o->status==='new' ? 'selected' : '' }}>New</option>
                            <option value="confirmed" {{ $o->status==='confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="loaded" {{ $o->status==='loaded' ? 'selected' : '' }}>Loaded</option>
                            <option value="in_transit" {{ $o->status==='in_transit' ? 'selected' : '' }}>In Transit</option>
                            <option value="delivered" {{ $o->status==='delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="canceled" {{ $o->status==='canceled' ? 'selected' : '' }}>Canceled</option>
                        </select>
                    </td>

                    {{-- Actions --}}
                    <td class="text-end">
                        <div class="d-inline-flex gap-2">
                            <button type="button" class="btn-action" onclick="openOrderDetails({{ $o->id }})" title="View Full Specs">
                                <i class="bx bx-show fs-5"></i>
                            </button>
                            <a href="{{ route('delete.scheduled.trip.order', $o->id) }}" class="btn-action btn-del confirm-delete" title="Delete Order">
                                <i class="bx bx-trash fs-5"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bx bx-calendar-x fs-1 text-slate-500 mb-2 d-block"></i>
                        No scheduled trip orders found matching your criteria.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ═════════════════════════════════════════════════════════════
     ORDER DETAILS MODAL DRAWER
═════════════════════════════════════════════════════════════ --}}
<div class="modal fade modal-sto-detail" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold text-white mb-0" id="modalTitle">Scheduled Trip Order Details</h5>
                    <small class="text-muted" id="modalSub">Full specifications and route breakdown</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <div class="text-center py-5" id="modalLoader">
                    <div class="spinner-border text-info" role="status"></div>
                    <div class="text-muted mt-2">Loading order details...</div>
                </div>
                <div id="modalContent" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-3 px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Document Image Lightbox Preview Modal --}}
<div class="modal fade" id="docImagePreviewModal" tabindex="-1" aria-hidden="true" style="z-index: 1065;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background:#0F172A;border:1px solid rgba(255,255,255,0.12);border-radius:16px;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);">
            <div class="modal-header py-3 px-4" style="background:rgba(255,255,255,0.03);border-bottom:1px solid rgba(255,255,255,0.08);">
                <div class="d-flex align-items-center gap-2">
                    <i class="bx bx-image text-info fs-4"></i>
                    <h6 class="modal-title fw-bold text-white mb-0" id="previewDocTitle">Document Preview</h6>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 text-center" style="background:#090D16;">
                <img id="previewDocImg" src="" alt="Document Preview" class="img-fluid rounded-3" style="max-height: 70vh; object-fit: contain; box-shadow: 0 10px 30px rgba(0,0,0,0.4);">
            </div>
            <div class="modal-footer py-2 px-4 d-flex justify-content-between" style="background:rgba(255,255,255,0.02);border-top:1px solid rgba(255,255,255,0.08);">
                <a id="previewDocDownloadBtn" href="#" class="btn btn-sm btn-info rounded-pill px-3 text-dark" download>
                    <i class="bx bx-download me-1"></i>Download
                </a>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // ── Update Order Status via AJAX ──
    function updateOrderStatus(id, newStatus) {
        $.ajax({
            url: '{{ route("scheduled.trip.order.status.ajax") }}',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                order_id: id,
                status: newStatus
            },
            success: function(res) {
                if (res.status === 'success') {
                    if (typeof toastr !== 'undefined') toastr.success(res.message);
                }
            },
            error: function() {
                if (typeof toastr !== 'undefined') toastr.error('Failed to update order status.');
            }
        });
    }

    // ── Open Order Details Modal via AJAX ──
    function openOrderDetails(id) {
        var modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
        $('#modalLoader').show();
        $('#modalContent').hide().empty();
        modal.show();

        $.ajax({
            url: '{{ route("get.scheduled.trip.order.details.ajax", ":id") }}'.replace(':id', id),
            type: 'GET',
            success: function(res) {
                $('#modalLoader').hide();
                if (res.status === 'success') {
                    renderOrderModal(res.order);
                    $('#modalContent').show();
                } else {
                    $('#modalContent').html('<div class="alert alert-danger">Order details not found.</div>').show();
                }
            },
            error: function() {
                $('#modalLoader').hide();
                $('#modalContent').html('<div class="alert alert-danger">Error loading order specifications.</div>').show();
            }
        });
    }

    function renderOrderModal(o) {
        $('#modalTitle').text('Order #' + (o.order_number || o.id));
        $('#modalSub').text('Created on ' + (o.created_at ? o.created_at.substring(0, 10) : '—') + ' | Status: ' + o.status.toUpperCase());

        var trip = o.scheduled_trip || {};
        var route = trip.route || {};
        var cust = o.customer || {};

        var html = '';

        // Section 1: Trip Specs
        html += '<div class="detail-sec-title"><i class="bx bx-calendar-event me-1"></i>Linked Scheduled Trip</div>';
        html += '<div class="detail-grid-box">';
        html += '<div class="detail-item"><div class="lbl">Trip ID</div><div class="val text-info">#' + (trip.id || '—') + '</div></div>';
        html += '<div class="detail-item"><div class="lbl">Route</div><div class="val">' + (route.origin_city ? route.origin_city.name_en : 'Origin') + ' ➔ ' + (route.destination_city ? route.destination_city.name_en : 'Dest') + '</div></div>';
        html += '<div class="detail-item"><div class="lbl">Trip Date</div><div class="val">' + (trip.trip_date || '—') + (trip.trip_time ? ' (' + trip.trip_time + ')' : '') + '</div></div>';
        html += '<div class="detail-item"><div class="lbl">Assigned Driver</div><div class="val">' + (trip.driver ? (trip.driver.fname + ' ' + (trip.driver.lname||'')) : 'Unassigned') + '</div></div>';
        html += '</div>';

        // Section 2: Customer Details
        html += '<div class="detail-sec-title"><i class="bx bx-user me-1"></i>Customer Details</div>';
        html += '<div class="detail-grid-box">';
        html += '<div class="detail-item"><div class="lbl">Customer</div><div class="val">' + (cust.fname || '—') + ' ' + (cust.lname || '') + '</div></div>';
        html += '<div class="detail-item"><div class="lbl">Email</div><div class="val">' + (cust.email || '—') + '</div></div>';
        html += '<div class="detail-item"><div class="lbl">Phone</div><div class="val">' + (cust.phone || '—') + '</div></div>';
        html += '</div>';

        // Section 3: Cargo & Truck
        html += '<div class="detail-sec-title"><i class="bx bx-package me-1"></i>Cargo & Truck Specs</div>';
        html += '<div class="detail-grid-box">';
        html += '<div class="detail-item"><div class="lbl">Cargo Label</div><div class="val text-white">' + (o.shipment_name || '—') + '</div></div>';
        html += '<div class="detail-item"><div class="lbl">Packages Count</div><div class="val">' + (o.packages_count || 1) + ' Pcs</div></div>';
        html += '<div class="detail-item"><div class="lbl">Total Weight</div><div class="val">' + (o.weight ? (o.weight + ' kg') : '—') + '</div></div>';
        html += '<div class="detail-item"><div class="lbl">Dimensions (LxWxH)</div><div class="val">' + (o.length||0) + ' x ' + (o.width||0) + ' x ' + (o.height||0) + ' cm</div></div>';
        html += '<div class="detail-item"><div class="lbl">Truck Type</div><div class="val text-info">' + (o.truck_type ? o.truck_type.name_en : '—') + '</div></div>';
        if (o.number_of_trucks) {
            html += '<div class="detail-item"><div class="lbl">Number of Trucks</div><div class="val text-info fw-bold">' + o.number_of_trucks + (o.number_of_trucks > 1 ? ' Trucks' : ' Truck') + '</div></div>';
        }
        if (o.special_equipment) {
            var eqArr = [];
            try {
                eqArr = (typeof o.special_equipment === 'string' && o.special_equipment.trim().startsWith('[')) ? JSON.parse(o.special_equipment) : [o.special_equipment];
            } catch(e) { eqArr = [o.special_equipment]; }
            html += '<div class="detail-item" style="grid-column: span 2;"><div class="lbl">Special Equipment</div><div class="val text-warning">' + (Array.isArray(eqArr) ? eqArr.join(', ') : eqArr) + '</div></div>';
        }
        if (o.truck_notes) {
            html += '<div class="detail-item" style="grid-column: span 2;"><div class="lbl">Driver & Truck Notes</div><div class="val text-slate-300">' + o.truck_notes + '</div></div>';
        }
        if (o.hs_code) {
            html += '<div class="detail-item"><div class="lbl">HS Tariff Code</div><div class="val text-warning">' + o.hs_code + '</div></div>';
        }
        if (o.customs_clearance !== undefined && o.customs_clearance !== null) {
            html += '<div class="detail-item"><div class="lbl">Customs Clearance</div><div class="val ' + (o.customs_clearance ? 'text-success fw-bold' : 'text-slate-400') + '">' + (o.customs_clearance ? '<i class="bx bx-check-shield me-1"></i>Yes (Included)' : '<i class="bx bx-x-circle me-1"></i>No (Not Included)') + '</div></div>';
        }
        html += '</div>';

        // Section 4: Locations & Contacts
        html += '<div class="detail-sec-title"><i class="bx bx-map-pin me-1"></i>Locations & Contacts</div>';
        html += '<div class="detail-grid-box">';

        var pickupLoc = (o.pickup_city ? o.pickup_city.name_en : '');
        if (o.pickup_area) pickupLoc += (pickupLoc ? ', ' : '') + o.pickup_area;
        if (o.pickup_address) pickupLoc += (pickupLoc ? ' — ' : '') + o.pickup_address;
        html += '<div class="detail-item"><div class="lbl">Pickup (Origin)</div><div class="val text-info">' + (pickupLoc || '—') + '</div></div>';

        var dropoffLoc = (o.dropoff_city ? o.dropoff_city.name_en : '');
        if (o.dropoff_area) dropoffLoc += (dropoffLoc ? ', ' : '') + o.dropoff_area;
        if (o.dropoff_address) dropoffLoc += (dropoffLoc ? ' — ' : '') + o.dropoff_address;
        html += '<div class="detail-item"><div class="lbl">Delivery (Dest)</div><div class="val text-success">' + (dropoffLoc || '—') + '</div></div>';

        if (o.loading_date || o.loading_time || o.loading_time_slot) {
            var loadSched = (o.loading_date || '') + (o.loading_time ? ' ' + o.loading_time : '') + (o.loading_time_slot ? ' (' + o.loading_time_slot + ')' : '');
            html += '<div class="detail-item"><div class="lbl">Loading Schedule</div><div class="val text-info fw-semibold">' + loadSched + '</div></div>';
        }
        if (o.receiving_date || o.receiving_time || o.receiving_time_slot) {
            var recvSched = (o.receiving_date || '') + (o.receiving_time ? ' ' + o.receiving_time : '') + (o.receiving_time_slot ? ' (' + o.receiving_time_slot + ')' : '');
            html += '<div class="detail-item"><div class="lbl">Receiving Schedule</div><div class="val text-success fw-semibold">' + recvSched + '</div></div>';
        }
        if (o.pickup_instructions) {
            html += '<div class="detail-item" style="grid-column: span 2;"><div class="lbl">Pickup Instructions</div><div class="val text-slate-300">' + o.pickup_instructions + '</div></div>';
        }
        if (o.delivery_instructions) {
            html += '<div class="detail-item" style="grid-column: span 2;"><div class="lbl">Delivery Instructions</div><div class="val text-slate-300">' + o.delivery_instructions + '</div></div>';
        }

        html += '<div class="detail-item"><div class="lbl">Loading Contact</div><div class="val">' + (o.loading_contact || '—') + '</div></div>';
        html += '<div class="detail-item"><div class="lbl">Arrival Contact</div><div class="val">' + (o.arrival_contact || '—') + '</div></div>';
        html += '</div>';

        // Section 5: Attached Documents
        var docs = [];
        if (Array.isArray(o.documents)) {
            docs = o.documents;
        } else if (typeof o.documents === 'string' && o.documents.trim().startsWith('[')) {
            try { docs = JSON.parse(o.documents); } catch(e) { docs = []; }
        }

        html += '<div class="detail-sec-title"><i class="bx bx-file me-1"></i>Attached Documents (' + docs.length + ')</div>';
        if (docs.length > 0) {
            html += '<div class="row g-2 mb-3">';
            docs.forEach(function(doc, idx) {
                var sizeStr = '';
                if (doc.file_size) {
                    sizeStr = (typeof doc.file_size === 'number' && doc.file_size > 1048576) 
                        ? (doc.file_size / 1048576).toFixed(1) + ' MB' 
                        : (typeof doc.file_size === 'number' ? Math.round(doc.file_size / 1024) + ' KB' : doc.file_size);
                }

                var fileExt = (doc.file_type || (doc.file_name ? doc.file_name.split('.').pop() : '') || '').toLowerCase();
                var isPdf = fileExt === 'pdf' || (doc.mime_type && doc.mime_type.includes('pdf'));
                var isImg = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'].includes(fileExt) || (doc.mime_type && doc.mime_type.includes('image'));
                var iconCls = isPdf ? 'bxs-file-pdf text-danger' : (isImg ? 'bxs-file-image text-success' : 'bxs-file-doc text-info');
                
                var viewUrl = doc.view_url || ('/' + (doc.file_path || '').replace(/^\//, ''));
                var downloadUrl = doc.download_url || viewUrl;
                var docTitle = doc.title || doc.file_name || ('Document ' + (idx + 1));
                var safeTitle = docTitle.replace(/'/g, "\\'");

                html += '<div class="col-12 col-md-6">';
                html += '  <div class="doc-card-item p-3 d-flex align-items-center justify-content-between" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:12px;transition:all 0.2s;">';
                html += '    <div class="d-flex align-items-center gap-2 overflow-hidden me-2" style="' + (isImg ? 'cursor:pointer;' : '') + '" ' + (isImg ? 'onclick="previewDocImage(\'' + viewUrl + '\', \'' + safeTitle + '\')"' : '') + '>';
                
                if (isImg) {
                    html += '      <div style="width:42px;height:42px;border-radius:10px;background:rgba(255,255,255,0.06);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;border:1px solid rgba(255,255,255,0.1);">';
                    html += '        <img src="' + viewUrl + '" alt="' + safeTitle + '" style="width:100%;height:100%;object-fit:cover;" onerror="this.onerror=null;this.parentElement.innerHTML=\'<i class=\\\'bx bxs-file-image text-success\\\'></i>\';">';
                    html += '      </div>';
                } else {
                    html += '      <div style="width:42px;height:42px;border-radius:10px;background:rgba(255,255,255,0.06);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;">';
                    html += '        <i class="bx ' + iconCls + '"></i>';
                    html += '      </div>';
                }

                html += '      <div class="overflow-hidden">';
                html += '        <div class="doc-card-title fw-semibold text-truncate" style="font-size:0.86rem;color:#F8FAFC;" title="' + docTitle + '">' + docTitle + '</div>';
                html += '        <div class="text-muted text-truncate" style="font-size:0.74rem;">' + (doc.file_name || '') + (sizeStr ? ' • ' + sizeStr : '') + '</div>';
                html += '      </div>';
                html += '    </div>';
                
                html += '    <div class="d-flex align-items-center gap-1 flex-shrink-0">';
                if (doc.is_required) {
                    html += '      <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-25 me-1" style="font-size:0.65rem;">Required</span>';
                }
                if (isImg) {
                    html += '      <button type="button" onclick="previewDocImage(\'' + viewUrl + '\', \'' + safeTitle + '\')" class="btn btn-sm btn-outline-success rounded-pill px-2 py-1" title="Quick Preview">';
                    html += '        <i class="bx bx-show"></i>';
                    html += '      </button>';
                }
                html += '      <a href="' + viewUrl + '" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-2 py-1" title="Open in New Tab">';
                html += '        <i class="bx bx-link-external"></i>';
                html += '      </a>';
                html += '      <a href="' + downloadUrl + '" class="btn btn-sm btn-info rounded-pill px-2 py-1 text-dark" title="Download">';
                html += '        <i class="bx bx-download"></i>';
                html += '      </a>';
                html += '    </div>';
                html += '  </div>';
                html += '</div>';
            });
            html += '</div>';
        } else {
            html += '<div class="p-3 text-center text-muted mb-3" style="background:rgba(255,255,255,0.02);border:1px dashed rgba(255,255,255,0.08);border-radius:12px;font-size:0.84rem;">';
            html += '<i class="bx bx-file-blank fs-4 d-block mb-1 text-secondary"></i>No documents attached to this order.';
            html += '</div>';
        }

        // Section 6: Financials
        html += '<div class="detail-sec-title"><i class="bx bx-dollar-circle me-1"></i>Pricing & Invoice Breakdown</div>';
        html += '<div class="detail-grid-box" style="background:rgba(16,185,129,0.06);border-color:rgba(16,185,129,0.2);">';
        html += '<div class="detail-item"><div class="lbl">Trip Unit Price</div><div class="val text-warning">KWD ' + parseFloat(o.trip_price || 0).toFixed(2) + '</div></div>';
        html += '<div class="detail-item"><div class="lbl">Booked Units</div><div class="val">' + (o.booked_capacity || 1) + ' Unit(s)</div></div>';
        html += '<div class="detail-item"><div class="lbl">Subtotal</div><div class="val">KWD ' + parseFloat(o.total_price || 0).toFixed(2) + '</div></div>';
        if (o.invoice) {
            html += '<div class="detail-item"><div class="lbl">Invoice Number</div><div class="val text-info">' + o.invoice.invoice_number + '</div></div>';
            html += '<div class="detail-item"><div class="lbl">Invoice Total (inc. VAT)</div><div class="val text-success fw-bold">KWD ' + parseFloat(o.invoice.total_amount).toFixed(2) + '</div></div>';
            var payClass = (o.payment_status === 'paid' || (o.invoice && o.invoice.status === 'paid')) ? 'text-success fw-bold' : 'text-danger fw-bold';
            var payText = (o.payment_status === 'paid' || (o.invoice && o.invoice.status === 'paid')) ? 'PAID' + (o.payment_method === 'credit' ? ' (CREDIT)' : '') : 'UNPAID';
            html += '<div class="detail-item"><div class="lbl">Payment Status</div><div class="val ' + payClass + '">' + payText + '</div></div>';
        }
        html += '</div>';

        $('#modalContent').html(html);
    }

    function previewDocImage(url, title) {
        $('#previewDocTitle').text(title || 'Document Preview');
        $('#previewDocImg').attr('src', url);
        $('#previewDocDownloadBtn').attr('href', url);
        var previewModal = new bootstrap.Modal(document.getElementById('docImagePreviewModal'));
        previewModal.show();
    }
</script>

@endsection
