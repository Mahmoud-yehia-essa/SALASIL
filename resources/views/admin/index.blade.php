@extends('admin.master_admin')
@section('admin')

<script type="text/javascript">
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        var data = google.visualization.arrayToDataTable([
            ['Operational Metric', 'Count'],
            ['Clients', {{ $clientsCount }}],
            ['Drivers', {{ $driversCount }}],
            ['Trucks', {{ $trucksCount }}],
            ['Scheduled Trips', {{ $scheduledTripsCount }}],
            ['Shipments', {{ $shipmentsCount }}],
            ['Invoices', {{ $invoicesCount }}],
        ]);

        var isLight = document.documentElement.classList.contains('light-theme');
        var titleColor = isLight ? '#0F172A' : '#F8FAFC';
        var legendColor = isLight ? '#475569' : '#94A3B8';

        var options = {
            title: 'SALASIL Network Operational Distribution',
            fontName: 'Plus Jakarta Sans',
            fontSize: 14,
            backgroundColor: 'transparent',
            titleTextStyle: { color: titleColor, fontSize: 16, bold: true },
            legend: { position: 'right', alignment: 'center', textStyle: { color: legendColor } },
            chartArea: { width: '85%', height: '80%' },
            colors: ['#06B6D4', '#14B8A6', '#0284C7', '#8B5CF6', '#F59E0B', '#10B981'],
            is3D: true
        };

        var chart = new google.visualization.PieChart(document.getElementById('piechart'));
        chart.draw(data, options);
    }

    // Re-draw chart on window resize or theme mode toggle
    window.addEventListener('resize', drawChart);
    document.addEventListener('DOMContentLoaded', function() {
        var modeIcons = document.querySelectorAll('.mode-icon, .dark-mode, .light-mode');
        modeIcons.forEach(function(icon) {
            icon.addEventListener('click', function() {
                setTimeout(drawChart, 150);
            });
        });
    });
</script>

<style>
    /* Google Charts SVG Light Theme Adaptive Styling */
    html.light-theme #piechart text {
        fill: #475569 !important;
    }
    html.light-theme #piechart text[text-anchor="start"] {
        fill: #0F172A !important;
    }

    .salasil-stat-card {
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-radius: 16px !important;
        background: #1E293B !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25) !important;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
        overflow: hidden;
    }
    .salasil-stat-card:hover {
        transform: translateY(-4px) !important;
        box-shadow: 0 15px 35px rgba(6, 182, 212, 0.2) !important;
        border-color: rgba(6, 182, 212, 0.4) !important;
    }
    .stat-icon-box {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: linear-gradient(135deg, rgba(6, 182, 212, 0.15) 0%, rgba(2, 132, 199, 0.25) 100%);
        color: #38BDF8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        border: 1px solid rgba(6, 182, 212, 0.2);
    }
    .stat-label {
        color: #94A3B8;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .stat-value {
        color: #F8FAFC;
        font-size: 1.85rem;
        font-weight: 800;
        font-family: 'Outfit', sans-serif;
    }
</style>

<!-- Dashboard Header Banner -->
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary border-opacity-25">
    <div>
        <h3 class="fw-bold text-white mb-0">Overview</h3>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 fw-semibold">
            <i class="bx bx-check-circle me-1"></i> System Online
        </span>
    </div>
</div>

