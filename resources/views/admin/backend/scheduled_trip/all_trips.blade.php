@extends('admin.master_admin')
@section('admin')

{{-- ========================
     ALL SCHEDULED TRIPS
======================== --}}

<style>
:root {
    --tp-cyan:       #06B6D4;
    --tp-cyan-light: #38BDF8;
    --tp-navy:       #0F172A;
    --tp-border:     rgba(255,255,255,0.08);
    --tp-success:    #10B981;
    --tp-warning:    #F59E0B;
    --tp-danger:     #F43F5E;
    --tp-purple:     #8B5CF6;
}

/* ─── KPI Cards ─── */
.kpi-card {
    background: linear-gradient(135deg, rgba(30,41,59,0.9) 0%, rgba(15,23,42,0.95) 100%);
    border: 1px solid var(--tp-border);
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
.kpi-icon-wrap.total      { background: linear-gradient(135deg, #06B6D4, #38BDF8); box-shadow: 0 8px 20px rgba(6,182,212,0.3); }
.kpi-icon-wrap.published  { background: linear-gradient(135deg, #10B981, #34D399); box-shadow: 0 8px 20px rgba(16,185,129,0.3); }
.kpi-icon-wrap.boarding   { background: linear-gradient(135deg, #F59E0B, #FCD34D); box-shadow: 0 8px 20px rgba(245,158,11,0.3); color:#0F172A; }
.kpi-icon-wrap.intransit  { background: linear-gradient(135deg, #8B5CF6, #A78BFA); box-shadow: 0 8px 20px rgba(139,92,246,0.3); }
.kpi-icon-wrap.completed  { background: linear-gradient(135deg, #3B82F6, #60A5FA); box-shadow: 0 8px 20px rgba(59,130,246,0.3); }
.kpi-icon-wrap.canceled   { background: linear-gradient(135deg, #F43F5E, #FB7185); box-shadow: 0 8px 20px rgba(244,63,94,0.3); }

.kpi-label { font-size: 0.8rem; font-weight: 700; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.5px; }
.kpi-value { font-size: 1.6rem; font-weight: 800; color: #F8FAFC; margin-top: 2px; }

/* ─── Filter Bar ─── */
.filter-card {
    background: rgba(30,41,59,0.7);
    border: 1px solid var(--tp-border);
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
    border-color: var(--tp-cyan) !important;
    box-shadow: 0 0 0 3px rgba(6,182,212,0.15) !important;
}
.filter-card .form-select option { background: #1E293B; color: #F8FAFC; }

/* ─── Data Table ─── */
.tp-table-card {
    background: linear-gradient(135deg, rgba(30,41,59,0.95) 0%, rgba(15,23,42,0.98) 100%);
    border: 1px solid var(--tp-border);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0,0,0,0.35);
}
.tp-table {
    width: 100%;
    margin-bottom: 0;
    color: #CBD5E1;
    border-collapse: separate;
    border-spacing: 0;
}
.tp-table th {
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
.tp-table td {
    padding: 16px 20px;
    vertical-align: middle;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    font-size: 0.88rem;
}
.tp-table tbody tr { transition: background 0.2s; }
.tp-table tbody tr:hover { background: rgba(6,182,212,0.05); }

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
.status-select-sm.status-published { border-color: rgba(16,185,129,0.5) !important; color: #34D399 !important; }
.status-select-sm.status-boarding  { border-color: rgba(245,158,11,0.5) !important; color: #F59E0B !important; }
.status-select-sm.status-in_transit{ border-color: rgba(139,92,246,0.5) !important; color: #A78BFA !important; }
.status-select-sm.status-completed { border-color: rgba(59,130,246,0.5) !important; color: #60A5FA !important; }
.status-select-sm.status-canceled  { border-color: rgba(244,63,94,0.5) !important;  color: #F43F5E !important; }

.btn-action {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: inline-flex; align-items: center; justify-content: center;
    border: 1px solid var(--tp-border);
    background: rgba(255,255,255,0.04);
    color: #CBD5E1;
    transition: all 0.2s;
}
.btn-action:hover {
    background: rgba(6,182,212,0.15);
    border-color: var(--tp-cyan);
    color: #38BDF8;
    transform: translateY(-2px);
}
.btn-action.btn-del:hover {
    background: rgba(244,63,94,0.15);
    border-color: var(--tp-danger);
    color: #F43F5E;
}

/* ─── Modal Drawer Styling ─── */
.modal-tp-detail .modal-dialog { max-width: 850px; }
.modal-tp-detail .modal-content {
    background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0,0,0,0.6);
    color: #F8FAFC;
}
.modal-tp-detail .modal-header {
    background: linear-gradient(135deg, rgba(6,182,212,0.15) 0%, rgba(56,189,248,0.08) 100%);
    border-bottom: 1px solid rgba(6,182,212,0.2);
    padding: 24px 32px;
}
.modal-tp-detail .modal-body { padding: 32px; max-height: 80vh; overflow-y: auto; }
.modal-tp-detail .modal-footer { border-top: 1px solid rgba(255,255,255,0.08); padding: 20px 32px; background: rgba(0,0,0,0.15); }

.detail-sec-title {
    font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px;
    color: var(--tp-cyan-light); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;
}
.detail-sec-title::after { content: ''; flex: 1; height: 1px; background: linear-gradient(90deg, rgba(6,182,212,0.4), transparent); }
.detail-card-grid { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 14px; padding: 18px 20px; margin-bottom: 20px; }
.detail-row { display: flex; gap: 12px; margin-bottom: 10px; align-items: flex-start; }
.detail-row:last-child { margin-bottom: 0; }
.detail-row .d-label { font-size: 0.78rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.4px; min-width: 140px; flex-shrink: 0; }
.detail-row .d-value { font-size: 0.88rem; color: #E2E8F0; font-weight: 500; }

/* ═════════════════════════════════════════════════════════════
   LIGHT MODE OVERRIDES FOR ALL SCHEDULED TRIPS PAGE
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
html.light-theme .tp-table-card {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 15px 45px rgba(0,0,0,0.05) !important;
}
html.light-theme .tp-table {
    color: #0F172A !important;
}
html.light-theme .tp-table th {
    background: #F1F5F9 !important;
    color: #334155 !important;
    border-bottom-color: #E2E8F0 !important;
}
html.light-theme .tp-table td {
    border-bottom-color: #F1F5F9 !important;
    color: #0F172A !important;
}
html.light-theme .tp-table tbody tr:hover {
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
html.light-theme .status-select-sm.status-published {
    background: #D1FAE5 !important;
    color: #059669 !important;
    border-color: #10B981 !important;
}
html.light-theme .status-select-sm.status-boarding {
    background: #FEF3C7 !important;
    color: #D97706 !important;
    border-color: #F59E0B !important;
}
html.light-theme .status-select-sm.status-in_transit {
    background: #EDE9FE !important;
    color: #6D28D9 !important;
    border-color: #8B5CF6 !important;
}
html.light-theme .status-select-sm.status-completed {
    background: #DBEAFE !important;
    color: #1D4ED8 !important;
    border-color: #3B82F6 !important;
}
html.light-theme .status-select-sm.status-canceled {
    background: #FFE4E6 !important;
    color: #E11D48 !important;
    border-color: #F43F5E !important;
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
html.light-theme .modal-tp-detail .modal-content {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    color: #0F172A !important;
    box-shadow: 0 25px 60px rgba(0,0,0,0.15) !important;
}
html.light-theme .modal-tp-detail .modal-header {
    background: linear-gradient(135deg, rgba(2,132,199,0.08) 0%, rgba(3,105,161,0.04) 100%) !important;
    border-bottom-color: #E2E8F0 !important;
}
html.light-theme .modal-tp-detail .modal-header h5,
html.light-theme .modal-tp-detail .modal-header .modal-title {
    color: #0F172A !important;
}
html.light-theme .modal-tp-detail .modal-header .text-muted {
    color: #64748B !important;
}
html.light-theme .modal-tp-detail .btn-close-white {
    filter: invert(1) grayscale(100%) brightness(200%);
}
html.light-theme .modal-tp-detail .modal-footer {
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
            <i class="bx bx-calendar-event text-info me-2"></i>Scheduled Trips System
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Inspect fixed scheduled trips, capacity availability, departure dates, pricing, and statuses.
        </p>
    </div>
    <div>
        <a href="{{ route('add.scheduled.trip') }}" class="btn btn-info rounded-3 px-3 py-2 fw-bold d-inline-flex align-items-center gap-2" style="background:linear-gradient(135deg,#06B6D4,#38BDF8);border:none;color:#0F172A;">
            <i class="bx bx-calendar-plus fs-5"></i>
            <span>Add Scheduled Trip</span>
        </a>
    </div>
</div>

{{-- ─── KPI Stats Bar ─── --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon-wrap total"><i class="bx bx-calendar"></i></div>
            <div>
                <div class="kpi-label">Total Trips</div>
                <div class="kpi-value">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon-wrap published"><i class="bx bx-badge-check"></i></div>
            <div>
                <div class="kpi-label">Published</div>
                <div class="kpi-value">{{ number_format($stats['published']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon-wrap boarding"><i class="bx bx-time-five"></i></div>
            <div>
                <div class="kpi-label">Boarding</div>
                <div class="kpi-value">{{ number_format($stats['boarding']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon-wrap intransit"><i class="bx bx-navigation"></i></div>
            <div>
                <div class="kpi-label">In Transit</div>
                <div class="kpi-value">{{ number_format($stats['in_transit']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon-wrap completed"><i class="bx bx-check-double"></i></div>
            <div>
                <div class="kpi-label">Completed</div>
                <div class="kpi-value">{{ number_format($stats['completed']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon-wrap canceled"><i class="bx bx-block"></i></div>
            <div>
                <div class="kpi-label">Canceled</div>
                <div class="kpi-value">{{ number_format($stats['canceled']) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ─── Filter & Search Bar ─── --}}
<div class="filter-card">
    <form method="GET" action="{{ route('all.scheduled.trips') }}" id="filterForm">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-md-4">
                <div class="position-relative">
                    <input type="text" name="search" class="form-control ps-5" placeholder="Search city, date, driver, ID..." value="{{ request('search') }}">
                    <span class="position-absolute text-muted" style="left:14px;top:50%;transform:translateY(-50%);"><i class="bx bx-search fs-5"></i></span>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <select name="status" class="form-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Statuses</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="boarding" {{ request('status') === 'boarding' ? 'selected' : '' }}>Boarding</option>
                    <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="canceled" {{ request('status') === 'canceled' ? 'selected' : '' }}>Canceled</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="truck_type_id" class="form-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Truck Types</option>
                    @foreach($truckTypes as $tt)
                    <option value="{{ $tt->id }}" {{ request('truck_type_id') == $tt->id ? 'selected' : '' }}>{{ $tt->name_en }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <select name="driver_id" class="form-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Drivers</option>
                    @foreach($drivers as $d)
                    <option value="{{ $d->id }}" {{ request('driver_id') == $d->id ? 'selected' : '' }}>
                        {{ $d->fname }} {{ $d->lname }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-1 text-end">
                <a href="{{ route('all.scheduled.trips') }}" class="btn btn-outline-secondary w-100 rounded-3" title="Reset Filters"><i class="bx bx-refresh fs-5"></i></a>
            </div>
        </div>
    </form>
</div>

{{-- ─── Data Table ─── --}}
<div class="tp-table-card">
    <div class="table-responsive">
        <table class="table tp-table">
            <thead>
                <tr>
                    <th># ID</th>
                    <th>Fixed Route (Origin ➔ Dropoff)</th>
                    <th>Truck Specs</th>
                    <th>Driver & Date</th>
                    <th>Capacity</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($trips as $t)
                <tr>
                    {{-- ID --}}
                    <td class="fw-bold text-white">#{{ $t->id }}</td>

                    {{-- Route --}}
                    <td>
                        @if($t->route)
                        <div class="fw-bold text-white">
                            <i class="bx bx-map-pin text-info me-1"></i>{{ $t->route->originCity->name_en ?? '—' }}
                            <i class="bx bx-right-arrow-alt mx-1 text-muted"></i>
                            <i class="bx bx-navigation text-success me-1"></i>{{ $t->route->destinationCity->name_en ?? '—' }}
                        </div>
                        <div class="text-muted" style="font-size:0.78rem;">
                            {{ ucfirst($t->route->quote_type) }} Route
                        </div>
                        @else
                        <span class="text-muted">Route Removed</span>
                        @endif
                    </td>

                    {{-- Truck Specs --}}
                    <td>
                        <div class="fw-bold text-slate-200">
                            {{ $t->truckType->name_en ?? '—' }}
                            @if($t->truckSubType)
                            <span class="text-info" style="font-size:0.8rem;">({{ $t->truckSubType->name_en }})</span>
                            @endif
                        </div>
                        <div class="text-muted" style="font-size:0.78rem;">
                            Trucks: {{ $t->number_of_trucks ?? 1 }} | {{ $t->total_weight_ton ? $t->total_weight_ton . ' Tons' : 'No Weight' }}
                        </div>
                    </td>

                    {{-- Driver & Date --}}
                    <td>
                        <div class="fw-bold text-white">
                            <i class="bx bx-calendar me-1 text-info"></i>{{ $t->trip_date }} {{ $t->trip_time ? '('.date('h:i A', strtotime($t->trip_time)).')' : '' }}
                        </div>
                        <div class="text-muted" style="font-size:0.78rem;">
                            <i class="bx bx-user me-1"></i>{{ $t->driver ? ($t->driver->fname . ' ' . $t->driver->lname) : 'Unassigned' }}
                        </div>
                    </td>

                    {{-- Capacity --}}
                    <td>
                        <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-25">
                            {{ $t->available_capacity }} / {{ $t->total_capacity }} Available
                        </span>
                    </td>

                    {{-- Price --}}
                    <td>
                        <strong class="text-warning">KWD {{ number_format($t->price, 2) }}</strong>
                    </td>

                    {{-- Status Dropdown --}}
                    <td>
                        <select class="form-select status-select-sm status-{{ $t->status }}" onchange="updateTripStatus({{ $t->id }}, this.value)">
                            <option value="published" {{ $t->status==='published' ? 'selected' : '' }}>Published</option>
                            <option value="boarding" {{ $t->status==='boarding' ? 'selected' : '' }}>Boarding</option>
                            <option value="in_transit" {{ $t->status==='in_transit' ? 'selected' : '' }}>In Transit</option>
                            <option value="completed" {{ $t->status==='completed' ? 'selected' : '' }}>Completed</option>
                            <option value="canceled" {{ $t->status==='canceled' ? 'selected' : '' }}>Canceled</option>
                        </select>
                    </td>

                    {{-- Actions --}}
                    <td class="text-end">
                        <div class="d-inline-flex gap-2">
                            <button type="button" class="btn-action" onclick="openTripDetails({{ $t->id }})" title="View Full Specs">
                                <i class="bx bx-show fs-5"></i>
                            </button>
                            <a href="{{ route('edit.scheduled.trip', $t->id) }}" class="btn-action" title="Edit Trip">
                                <i class="bx bx-edit fs-5"></i>
                            </a>
                            <a href="{{ route('delete.scheduled.trip', $t->id) }}" class="btn-action btn-del confirm-delete" title="Delete Trip">
                                <i class="bx bx-trash fs-5"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bx bx-calendar-x fs-1 text-slate-500 mb-2 d-block"></i>
                        No scheduled trips found matching your criteria.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    // ── Update Trip Status via AJAX ──
    function updateTripStatus(id, newStatus) {
        $.ajax({
            url: '{{ route("scheduled.trip.status.ajax") }}',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                trip_id: id,
                status: newStatus
            },
            success: function(res) {
                if (res.status === 'success') {
                    if (typeof toastr !== 'undefined') toastr.success(res.message);
                }
            },
            error: function() {
                if (typeof toastr !== 'undefined') toastr.error('Failed to update trip status.');
            }
        });
    }

    // ── Open Trip Details Modal via AJAX ──
    function openTripDetails(id) {
        var modal = new bootstrap.Modal(document.getElementById('tripDetailsModal'));
        $('#modalLoader').show();
        $('#modalDataWrap').hide().empty();
        modal.show();

        $.ajax({
            url: '{{ route("get.scheduled.trip.details.ajax", ":id") }}'.replace(':id', id),
            type: 'GET',
            success: function(res) {
                $('#modalLoader').hide();
                if (res.status === 'success') {
                    renderTripModal(res.trip);
                    $('#modalDataWrap').show();
                } else {
                    $('#modalDataWrap').html('<div class="alert alert-danger">Failed to load details.</div>').show();
                }
            },
            error: function() {
                $('#modalLoader').hide();
                $('#modalDataWrap').html('<div class="alert alert-danger">Error fetching trip data.</div>').show();
            }
        });
    }

    function renderTripModal(t) {
        $('#modalTitle').text('Scheduled Trip #' + t.id);

        var html = '';

        // Section 1: Route & Location
        html += '<div class="detail-sec-title"><i class="bx bx-map me-1"></i>Fixed Route Specifications</div>';
        html += '<div class="detail-card-grid"><div class="row g-2">';
        html += dRow('Route Type', t.quote_type + ' Route');
        html += dRow('Origin Location', t.origin_city + ' (' + t.origin_country + ')');
        html += dRow('Destination Location', t.destination_city + ' (' + t.destination_country + ')');
        html += dRow('Estimated Distance', t.estimated_distance);
        html += '</div></div>';

        // Section 2: Truck & Cargo
        html += '<div class="detail-sec-title"><i class="bx bx-truck me-1"></i>Truck & Capacity Specifications</div>';
        html += '<div class="detail-card-grid"><div class="row g-2">';
        html += dRow('Truck Type', t.truck_type);
        html += dRow('Truck Sub-Type', t.truck_sub_type);
        html += dRow('Number of Trucks', t.number_of_trucks);
        html += dRow('Total Approx. Weight', t.total_weight_ton);
        html += dRow('Booking Capacity', t.available_capacity + ' / ' + t.total_capacity + ' Available');
        html += dRow('Trip Price', '<strong class="text-warning">KWD ' + t.price + '</strong>');
        html += '</div></div>';

        // Section 3: Driver & Timing
        html += '<div class="detail-sec-title"><i class="bx bx-time me-1"></i>Driver Assignment & Timing</div>';
        html += '<div class="detail-card-grid"><div class="row g-2">';
        html += dRow('Assigned Driver', t.driver_name + ' (' + t.driver_phone + ')');
        html += dRow('Departure Date', t.trip_date);
        html += dRow('Departure Time', t.trip_time);
        html += dRow('Trip Status', '<span class="badge bg-info text-dark fw-bold">' + t.status.toUpperCase() + '</span>');
        html += dRow('Created At', t.created_at);
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
            title: 'Delete Scheduled Trip?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#F43F5E',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Yes, Delete Trip!',
            background: '#1E293B',
            color: '#F8FAFC'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = link;
            }
        });
    });
</script>

{{-- FULL DETAILS MODAL DRAWER --}}
<div class="modal fade modal-tp-detail" id="tripDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-info bg-opacity-10 p-2 border border-info border-opacity-25 text-info">
                        <i class="bx bx-calendar-event fs-3"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="modalTitle">Scheduled Trip Details</h5>
                        <small class="text-muted">Full route, truck, and schedule specs</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-5" id="modalLoader">
                    <div class="spinner-border text-info" role="status"></div>
                    <div class="text-info mt-2">Loading trip specifications...</div>
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
