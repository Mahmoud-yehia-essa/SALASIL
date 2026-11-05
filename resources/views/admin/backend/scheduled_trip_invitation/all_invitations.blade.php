@extends('admin.master_admin')
@section('admin')

{{-- ==========================================
     ALL SCHEDULED TRIP DRIVER INVITATIONS
========================================== --}}

<style>
:root {
    --inv-cyan:       #06B6D4;
    --inv-cyan-light: #38BDF8;
    --inv-navy:       #0F172A;
    --inv-card:       #1E293B;
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
.kpi-icon-wrap.pending  { background: linear-gradient(135deg, #F59E0B, #FCD34D); box-shadow: 0 8px 20px rgba(245,158,11,0.3); color:#0F172A; }
.kpi-icon-wrap.accepted { background: linear-gradient(135deg, #10B981, #34D399); box-shadow: 0 8px 20px rgba(16,185,129,0.3); }
.kpi-icon-wrap.rejected { background: linear-gradient(135deg, #F43F5E, #FB7185); box-shadow: 0 8px 20px rgba(244,63,94,0.3); }

.kpi-label { font-size: 0.8rem; font-weight: 700; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.5px; }
.kpi-value { font-size: 1.6rem; font-weight: 800; color: #F8FAFC; margin-top: 2px; }

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

/* ─── Data Table Container ─── */
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
}

/* ─── Badges & Status Controls ─── */
.channel-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 50px;
    font-size: 0.78rem;
    font-weight: 700;
}
.channel-badge.in_app   { background: rgba(6,182,212,0.15); color: #38BDF8; border: 1px solid rgba(6,182,212,0.3); }
.channel-badge.whatsapp { background: rgba(37,211,102,0.15); color: #25D366; border: 1px solid rgba(37,211,102,0.3); }
.channel-badge.sms      { background: rgba(245,158,11,0.15); color: #F59E0B; border: 1px solid rgba(245,158,11,0.3); }

.status-select-sm {
    background: rgba(255,255,255,0.06) !important;
    border: 1px solid rgba(255,255,255,0.15) !important;
    color: #F8FAFC !important;
    border-radius: 50px !important;
    padding: 5px 28px 5px 14px !important;
    font-size: 0.8rem !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    width: auto !important;
    display: inline-block !important;
}
.status-select-sm.status-pending  { border-color: rgba(245,158,11,0.5) !important; color: #F59E0B !important; }
.status-select-sm.status-accepted { border-color: rgba(16,185,129,0.5) !important; color: #34D399 !important; }
.status-select-sm.status-rejected { border-color: rgba(244,63,94,0.5) !important;  color: #FB7185 !important; }
.status-select-sm.status-canceled { border-color: rgba(148,163,184,0.5) !important; color: #94A3B8 !important; }

/* ─── Action Buttons ─── */
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
.btn-action.btn-wa:hover {
    background: rgba(37,211,102,0.15);
    border-color: #25D366;
    color: #25D366;
}
.btn-action.btn-copy-link:hover {
    background: rgba(139,92,246,0.15);
    border-color: #8B5CF6;
    color: #A78BFA;
}

/* ═════════════════════════════════════════════════════════════
   LIGHT MODE OVERRIDES
═════════════════════════════════════════════════════════════ */
html.light-theme .kpi-card {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 8px 25px rgba(0,0,0,0.04) !important;
}
html.light-theme .kpi-label { color: #64748B !important; }
html.light-theme .kpi-value { color: #0F172A !important; }
html.light-theme .filter-card {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 8px 25px rgba(0,0,0,0.03) !important;
}
html.light-theme .filter-card .form-control,
html.light-theme .filter-card .form-select {
    background: #F8FAFC !important;
    border-color: #CBD5E1 !important;
    color: #0F172A !important;
}
html.light-theme .filter-card .form-select option { background: #FFFFFF !important; color: #0F172A !important; }
html.light-theme .inv-table-card {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 15px 45px rgba(0,0,0,0.05) !important;
}
html.light-theme .inv-table { color: #334155 !important; }
html.light-theme .inv-table thead th {
    background: #F8FAFC !important;
    color: #475569 !important;
    border-bottom: 2px solid #E2E8F0 !important;
}
html.light-theme .inv-table tbody tr { border-bottom-color: #F1F5F9 !important; }
html.light-theme .inv-table tbody tr:hover { background: #F0F9FF !important; }
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
html.light-theme .status-select-sm.status-pending { background: #FEF3C7 !important; color: #D97706 !important; border-color: #F59E0B !important; }
html.light-theme .status-select-sm.status-accepted { background: #D1FAE5 !important; color: #059669 !important; border-color: #10B981 !important; }
html.light-theme .status-select-sm.status-rejected { background: #FFE4E6 !important; color: #E11D48 !important; border-color: #F43F5E !important; }
html.light-theme .status-select-sm.status-canceled { background: #F1F5F9 !important; color: #64748B !important; border-color: #CBD5E1 !important; }
</style>

{{-- Page Header --}}
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold mb-1 header-title">
            <i class="bx bx-calendar-event text-info me-2"></i>Scheduled Trip Driver Invitations
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Manage and track all driver invitations sent for fixed and scheduled trip routes.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('all.scheduled.trips') }}" class="btn btn-outline-secondary rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-map-pin fs-5"></i>
            <span>Scheduled Trips</span>
        </a>
        <a href="{{ route('add.scheduled.trip.invitation') }}" class="btn btn-primary rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2" style="background: linear-gradient(135deg, var(--inv-cyan), var(--inv-cyan-light)); border:none; color:#0F172A;">
            <i class="bx bx-send fs-5"></i>
            <span>Invite Driver to Trip</span>
        </a>
    </div>
</div>

{{-- ─── KPI Cards ─── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap total"><i class="bx bx-mail-send"></i></div>
            <div>
                <div class="kpi-label">Total Invitations</div>
                <div class="kpi-value">{{ $stats['total'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap pending"><i class="bx bx-time-five"></i></div>
            <div>
                <div class="kpi-label">Pending Response</div>
                <div class="kpi-value">{{ $stats['pending'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap accepted"><i class="bx bx-check-double"></i></div>
            <div>
                <div class="kpi-label">Accepted by Driver</div>
                <div class="kpi-value">{{ $stats['accepted'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap rejected"><i class="bx bx-x-circle"></i></div>
            <div>
                <div class="kpi-label">Declined / Canceled</div>
                <div class="kpi-value">{{ $stats['rejected_canceled'] }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ─── Filter & Search Bar ─── --}}
<div class="filter-card">
    <form method="GET" action="{{ route('all.scheduled.trip.invitations') }}" id="filterForm">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-md-4">
                <div class="position-relative">
                    <input type="text" name="search" class="form-control ps-5" placeholder="Search driver, phone, trip ID, city..." value="{{ request('search') }}">
                    <span class="position-absolute text-muted" style="left:14px;top:50%;transform:translateY(-50%);"><i class="bx bx-search fs-5"></i></span>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <select name="status" class="form-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Accepted</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="canceled" {{ request('status') === 'canceled' ? 'selected' : '' }}>Canceled</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="channel" class="form-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Channels</option>
                    <option value="in_app" {{ request('channel') === 'in_app' ? 'selected' : '' }}>In-App Alert</option>
                    <option value="whatsapp" {{ request('channel') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                    <option value="sms" {{ request('channel') === 'sms' ? 'selected' : '' }}>SMS</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <select name="driver_id" class="form-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Drivers</option>
                    @foreach($drivers as $d)
                    <option value="{{ $d->id }}" {{ request('driver_id') == $d->id ? 'selected' : '' }}>
                        {{ $d->fname }} {{ $d->lname }} ({{ $d->phone }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-1 text-end">
                <a href="{{ route('all.scheduled.trip.invitations') }}" class="btn btn-outline-secondary w-100 rounded-3" title="Reset Filters"><i class="bx bx-refresh fs-5"></i></a>
            </div>
        </div>
    </form>
</div>

{{-- ─── Data Table Card ─── --}}
<div class="inv-table-card card border-0 shadow-lg rounded-4 overflow-hidden">
    <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
            <i class="bx bx-table text-info fs-4"></i>
            <span>Scheduled Trip Driver Invitation Records</span>
        </h5>
        <span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-25 px-3 py-1 fw-semibold fs-6">
            Total Records: {{ count($invitations) }}
        </span>
    </div>

    <div class="card-body p-4">
        <div class="table-responsive">
            <table id="example" class="table table-striped table-bordered align-middle inv-table" style="width:100%">
                <thead>
                    <tr>
                        <th style="width: 40px;" class="text-center">#</th>
                        <th>Driver Name</th>
                        <th>Scheduled Trip Details</th>
                        <th>Channel</th>
                        <th style="width: 175px;">Status</th>
                        <th>Sent At</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invitations as $key => $inv)
                        @php
                            $magicLink = $inv->magic_link;
                            $driverPhoneClean = preg_replace('/[^0-9]/', '', $inv->driver->phone ?? '');
                            $driverName = $inv->driver->fname ?? 'Driver';
                            $trip = $inv->scheduledTrip;
                            $order = $inv->scheduledTripOrder;
                            $originCity = $order->pickupCity->name_en ?? ($trip->route->originCity->name_en ?? 'Origin');
                            $destCity   = $order->dropoffCity->name_en ?? ($trip->route->destinationCity->name_en ?? 'Destination');
                            $orderLabel = $order ? ($order->order_number ?: ('#STO-' . $order->id)) : ("Scheduled Trip #{$inv->scheduled_trip_id}");
                            $waText = "Hello {$driverName},\nYou have been invited by SALASIL Operations for {$orderLabel} ({$originCity} ➔ {$destCity}).\nOffered Driver Compensation: KWD " . number_format($inv->offered_price, 2) . "\nPlease click the secure link below to view cargo, route specifications and respond:\n{$magicLink}";
                            $whatsappUrl = "https://wa.me/{$driverPhoneClean}?text=" . urlencode($waText);
                        @endphp
                        <tr>
                            {{-- # ID --}}
                            <td class="text-center fw-bold">{{ $key + 1 }}</td>

                            {{-- Driver Name & Profile --}}
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-sm rounded-circle bg-info bg-opacity-15 text-info d-flex align-items-center justify-content-center fw-bold fs-6" style="width: 38px; height: 38px; flex-shrink: 0; border: 1px solid rgba(6,182,212,0.3);">
                                        {{ strtoupper(substr($inv->driver->fname ?? 'D', 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-bold text-white fs-6 d-block">{{ $inv->driver->fname ?? '—' }} {{ $inv->driver->lname ?? '' }}</span>
                                        <small class="text-slate-400 d-block fs-8"><i class="bx bx-phone me-1 text-info"></i>{{ $inv->driver->phone ?? '—' }}</small>
                                        @if($inv->driverTruck)
                                            <small class="text-info d-block fs-8 mt-0.5">
                                                <i class="bx bx-truck me-1"></i>{{ $inv->driverTruck->truckType->name_en ?? 'Truck' }} ({{ $inv->driverTruck->plate_number }})
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Scheduled Trip Order Details --}}
                            <td>
                                @if($order)
                                    <div class="fw-bold text-slate-100">
                                        <span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-30 me-1">{{ $order->order_number ?: ('#STO-' . $order->id) }}</span>
                                        {{ $originCity }} ➔ {{ $destCity }}
                                    </div>
                                    <small class="text-slate-400 d-block fs-8 mt-0.5">
                                        <i class="bx bx-user text-info me-1"></i>{{ $order->customer ? ($order->customer->fname . ' ' . $order->customer->lname) : 'Customer' }}
                                        <span class="mx-1">•</span>
                                        <i class="bx bx-calendar text-info me-1"></i>{{ $order->loading_date ? date('Y-m-d', strtotime($order->loading_date)) : 'Flexible Date' }}
                                        <span class="mx-1">•</span>
                                        <i class="bx bx-truck text-secondary me-1"></i>{{ $order->truckType->name_en ?? ($trip->truckType->name_en ?? 'Truck') }}
                                    </small>
                                    <div class="mt-1.5 d-flex flex-wrap gap-1.5 align-items-center">
                                        {{-- Order Total Value --}}
                                        <span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-30 px-2.5 py-1 fw-bold fs-7" title="Customer Order Value">
                                            <i class="bx bx-receipt me-1"></i> Order: KWD {{ number_format($order->total_price ?: ($order->trip_price ?: 0), 2) }}
                                        </span>

                                        {{-- Offered Price to Driver --}}
                                        <span class="badge bg-warning bg-opacity-20 text-warning border border-warning border-opacity-30 px-2.5 py-1 fw-bold fs-7" title="Offered Price to Driver">
                                            <i class="bx bx-money me-1"></i> Offered: KWD {{ number_format($inv->offered_price, 2) }}
                                        </span>
                                    </div>
                                @elseif($trip)
                                    <div class="fw-bold text-slate-100">
                                        #{{ $trip->id }} — {{ $originCity }} ➔ {{ $destCity }}
                                    </div>
                                    <small class="text-slate-400 d-block fs-8 mt-0.5">
                                        <i class="bx bx-calendar text-info me-1"></i>{{ $trip->trip_date ? date('Y-m-d', strtotime($trip->trip_date)) : 'Flexible Date' }}
                                        @if($trip->trip_time)
                                            <span class="ms-1 text-muted">({{ date('h:i A', strtotime($trip->trip_time)) }})</span>
                                        @endif
                                        <span class="mx-1">•</span>
                                        <i class="bx bx-truck text-secondary me-1"></i>{{ $trip->truckType->name_en ?? 'Truck' }}
                                    </small>
                                    <div class="mt-1.5 d-flex flex-wrap gap-1.5 align-items-center">
                                        <span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-30 px-2.5 py-1 fw-bold fs-7" title="Base Trip Price">
                                            <i class="bx bx-receipt me-1"></i> Trip Price: KWD {{ number_format($trip->price ?? 0, 2) }}
                                        </span>
                                        <span class="badge bg-warning bg-opacity-20 text-warning border border-warning border-opacity-30 px-2.5 py-1 fw-bold fs-7" title="Offered Price to Driver">
                                            <i class="bx bx-money me-1"></i> Offered: KWD {{ number_format($inv->offered_price ?: ($trip->price ?? 0), 2) }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-muted">Order Record Removed</span>
                                @endif
                            </td>

                            {{-- Channel --}}
                            <td>
                                @if($inv->channel === 'in_app')
                                    <span class="channel-badge in_app"><i class="bx bx-bell"></i> In-App Alert</span>
                                @elseif($inv->channel === 'whatsapp')
                                    <span class="channel-badge whatsapp"><i class="bx bxl-whatsapp"></i> WhatsApp</span>
                                @else
                                    <span class="channel-badge sms"><i class="bx bx-message-square-dots"></i> SMS</span>
                                @endif
                            </td>

                            {{-- Status + Rejection Reason Icon --}}
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <select class="form-select status-select-sm status-{{ $inv->status }}" 
                                            onchange="updateInvitationStatus({{ $inv->id }}, this.value)">
                                        <option value="pending" {{ $inv->status==='pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="accepted" {{ $inv->status==='accepted' ? 'selected' : '' }}>Accepted</option>
                                        <option value="rejected" {{ $inv->status==='rejected' ? 'selected' : '' }}>Rejected</option>
                                        <option value="canceled" {{ $inv->status==='canceled' ? 'selected' : '' }}>Canceled</option>
                                    </select>

                                    @if($inv->status === 'rejected')
                                        <span class="rejection-trigger ms-1" 
                                              data-bs-toggle="tooltip" 
                                              data-bs-placement="top" 
                                              title="Rejection Reason: {{ $inv->rejection_reason ?: 'No reason specified' }}" 
                                              data-driver="{{ $inv->driver->fname ?? 'Driver' }} {{ $inv->driver->lname ?? '' }}" 
                                              data-trip="#{{ $inv->scheduled_trip_id }} — {{ $originCity }} ➔ {{ $destCity }}" 
                                              data-reason="{{ $inv->rejection_reason ?: 'No rejection reason specified.' }}" 
                                              onclick="openRejectionReasonModal(this)"
                                              style="cursor: pointer;">
                                            <i class="bx bx-error-circle text-danger fs-5"></i>
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Sent At --}}
                            <td class="text-slate-300" style="font-size:0.82rem;">
                                {{ $inv->created_at ? $inv->created_at->format('Y-m-d H:i') : '—' }}
                            </td>

                            {{-- Actions --}}
                            <td class="text-center">
                                <div class="d-inline-flex align-items-center justify-content-center gap-2">
                                    {{-- Direct WhatsApp Send Button --}}
                                    @if($driverPhoneClean)
                                        <a href="{{ $whatsappUrl }}" 
                                           target="_blank" 
                                           class="btn-action btn-wa" 
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top" 
                                           title="Send via WhatsApp (إرسال عبر الواتساب)"
                                           style="color: #25D366; border-color: rgba(37,211,102,0.3);">
                                            <i class="bx bxl-whatsapp fs-5"></i>
                                        </a>
                                    @endif

                                    {{-- Copy Link Action Button --}}
                                    @if(in_array($inv->channel, ['whatsapp', 'sms']) || $inv->token)
                                        <button type="button" 
                                                class="btn-action btn-copy-link" 
                                                data-link="{{ $magicLink }}" 
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                title="Copy Link" 
                                                onclick="copyMagicLink(this, '{{ $magicLink }}')">
                                            <i class="bx bx-copy fs-5"></i>
                                        </button>
                                    @endif

                                    <a href="{{ route('edit.scheduled.trip.invitation', $inv->id) }}" class="btn-action" title="Edit Invitation">
                                        <i class="bx bx-edit fs-5"></i>
                                    </a>

                                    <a href="{{ route('delete.scheduled.trip.invitation', $inv->id) }}" class="btn-action btn-del confirm-delete" title="Delete Invitation">
                                        <i class="bx bx-trash fs-5"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ─── REJECTION REASON MODAL ─── --}}
<div class="modal fade" id="rejectionModal" tabindex="-1" aria-labelledby="rejectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background: #141E47; border: 1px solid rgba(244, 63, 94, 0.35) !important;">
            <div class="modal-header border-bottom border-secondary border-opacity-25 py-3 px-4">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="rejectionModalLabel">
                    <i class="bx bx-error-circle text-danger fs-4"></i>
                    <span>Driver Rejection Details</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-start">
                <div class="mb-3 p-3 rounded-3 bg-dark bg-opacity-50 border border-secondary border-opacity-25">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-slate-400 fs-8 fw-semibold text-uppercase">Driver Name</span>
                        <span class="text-white fw-bold fs-7" id="rejectModalDriver">—</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-slate-400 fs-8 fw-semibold text-uppercase">Scheduled Trip</span>
                        <span class="text-info fw-bold fs-7" id="rejectModalTrip">—</span>
                    </div>
                </div>

                <label class="form-label text-slate-300 fw-bold fs-8 text-uppercase mb-2">Reason Provided by Driver:</label>
                <div class="p-3 rounded-3 border border-danger border-opacity-30 bg-danger bg-opacity-10 text-danger-emphasis fs-7" id="rejectModalReason" style="min-height: 80px; white-space: pre-wrap; line-height: 1.6;">
                    —
                </div>
            </div>
            <div class="modal-footer border-top border-secondary border-opacity-25 py-2 px-4 bg-dark bg-opacity-30">
                <button type="button" class="btn btn-secondary rounded-3 px-4 btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// ── Change Status via AJAX ──
function updateInvitationStatus(invitationId, newStatus) {
    var reason = null;
    if (newStatus === 'rejected') {
        reason = prompt("Please enter the driver's rejection reason (optional):");
    }

    $.ajax({
        url: '{{ route("scheduled.trip.invitation.status.ajax") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            invitation_id: invitationId,
            status: newStatus,
            rejection_reason: reason
        },
        success: function(res) {
            if (res.status === 'success') {
                toastr.success(res.message);
                setTimeout(function() {
                    location.reload();
                }, 800);
            }
        },
        error: function(err) {
            toastr.error('Failed to update invitation status.');
        }
    });
}

// ── Open Rejection Reason Modal ──
function openRejectionReasonModal(element) {
    var driver = $(element).data('driver');
    var trip = $(element).data('trip');
    var reason = $(element).data('reason');

    $('#rejectModalDriver').text(driver);
    $('#rejectModalTrip').text(trip);
    $('#rejectModalReason').text(reason);

    var modal = new bootstrap.Modal(document.getElementById('rejectionModal'));
    modal.show();
}

// ── Copy Magic Link to Clipboard ──
function copyMagicLink(btn, url) {
    navigator.clipboard.writeText(url).then(function() {
        var originalTitle = $(btn).attr('title') || 'Copy Link';
        $(btn).attr('title', 'Copied!').tooltip('dispose').tooltip('show');
        toastr.success('Invitation Magic Link copied to clipboard!');
        setTimeout(function() {
            $(btn).attr('title', originalTitle).tooltip('dispose').tooltip();
        }, 2000);
    }).catch(function() {
        prompt("Copy Invitation Link:", url);
    });
}
</script>

@endsection

