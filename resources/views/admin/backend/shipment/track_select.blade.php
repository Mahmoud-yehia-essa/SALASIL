@extends('admin.master_admin')
@section('admin')

{{-- =========================================
     SHIPMENT SELECTION PAGE FOR LIVE TRACKING
   ========================================= --}}

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
    background: linear-gradient(135deg, rgba(30,41,59,0.95) 0%, rgba(15,23,42,0.98) 100%);
    border: 1px solid var(--sh-border);
    border-radius: 16px;
    padding: 20px 24px;
    display: flex;
    align-items: center;
    gap: 18px;
    transition: all 0.3s cubic-bezier(0.34,1.56,0.64,1);
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
}
.kpi-card:hover {
    transform: translateY(-4px);
    border-color: rgba(6,182,212,0.4);
    box-shadow: 0 15px 35px rgba(6,182,212,0.2);
}
.kpi-icon-wrap {
    width: 54px; height: 54px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 26px; color: #fff;
    flex-shrink: 0;
}
.kpi-icon-wrap.total    { background: linear-gradient(135deg, #06B6D4, #38BDF8); box-shadow: 0 8px 20px rgba(6,182,212,0.35); }
.kpi-icon-wrap.active   { background: linear-gradient(135deg, #F59E0B, #FCD34D); box-shadow: 0 8px 20px rgba(245,158,11,0.35); color:#0F172A; }
.kpi-icon-wrap.delivered{ background: linear-gradient(135deg, #10B981, #34D399); box-shadow: 0 8px 20px rgba(16,185,129,0.35); }
.kpi-icon-wrap.pending  { background: linear-gradient(135deg, #8B5CF6, #A78BFA); box-shadow: 0 8px 20px rgba(139,92,246,0.35); }

.kpi-label { font-size: 0.82rem; font-weight: 700; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.5px; }
.kpi-value { font-size: 1.7rem; font-weight: 800; color: #F8FAFC; margin-top: 2px; }

/* ─── Filter Bar ─── */
.filter-card {
    background: rgba(30,41,59,0.75);
    border: 1px solid var(--sh-border);
    border-radius: 16px;
    padding: 20px 24px;
    margin-bottom: 24px;
    backdrop-filter: blur(12px);
}

/* ─── Table & Cards ─── */
.main-card {
    background: linear-gradient(135deg, rgba(30,41,59,0.95) 0%, rgba(15,23,42,0.98) 100%);
    border: 1px solid var(--sh-border);
    border-radius: 20px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.3);
    overflow: hidden;
}

.table-custom {
    color: #E2E8F0;
    margin-bottom: 0;
}
.table-custom thead th {
    background: rgba(15,23,42,0.8);
    color: #94A3B8;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    border-bottom: 1px solid var(--sh-border);
    padding: 16px 20px;
}
.table-custom tbody tr {
    border-bottom: 1px solid rgba(255,255,255,0.04);
    transition: background 0.2s;
}
.table-custom tbody tr:hover {
    background: rgba(6,182,212,0.05);
}
.table-custom td {
    padding: 16px 20px;
    vertical-align: middle;
}

/* Radar pulse animation */
.radar-pulse {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #10B981;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: pulse-green 1.6s infinite;
}

@keyframes pulse-green {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.badge-status {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.badge-status.new { background: rgba(245,158,11,0.15); color: #FBBF24; border: 1px solid rgba(245,158,11,0.3); }
.badge-status.in_transit, .badge-status.approved { background: rgba(6,182,212,0.15); color: #38BDF8; border: 1px solid rgba(6,182,212,0.3); }
.badge-status.delivered { background: rgba(16,185,129,0.15); color: #34D399; border: 1px solid rgba(16,185,129,0.3); }
.badge-status.canceled, .badge-status.rejected { background: rgba(244,63,94,0.15); color: #FB7185; border: 1px solid rgba(244,63,94,0.3); }

.btn-track {
    background: linear-gradient(135deg, #06B6D4, #2563EB);
    color: #fff;
    font-weight: 700;
    border-radius: 12px;
    padding: 8px 18px;
    border: none;
    transition: all 0.3s cubic-bezier(0.34,1.56,0.64,1);
    box-shadow: 0 4px 15px rgba(6,182,212,0.3);
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-track:hover {
    transform: translateY(-2px) scale(1.03);
    box-shadow: 0 8px 25px rgba(6,182,212,0.5);
    color: #fff;
}

/* ═════════════════════════════════════════════════════════════
   LIGHT MODE OVERRIDES FOR SHIPMENT TRACK SELECT PAGE
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
html.light-theme .main-card {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 15px 45px rgba(0,0,0,0.05) !important;
}
html.light-theme .table-custom {
    color: #0F172A !important;
}
html.light-theme .table-custom thead th {
    background: #F1F5F9 !important;
    color: #334155 !important;
    border-bottom-color: #E2E8F0 !important;
}
html.light-theme .table-custom tbody tr {
    border-bottom-color: #E2E8F0 !important;
}
html.light-theme .table-custom tbody tr:hover {
    background: #F8FAFC !important;
}
html.light-theme .table-custom td {
    color: #0F172A !important;
}
</style>

<div class="page-content">
    <!-- Breadcrumb Header -->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
        <div class="breadcrumb-title pe-3 text-white fw-bold">Shipments</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt text-info"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('all.shipments') }}" class="text-secondary">Shipment Operations</a></li>
                    <li class="breadcrumb-item active text-info" aria-current="page">Live Shipment Tracking</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a href="{{ route('all.shipments') }}" class="btn btn-outline-light rounded-pill px-4">
                <i class="bx bx-arrow-back me-1"></i> Back to Shipments
            </a>
        </div>
    </div>

    <!-- Title Banner -->
    <div class="card mb-4 border-0 overflow-hidden" style="background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%); border: 1px solid rgba(6,182,212,0.2) !important; border-radius: 18px;">
        <div class="card-body p-4 position-relative">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-info bg-opacity-10 p-3 rounded-4 border border-info border-opacity-25">
                        <i class="bx bxs-truck fs-1 text-info animate__animated animate__pulse animate__infinite"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold text-white mb-1">Real-Time Shipment Tracking Center</h3>
                        <p class="text-secondary mb-0">Select a shipment to track its live GPS location, speed, and status on the map</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Summary Row -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 mb-4">
        <div class="col">
            <div class="kpi-card">
                <div class="kpi-icon-wrap total">
                    <i class="bx bx-package"></i>
                </div>
                <div>
                    <div class="kpi-label">Total Shipments</div>
                    <div class="kpi-value">{{ number_format($stats['total']) }}</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="kpi-card">
                <div class="kpi-icon-wrap active">
                    <i class="bx bx-run"></i>
                </div>
                <div>
                    <div class="kpi-label">In Transit / Active</div>
                    <div class="kpi-value">{{ number_format($stats['in_transit']) }}</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="kpi-card">
                <div class="kpi-icon-wrap delivered">
                    <i class="bx bx-check-double"></i>
                </div>
                <div>
                    <div class="kpi-label">Delivered</div>
                    <div class="kpi-value">{{ number_format($stats['delivered']) }}</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="kpi-card">
                <div class="kpi-icon-wrap pending">
                    <i class="bx bx-time-five"></i>
                </div>
                <div>
                    <div class="kpi-label">Pending / New</div>
                    <div class="kpi-value">{{ number_format($stats['pending']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="filter-card">
        <form action="{{ route('track.shipments') }}" method="GET" class="row g-3 align-items-center">
            <div class="col-md-6 col-lg-5">
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bx bx-search fs-5"></i></span>
                    <input type="text" name="search" class="form-control bg-dark border-secondary text-white" placeholder="Search by Shipment ID, Customer Name, Address..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4 col-lg-4">
                <select name="status" class="form-select bg-dark border-secondary text-white" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>In Transit / Approved</option>
                    <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Canceled</option>
                </select>
            </div>
            <div class="col-md-2 col-lg-3 d-flex gap-2">
                <button type="submit" class="btn btn-info px-4 rounded-3 w-100 fw-bold">
                    <i class="bx bx-filter-alt me-1"></i> Filter
                </button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('track.shipments') }}" class="btn btn-outline-secondary rounded-3 px-3" title="Reset Filter">
                        <i class="bx bx-refresh fs-5"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Main Shipments Table Card -->
    <div class="main-card">
        <div class="card-header bg-transparent border-bottom border-white border-opacity-10 py-3 px-4 d-flex align-items-center justify-content-between">
            <h5 class="card-title text-white mb-0 fw-bold d-flex align-items-center gap-2">
                <i class="bx bx-list-ul text-info"></i> Shipments Available for Tracking
            </h5>
            <span class="badge bg-dark text-info border border-info border-opacity-25 px-3 py-2 rounded-pill">
                Total: {{ $shipments->count() }} Shipments
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-custom align-middle">
                <thead>
                    <tr>
                        <th>Shipment ID</th>
                        <th>Customer & Company</th>
                        <th>Route (Pickup ➔ Dropoff)</th>
                        <th>Driver & Truck</th>
                        <th>Status</th>
                        <th>Last GPS Update</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shipments as $shipment)
                        @php
                            $customerName = $shipment->customer ? ($shipment->customer->fname . ' ' . $shipment->customer->lname) : 'Unassigned';
                            $companyName  = $shipment->customer->companyProfile->company_name ?? null;
                            $pickupCity   = $shipment->pickupCity->name_en ?? ($shipment->pickupCity->name_ar ?? 'Pickup');
                            $dropoffCity  = $shipment->dropoffCity->name_en ?? ($shipment->dropoffCity->name_ar ?? 'Dropoff');
                            $driverName   = $shipment->driver ? ($shipment->driver->fname . ' ' . $shipment->driver->lname) : 'No Driver Assigned';
                            $lastLog      = $shipment->latestTrackingLog;
                            $lastTracking = $shipment->latestTracking;
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-bold text-white fs-6">#SHP-{{ sprintf('%04d', $shipment->id) }}</div>
                                <small class="text-secondary">{{ $shipment->shipment_name ?? 'General Cargo' }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-sm bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:36px; height:36px;">
                                        {{ mb_substr($customerName, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-white">{{ $customerName }}</div>
                                        @if($companyName)
                                            <small class="text-info d-block">{{ $companyName }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-secondary bg-opacity-25 text-white px-2 py-1">{{ $pickupCity }}</span>
                                    <i class="bx bx-right-arrow-alt text-info fs-5"></i>
                                    <span class="badge bg-info bg-opacity-25 text-info px-2 py-1">{{ $dropoffCity }}</span>
                                </div>
                                <small class="text-secondary d-block mt-1 text-truncate" style="max-width: 250px;">
                                    {{ $shipment->pickup_address }} ➔ {{ $shipment->dropoff_address }}
                                </small>
                            </td>
                            <td>
                                <div class="text-white fw-medium"><i class="bx bx-user me-1 text-secondary"></i>{{ $driverName }}</div>
                                <small class="text-secondary">
                                    {{ $shipment->truckType->name_en ?? ($shipment->truckType->name_ar ?? 'Truck') }}
                                </small>
                            </td>
                            <td>
                                @if(in_array($shipment->status, ['approved', 'in_transit']))
                                    <span class="badge-status in_transit">
                                        <span class="radar-pulse"></span> In Transit
                                    </span>
                                @elseif($shipment->status == 'delivered')
                                    <span class="badge-status delivered">
                                        <i class="bx bx-check-circle"></i> Delivered
                                    </span>
                                @elseif($shipment->status == 'new')
                                    <span class="badge-status new">
                                        <i class="bx bx-time"></i> New
                                    </span>
                                @else
                                    <span class="badge-status new">
                                        {{ ucfirst($shipment->status) }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($lastLog)
                                    <div class="text-info fw-semibold">
                                        <i class="bx bx-map-pin me-1"></i> {{ $lastLog->created_at ? $lastLog->created_at->diffForHumans() : 'Just now' }}
                                    </div>
                                    <small class="text-secondary font-monospace">{{ number_format($lastLog->latitude, 4) }}, {{ number_format($lastLog->longitude, 4) }}</small>
                                @elseif($lastTracking)
                                    <div class="text-light fw-medium">{{ $lastTracking->location_description ?? 'Recorded Point' }}</div>
                                    <small class="text-secondary">{{ $lastTracking->created_at ? $lastTracking->created_at->diffForHumans() : '—' }}</small>
                                @else
                                    <span class="text-secondary fs-7">Not Started</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('track.shipment.live', $shipment->id) }}" class="btn-track">
                                    <i class="bx bx-radar fs-5"></i> Live Track
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="py-4">
                                    <i class="bx bx-radar fs-1 text-secondary opacity-50 mb-3 d-block"></i>
                                    <h5 class="text-secondary fw-normal">No shipments match your search criteria</h5>
                                    <a href="{{ route('track.shipments') }}" class="btn btn-outline-info rounded-pill px-4 mt-2">Reset Filters</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
