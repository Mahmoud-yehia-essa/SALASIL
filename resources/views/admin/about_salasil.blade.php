@extends('admin.master_admin')
@section('admin')

<style>
    .salasil-about-hero {
        background: linear-gradient(135deg, #0F172A 0%, #1E293B 50%, #06B6D4 150%);
        border-radius: 20px;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
    }
    .salasil-about-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(6, 182, 212, 0.12) 0%, transparent 60%);
        pointer-events: none;
    }
    .feature-card {
        background: rgba(30, 41, 59, 0.7);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        transition: all 0.3s ease;
    }
    .feature-card:hover {
        transform: translateY(-5px);
        border-color: rgba(6, 182, 212, 0.4);
        box-shadow: 0 12px 30px rgba(6, 182, 212, 0.15);
    }
    .feature-icon-wrapper {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }
    .badge-glow-cyan {
        background: rgba(6, 182, 212, 0.15);
        color: #38BDF8;
        border: 1px solid rgba(6, 182, 212, 0.3);
        box-shadow: 0 0 15px rgba(6, 182, 212, 0.2);
    }
    .stat-pill {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        padding: 12px 20px;
    }
</style>

<div class="container-fluid px-0">
    <!-- Breadcrumb & Header Title -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary border-opacity-25">
        <div>
            <h3 class="fw-bold text-white mb-1">About SALASIL Platform</h3>
            <p class="text-slate-400 fs-8 mb-0">Overview of SALASIL Smart Logistics Network & Supply Chain Ecosystem</p>
        </div>
        <div>
            <span class="badge badge-glow-cyan px-3 py-2 fw-semibold rounded-pill">
                <i class="bx bx-planet me-1"></i> SALASIL Logistics v2.0
            </span>
        </div>
    </div>

    <!-- Hero Banner -->
    <div class="salasil-about-hero p-4 p-md-5 mb-5 text-white">
        <div class="row align-items-center position-relative" style="z-index: 2;">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-white rounded-3 p-2 d-flex align-items-center justify-content-center shadow-lg" style="height: 48px;">
                        <img src="{{ asset('backend/assets/images/salasil-logo.svg') }}" style="max-height: 38px; width: auto;" alt="SALASIL Logo">
                    </div>
                    <div>
                        <span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-30 rounded-pill px-3 py-1 fs-8 fw-semibold">
                            Next-Gen Freight & Fleet OS
                        </span>
                    </div>
                </div>
                <h1 class="fw-extrabold display-6 mb-3 text-white">
                    SALASIL Logistics Platform
                </h1>
                <p class="lead text-slate-300 mb-4" style="line-height: 1.6; max-width: 720px;">
                    SALASIL is an end-to-end digital logistics ecosystem designed to streamline freight transport, fleet management, and supply chain operations. By connecting shippers, individual and corporate customers, and professional truck drivers in real-time, SALASIL eliminates friction in cargo movement across regional routes.
                </p>
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <a href="{{ route('all.shipments') }}" class="btn btn-info px-4 py-2.5 rounded-3 fw-bold text-white shadow-sm d-inline-flex align-items-center gap-2">
                        <i class="bx bx-package fs-5"></i> View Active Shipments
                    </a>
                    <a href="{{ route('all.shipment.invitations') }}" class="btn btn-outline-light px-4 py-2.5 rounded-3 fw-bold d-inline-flex align-items-center gap-2">
                        <i class="bx bx-send fs-5"></i> Driver Invitations
                    </a>
                </div>
            </div>
            <div class="col-lg-4 text-center mt-4 mt-lg-0">
                <div class="p-4 rounded-4" style="background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255, 255, 255, 0.1);">
                    <i class="bx bx-shield-quarter text-cyan display-3 mb-2" style="color: #38BDF8;"></i>
                    <h5 class="fw-bold text-white mb-2">Secure & Verified</h5>
                    <p class="fs-9 text-slate-400 mb-0">Full compliance with national transport regulations, driver verification, and automated HS Code classification.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Core Pillars Grid -->
    <h4 class="fw-bold text-white mb-3"><i class="bx bx-grid-alt text-info me-2"></i>Platform Core Modules</h4>
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 mb-5">
        <!-- 1. Fleet & Driver Management -->
        <div class="col">
            <div class="card feature-card h-100 p-4">
                <div class="feature-icon-wrapper bg-cyan bg-opacity-15 text-cyan mb-3" style="background: rgba(6, 182, 212, 0.15); color: #38BDF8;">
                    <i class="bx bx-truck"></i>
                </div>
                <h5 class="fw-bold text-white mb-2">Fleet & Driver Intelligence</h5>
                <p class="fs-8 text-slate-400 mb-3" style="line-height: 1.5;">
                    Comprehensive database of truck types, sub-types, brands, models, license numbers, and verified driver profiles.
                </p>
                <div class="mt-auto">
                    <span class="fs-9 text-info font-monospace"><i class="bx bx-check-circle me-1"></i> Verification & Badging System</span>
                </div>
            </div>
        </div>

        <!-- 2. Smart Invitations & WhatsApp Sharing -->
        <div class="col">
            <div class="card feature-card h-100 p-4">
                <div class="feature-icon-wrapper bg-success bg-opacity-15 text-success mb-3" style="background: rgba(16, 185, 129, 0.15); color: #10B981;">
                    <i class="bx bx-paper-plane"></i>
                </div>
                <h5 class="fw-bold text-white mb-2">Direct Driver Magic Links</h5>
                <p class="fs-8 text-slate-400 mb-3" style="line-height: 1.5;">
                    Instant invitation links generated for drivers with direct WhatsApp integration, route visualization, pickup & delivery details, and one-click accept/decline.
                </p>
                <div class="mt-auto">
                    <span class="fs-9 text-success font-monospace"><i class="bx bx-check-circle me-1"></i> Instant WhatsApp Dispatching</span>
                </div>
            </div>
        </div>

        <!-- 3. Dynamic Route & Map Visualization -->
        <div class="col">
            <div class="card feature-card h-100 p-4">
                <div class="feature-icon-wrapper bg-warning bg-opacity-15 text-warning mb-3" style="background: rgba(245, 158, 11, 0.15); color: #F59E0B;">
                    <i class="bx bx-map-alt"></i>
                </div>
                <h5 class="fw-bold text-white mb-2">Interactive Route & Map Engine</h5>
                <p class="fs-8 text-slate-400 mb-3" style="line-height: 1.5;">
                    Real-time Leaflet map rendering with animated route paths, customized pickup & delivery markers, distance calculations, and location tracking.
                </p>
                <div class="mt-auto">
                    <span class="fs-9 text-warning font-monospace"><i class="bx bx-check-circle me-1"></i> High-precision Waypoints</span>
                </div>
            </div>
        </div>

        <!-- 4. HS Code Customs & Commodities -->
        <div class="col">
            <div class="card feature-card h-100 p-4">
                <div class="feature-icon-wrapper bg-purple bg-opacity-15 text-purple mb-3" style="background: rgba(168, 85, 247, 0.15); color: #A855F7;">
                    <i class="bx bx-barcode font-size-24"></i>
                </div>
                <h5 class="fw-bold text-white mb-2">HS Code & Cargo Tariff Matrix</h5>
                <p class="fs-8 text-slate-400 mb-3" style="line-height: 1.5;">
                    Harmonized System (HS Code) cataloging, cargo classification, customs duty compliance, and commodity specification.
                </p>
                <div class="mt-auto">
                    <span class="fs-9 text-purple font-monospace" style="color: #C084FC;"><i class="bx bx-check-circle me-1"></i> International Standards</span>
                </div>
            </div>
        </div>

        <!-- 5. Real-Time Admin Notifications -->
        <div class="col">
            <div class="card feature-card h-100 p-4">
                <div class="feature-icon-wrapper bg-danger bg-opacity-15 text-danger mb-3" style="background: rgba(244, 63, 94, 0.15); color: #F43F5E;">
                    <i class="bx bx-bell"></i>
                </div>
                <h5 class="fw-bold text-white mb-2">Live Notification Center</h5>
                <p class="fs-8 text-slate-400 mb-3" style="line-height: 1.5;">
                    Laravel-backed database notification system with real-time top navbar alerts, badge counts, sound/badge updates, and instant status tracking.
                </p>
                <div class="mt-auto">
                    <span class="fs-9 text-danger font-monospace"><i class="bx bx-check-circle me-1"></i> Instant Status Synchronization</span>
                </div>
            </div>
        </div>

        <!-- 6. Billing, Invoices & Financial Ecosystem -->
        <div class="col">
            <div class="card feature-card h-100 p-4">
                <div class="feature-icon-wrapper bg-info bg-opacity-15 text-info mb-3" style="background: rgba(14, 165, 233, 0.15); color: #38BDF8;">
                    <i class="bx bx-wallet-alt"></i>
                </div>
                <h5 class="fw-bold text-white mb-2">Invoicing & Financial Wallets</h5>
                <p class="fs-8 text-slate-400 mb-3" style="line-height: 1.5;">
                    Integrated billing, automated invoice generation, payment processing, driver wallet balances, and financial reporting.
                </p>
                <div class="mt-auto">
                    <span class="fs-9 text-info font-monospace"><i class="bx bx-check-circle me-1"></i> Automated Reconciliation</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Footer Row -->
    <div class="salasil-about-hero p-4 rounded-4 text-white">
        <div class="row align-items-center">
            <div class="col-md-4 mb-3 mb-md-0">
                <h5 class="fw-bold mb-1 text-white">SALASIL Ecosystem Stats</h5>
                <p class="fs-9 text-slate-400 mb-0">Live data directly from operational database tables.</p>
            </div>
            <div class="col-md-8">
                <div class="row g-2">
                    <div class="col-6 col-sm-3">
                        <div class="stat-pill text-center">
                            <span class="fs-9 text-slate-400 d-block">Clients</span>
                            <span class="fw-bold fs-5 text-info">{{ \App\Models\User::whereIn('role', ['individual_customer', 'company_customer'])->count() }}</span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="stat-pill text-center">
                            <span class="fs-9 text-slate-400 d-block">Drivers</span>
                            <span class="fw-bold fs-5 text-success">{{ \App\Models\User::where('role', 'driver')->count() }}</span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="stat-pill text-center">
                            <span class="fs-9 text-slate-400 d-block">Shipments</span>
                            <span class="fw-bold fs-5 text-warning">{{ \App\Models\Shipment::count() }}</span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="stat-pill text-center">
                            <span class="fs-9 text-slate-400 d-block">Truck Types</span>
                            <span class="fw-bold fs-5 text-cyan" style="color: #38BDF8;">{{ \App\Models\TruckType::count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
