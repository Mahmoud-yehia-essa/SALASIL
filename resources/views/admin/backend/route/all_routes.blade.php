@extends('admin.master_admin')
@section('admin')

{{-- ========================
     ALL FIXED ROUTES
======================== --}}

<style>
:root {
    --rt-cyan:       #06B6D4;
    --rt-cyan-light: #38BDF8;
    --rt-navy:       #0F172A;
    --rt-border:     rgba(255,255,255,0.08);
    --rt-success:    #10B981;
    --rt-danger:     #F43F5E;
}

/* ─── KPI Cards ─── */
.kpi-card {
    background: linear-gradient(135deg, rgba(30,41,59,0.9) 0%, rgba(15,23,42,0.95) 100%);
    border: 1px solid var(--rt-border);
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
.kpi-icon-wrap.total { background: linear-gradient(135deg, #06B6D4, #38BDF8); box-shadow: 0 8px 20px rgba(6,182,212,0.3); }
.kpi-icon-wrap.local { background: linear-gradient(135deg, #10B981, #34D399); box-shadow: 0 8px 20px rgba(16,185,129,0.3); }
.kpi-icon-wrap.intl  { background: linear-gradient(135deg, #8B5CF6, #A78BFA); box-shadow: 0 8px 20px rgba(139,92,246,0.3); }
.kpi-icon-wrap.active{ background: linear-gradient(135deg, #F59E0B, #FCD34D); box-shadow: 0 8px 20px rgba(245,158,11,0.3); color:#0F172A; }

.kpi-label { font-size: 0.8rem; font-weight: 700; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.5px; }
.kpi-value { font-size: 1.6rem; font-weight: 800; color: #F8FAFC; margin-top: 2px; }

/* ─── Filter Bar ─── */
.filter-card {
    background: rgba(30,41,59,0.7);
    border: 1px solid var(--rt-border);
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
    border-color: var(--rt-cyan) !important;
    box-shadow: 0 0 0 3px rgba(6,182,212,0.15) !important;
}
.filter-card .form-select option { background: #1E293B; color: #F8FAFC; }

/* ─── Data Table ─── */
.rt-table-card {
    background: linear-gradient(135deg, rgba(30,41,59,0.95) 0%, rgba(15,23,42,0.98) 100%);
    border: 1px solid var(--rt-border);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0,0,0,0.35);
}
.rt-table {
    width: 100%;
    margin-bottom: 0;
    color: #CBD5E1;
    border-collapse: separate;
    border-spacing: 0;
}
.rt-table th {
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
.rt-table td {
    padding: 16px 20px;
    vertical-align: middle;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    font-size: 0.88rem;
}
.rt-table tbody tr { transition: background 0.2s; }
.rt-table tbody tr:hover { background: rgba(6,182,212,0.05); }

/* ─── Badges & Buttons ─── */
.type-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 12px; border-radius: 50px;
    font-size: 0.78rem; font-weight: 700;
}
.type-badge.local { background: rgba(16,185,129,0.15); color: #34D399; border: 1px solid rgba(16,185,129,0.3); }
.type-badge.intl  { background: rgba(139,92,246,0.15); color: #A78BFA; border: 1px solid rgba(139,92,246,0.3); }

.status-switch { cursor: pointer; }

.btn-action {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: inline-flex; align-items: center; justify-content: center;
    border: 1px solid var(--rt-border);
    background: rgba(255,255,255,0.04);
    color: #CBD5E1;
    transition: all 0.2s;
}
.btn-action:hover {
    background: rgba(6,182,212,0.15);
    border-color: var(--rt-cyan);
    color: #38BDF8;
    transform: translateY(-2px);
}
.btn-action.btn-del:hover {
    background: rgba(244,63,94,0.15);
    border-color: var(--rt-danger);
    color: #F43F5E;
}

/* ═════════════════════════════════════════════════════════════
   LIGHT MODE OVERRIDES FOR ALL FIXED ROUTES PAGE
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
html.light-theme .rt-table-card {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 15px 45px rgba(0,0,0,0.05) !important;
}
html.light-theme .rt-table {
    color: #0F172A !important;
}
html.light-theme .rt-table th {
    background: #F1F5F9 !important;
    color: #334155 !important;
    border-bottom-color: #E2E8F0 !important;
}
html.light-theme .rt-table td {
    border-bottom-color: #F1F5F9 !important;
    color: #0F172A !important;
}
html.light-theme .rt-table tbody tr:hover {
    background: #F8FAFC !important;
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
</style>

{{-- Page Header --}}
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold mb-1 header-title">
            <i class="bx bx-map-alt text-info me-2"></i>Fixed Routes System
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Manage origin and destination routes for scheduled trips.
        </p>
    </div>
    <div>
        <a href="{{ route('add.route') }}" class="btn btn-info rounded-3 px-3 py-2 fw-bold d-inline-flex align-items-center gap-2" style="background:linear-gradient(135deg,#06B6D4,#38BDF8);border:none;color:#0F172A;">
            <i class="bx bx-plus-circle fs-5"></i>
            <span>Add Fixed Route</span>
        </a>
    </div>
</div>

{{-- ─── KPI Stats Bar ─── --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap total"><i class="bx bx-map-alt"></i></div>
            <div>
                <div class="kpi-label">Total Routes</div>
                <div class="kpi-value">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap local"><i class="bx bx-home-alt"></i></div>
            <div>
                <div class="kpi-label">Local Routes</div>
                <div class="kpi-value">{{ number_format($stats['local']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap intl"><i class="bx bx-globe"></i></div>
            <div>
                <div class="kpi-label">International</div>
                <div class="kpi-value">{{ number_format($stats['international']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap active"><i class="bx bx-check-circle"></i></div>
            <div>
                <div class="kpi-label">Active Routes</div>
                <div class="kpi-value">{{ number_format($stats['active']) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ─── Filter & Search Bar ─── --}}
<div class="filter-card">
    <form method="GET" action="{{ route('all.routes') }}" id="filterForm">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-md-5">
                <div class="position-relative">
                    <input type="text" name="search" class="form-control ps-5" placeholder="Search city or route ID..." value="{{ request('search') }}">
                    <span class="position-absolute text-muted" style="left:14px;top:50%;transform:translateY(-50%);"><i class="bx bx-search fs-5"></i></span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="quote_type" class="form-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Types (Local / Intl)</option>
                    <option value="local" {{ request('quote_type') === 'local' ? 'selected' : '' }}>Local (Domestic)</option>
                    <option value="international" {{ request('quote_type') === 'international' ? 'selected' : '' }}>International</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-12 col-md-1 text-end">
                <a href="{{ route('all.routes') }}" class="btn btn-outline-secondary w-100 rounded-3" title="Reset Filters"><i class="bx bx-refresh fs-5"></i></a>
            </div>
        </div>
    </form>
</div>

{{-- ─── Data Table ─── --}}
<div class="rt-table-card">
    <div class="table-responsive">
        <table class="table rt-table">
            <thead>
                <tr>
                    <th># ID</th>
                    <th>Quote Type</th>
                    <th>Origin Point</th>
                    <th>Destination Point</th>
                    <th>Est. Distance</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($routes as $rt)
                <tr>
                    {{-- ID --}}
                    <td class="fw-bold text-white">#{{ $rt->id }}</td>

                    {{-- Quote Type Badge --}}
                    <td>
                        @if($rt->quote_type === 'international')
                            <span class="type-badge intl"><i class="bx bx-globe"></i> International</span>
                        @else
                            <span class="type-badge local"><i class="bx bx-map-pin"></i> Local</span>
                        @endif
                    </td>

                    {{-- Origin --}}
                    <td>
                        <div class="fw-bold text-white">
                            <i class="bx bx-map-pin me-1 text-info"></i>{{ $rt->originCity->name_en ?? '—' }}
                        </div>
                        <div class="text-muted" style="font-size:0.78rem;">
                            {{ $rt->originCountry->name_en ?? '—' }}
                        </div>
                    </td>

                    {{-- Destination --}}
                    <td>
                        <div class="fw-bold text-white">
                            <i class="bx bx-navigation me-1 text-success"></i>{{ $rt->destinationCity->name_en ?? '—' }}
                        </div>
                        <div class="text-muted" style="font-size:0.78rem;">
                            {{ $rt->destinationCountry->name_en ?? '—' }}
                        </div>
                    </td>

                    {{-- Est Distance --}}
                    <td>
                        @if($rt->estimated_distance)
                            <span class="badge bg-dark border border-secondary border-opacity-25 text-info">{{ number_format($rt->estimated_distance, 1) }} KM</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    {{-- Status Toggle --}}
                    <td>
                        <div class="form-check form-switch status-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="status_{{ $rt->id }}" {{ $rt->status === 'active' ? 'checked' : '' }} onchange="toggleRouteStatus({{ $rt->id }}, this.checked)">
                            <label class="form-check-label text-slate-300 ms-1" style="font-size:0.8rem;" id="status_label_{{ $rt->id }}">{{ ucfirst($rt->status) }}</label>
                        </div>
                    </td>

                    {{-- Created At --}}
                    <td class="text-muted" style="font-size:0.82rem;">
                        {{ $rt->created_at ? $rt->created_at->format('Y-m-d') : '—' }}
                    </td>

                    {{-- Actions --}}
                    <td class="text-end">
                        <div class="d-inline-flex gap-2">
                            <a href="{{ route('edit.route', $rt->id) }}" class="btn-action" title="Edit Route">
                                <i class="bx bx-edit fs-5"></i>
                            </a>
                            <a href="{{ route('delete.route', $rt->id) }}" class="btn-action btn-del confirm-delete" title="Delete Route">
                                <i class="bx bx-trash fs-5"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bx bx-map-alt fs-1 text-slate-500 mb-2 d-block"></i>
                        No fixed routes found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    // ── Toggle Route Status via AJAX ──
    function toggleRouteStatus(id, isChecked) {
        var newStatus = isChecked ? 'active' : 'inactive';
        $.ajax({
            url: '{{ route("route.status.ajax") }}',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                route_id: id,
                status: newStatus
            },
            success: function(res) {
                if (res.status === 'success') {
                    $('#status_label_' + id).text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1));
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
            title: 'Delete Fixed Route?',
            text: "Scheduled trips linked to this route may be impacted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#F43F5E',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Yes, Delete Route!',
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