<!-- Statistics Cards Row (Real Database Values) -->
<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3 mb-4">
    <!-- Total Clients -->
    <div class="col">
        <div class="card salasil-stat-card h-100">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="stat-label">Total Clients</span>
                    <h3 class="stat-value mb-0 mt-1">{{ number_format($clientsCount) }}</h3>
                </div>
                <div class="stat-icon-box">
                    <i class='bx bx-user-check'></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Fleet Drivers -->
    <div class="col">
        <div class="card salasil-stat-card h-100">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="stat-label">Drivers</span>
                    <h3 class="stat-value mb-0 mt-1">{{ number_format($driversCount) }}</h3>
                </div>
                <div class="stat-icon-box" style="background: rgba(20, 184, 166, 0.15); color: #14B8A6;">
                    <i class='bx bx-id-card'></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Trucks -->
    <div class="col">
        <div class="card salasil-stat-card h-100">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="stat-label">Total Trucks</span>
                    <h3 class="stat-value mb-0 mt-1">{{ number_format($trucksCount) }}</h3>
                </div>
                <div class="stat-icon-box" style="background: rgba(2, 132, 199, 0.15); color: #0284C7;">
                    <i class='bx bxs-truck'></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Scheduled Trips -->
    <div class="col">
        <div class="card salasil-stat-card h-100">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="stat-label">Scheduled Trips</span>
                    <h3 class="stat-value mb-0 mt-1">{{ number_format($scheduledTripsCount) }}</h3>
                </div>
                <div class="stat-icon-box" style="background: rgba(139, 92, 246, 0.15); color: #A78BFA;">
                    <i class='bx bx-map-pin'></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Shipments -->
    <div class="col">
        <div class="card salasil-stat-card h-100">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="stat-label">Total Shipments</span>
                    <h3 class="stat-value mb-0 mt-1">{{ number_format($shipmentsCount) }}</h3>
                </div>
                <div class="stat-icon-box" style="background: rgba(245, 158, 11, 0.15); color: #F59E0B;">
                    <i class='bx bx-package'></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices Issued -->
    <div class="col">
        <div class="card salasil-stat-card h-100">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="stat-label">Invoices</span>
                    <h3 class="stat-value mb-0 mt-1" style="color: #34D399;">{{ number_format($invoicesCount) }}</h3>
                </div>
                <div class="stat-icon-box" style="background: rgba(16, 185, 129, 0.15); color: #34D399;">
                    <i class='bx bx-receipt'></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart Row -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card salasil-stat-card">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3 px-4">
                <h5 class="card-title text-white mb-0 fw-bold">
                    <i class="bx bx-pie-chart-alt-2 text-info me-2"></i>Network Distribution Analytics
                </h5>
            </div>
            <div class="card-body p-3">
                <div id="piechart" style="width: 100%; height: 360px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Registered Clients Table -->
<div class="card salasil-stat-card">
    <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3 px-4 d-flex justify-content-between align-items-center">
        <h5 class="card-title text-white mb-0 fw-bold">
            <i class="bx bx-user-plus text-info me-2"></i>Recent Registered Clients
        </h5>
        <span class="badge bg-info bg-opacity-10 text-info px-3 py-1 fw-semibold">Latest {{ count($recentClients) }} Clients</span>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Client Name</th>
                        <th>Email Address</th>
                        <th>Phone Number</th>
                        <th>Client Type</th>
                        <th>Registration Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentClients as $key => $client)
                        @php
                            $clientName  = trim(($client->fname ?? '') . ' ' . ($client->lname ?? ''));
                            $companyName = $client->companyProfile->company_name ?? null;
                            $roleLabel   = ($client->role == 'company_customer') ? 'Company Client' : 'Individual Client';
                        @endphp
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td class="fw-bold text-white">
                                {{ $clientName ?: 'Client #' . $client->id }}
                                @if($companyName)
                                    <small class="d-block text-info fs-7">{{ $companyName }}</small>
                                @endif
                            </td>
                            <td>{{ $client->email }}</td>
                            <td>{{ $client->phone ? (($client->country_code ?? '') . ' ' . $client->phone) : '—' }}</td>
                            <td>
                                <span class="badge bg-primary bg-opacity-25 text-info px-3 py-2 border border-info border-opacity-25">
                                    <i class="bx bx-user me-1"></i> {{ $roleLabel }}
                                </span>
                            </td>
                            <td>
                                <span class="text-slate-400" style="color: #94A3B8;">
                                    <i class="bx bx-time me-1"></i> {{ $client->created_at ? $client->created_at->diffForHumans() : 'Recently' }}
                                </span>
                            </td>
                            <td>
                                @if(in_array(strtolower($client->status ?? ''), ['active', 'approved', '1']))
                                    <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-25 px-3 py-1">Active</span>
                                @else
                                    <span class="badge bg-warning bg-opacity-20 text-warning border border-warning border-opacity-25 px-3 py-1">{{ ucfirst($client->status ?? 'Pending') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-secondary">No registered clients found in the database.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
