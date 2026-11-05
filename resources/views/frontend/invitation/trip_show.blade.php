<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scheduled Trip Invitation #{{ $invitation->id }} - SALASIL Logistics</title>
    
    {{-- Local CSS first with CDN fallbacks --}}
    <link rel="stylesheet" href="{{ asset('backend/assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/assets/css/icons.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <style>
        :root {
            --inv-cyan:       #06B6D4;
            --inv-cyan-light: #38BDF8;
            --inv-navy:       #0F172A;
            --inv-card:       #1E293B;
            --inv-border:     rgba(255, 255, 255, 0.08);
            --inv-border-light: rgba(255, 255, 255, 0.12);
            --inv-success:    #10B981;
            --inv-warning:    #F59E0B;
            --inv-danger:     #F43F5E;
        }

        body {
            background: #090E1A;
            color: #F8FAFC;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            padding: 32px 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .inv-page-card {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.98) 0%, rgba(15, 23, 42, 0.99) 100%);
            border: 1px solid var(--inv-border);
            border-radius: 26px;
            max-width: 740px;
            width: 100%;
            overflow: hidden;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.65);
        }

        /* ─── Header ─── */
        .inv-header {
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.16) 0%, rgba(56, 189, 248, 0.08) 100%);
            border-bottom: 1px solid rgba(6, 182, 212, 0.22);
            padding: 24px 30px;
        }

        .logo-wrap {
            background: #FFFFFF;
            border-radius: 14px;
            padding: 7px 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 18px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.25);
        }
        .logo-wrap img {
            max-height: 32px;
            width: auto;
            display: block;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }
        .status-pill.pending  { background: rgba(245, 158, 11, 0.16); color: #F59E0B; border: 1.5px solid rgba(245, 158, 11, 0.4); }
        .status-pill.accepted { background: rgba(16, 185, 129, 0.16); color: #34D399; border: 1.5px solid rgba(16, 185, 129, 0.4); }
        .status-pill.rejected { background: rgba(244, 63, 94, 0.16);  color: #FB7185; border: 1.5px solid rgba(244, 63, 94, 0.4); }
        .status-pill.canceled { background: rgba(148, 163, 184, 0.16); color: #94A3B8; border: 1.5px solid rgba(148, 163, 184, 0.4); }

        .inv-body { padding: 30px; }

        /* ─── Hero Driver Card ─── */
        .driver-hero-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--inv-border-light);
            border-radius: 20px;
            padding: 20px 24px;
            margin-bottom: 24px;
        }

        .driver-id-badge {
            background: rgba(6, 182, 212, 0.15) !important;
            border: 1px solid rgba(6, 182, 212, 0.45) !important;
            color: #38BDF8 !important;
            padding: 5px 14px !important;
            border-radius: 50px !important;
            font-weight: 700 !important;
            font-size: 0.82rem !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            letter-spacing: 0.4px !important;
            white-space: nowrap !important;
        }

        .driver-phone-badge {
            background: rgba(255, 255, 255, 0.06) !important;
            border: 1px solid rgba(255, 255, 255, 0.16) !important;
            color: #E2E8F0 !important;
            padding: 5px 14px !important;
            border-radius: 50px !important;
            font-weight: 600 !important;
            font-size: 0.82rem !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            white-space: nowrap !important;
        }

        .trip-status-badge {
            background: rgba(16, 185, 129, 0.15) !important;
            border: 1px solid rgba(16, 185, 129, 0.4) !important;
            color: #34D399 !important;
            padding: 4px 12px !important;
            border-radius: 50px !important;
            font-weight: 700 !important;
            font-size: 0.82rem !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
        }

        .custom-alert-success {
            background: rgba(16, 185, 129, 0.15) !important;
            border: 1px solid rgba(16, 185, 129, 0.4) !important;
            color: #34D399 !important;
        }

        .custom-alert-danger {
            background: rgba(244, 63, 94, 0.15) !important;
            border: 1px solid rgba(244, 63, 94, 0.4) !important;
            color: #FB7185 !important;
        }

        .custom-alert-secondary {
            background: rgba(148, 163, 184, 0.15) !important;
            border: 1px solid rgba(148, 163, 184, 0.35) !important;
            color: #CBD5E1 !important;
        }

        .modal-danger-icon {
            background: rgba(244, 63, 94, 0.15) !important;
            color: #F43F5E !important;
            border: 1px solid rgba(244, 63, 94, 0.35) !important;
            padding: 8px !important;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* ─── Payout Hero Box ─── */
        .payout-hero-box {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.16) 0%, rgba(217, 119, 6, 0.08) 100%);
            border: 1.5px solid rgba(245, 158, 11, 0.4);
            border-radius: 22px;
            padding: 24px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(245, 158, 11, 0.12);
            position: relative;
            overflow: hidden;
        }
        .payout-hero-box::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, transparent, #F59E0B, transparent);
        }
        .payout-tag {
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #FCD34D;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 8px;
        }
        .payout-amount {
            display: flex;
            align-items: baseline;
            justify-content: center;
            gap: 8px;
            margin-bottom: 4px;
        }
        .payout-amount .currency {
            font-size: 1.35rem;
            font-weight: 800;
            color: #F59E0B;
        }
        .payout-amount .value {
            font-size: 2.75rem;
            font-weight: 900;
            color: #F59E0B;
            letter-spacing: -1px;
            line-height: 1;
        }
        .payout-benchmark {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(0, 0, 0, 0.25);
            padding: 4px 14px;
            border-radius: 50px;
            font-size: 0.78rem;
            color: #CBD5E1;
            margin-top: 6px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .payout-guarantee {
            font-size: 0.8rem;
            color: #94A3B8;
            margin-top: 10px;
            line-height: 1.4;
        }

        /* ─── Route Box & Timeline ─── */
        .route-box {
            background: rgba(6, 182, 212, 0.05);
            border: 1.5px solid rgba(6, 182, 212, 0.22);
            border-radius: 22px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .route-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 16px;
            margin-bottom: 18px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            flex-wrap: gap;
            gap: 12px;
        }
        .route-title {
            font-size: 0.82rem;
            font-weight: 800;
            color: var(--inv-cyan-light);
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .route-badges {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .route-badge {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #CBD5E1;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .route-badge.route-type-international {
            background: rgba(139, 92, 246, 0.15);
            border-color: rgba(139, 92, 246, 0.35);
            color: #C4B5FD;
        }
        .route-badge.route-type-local {
            background: rgba(6, 182, 212, 0.15);
            border-color: rgba(6, 182, 212, 0.35);
            color: #7DD3FC;
        }

        .route-timeline {
            display: flex;
            flex-direction: column;
            gap: 0;
        }
        .route-stop {
            display: flex;
            align-items: center;
            gap: 18px;
        }
        .stop-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .origin-icon {
            background: rgba(6, 182, 212, 0.15) !important;
            border: 1.5px solid rgba(6, 182, 212, 0.45) !important;
            box-shadow: 0 4px 18px rgba(6, 182, 212, 0.25);
        }
        .destination-icon {
            background: rgba(16, 185, 129, 0.15) !important;
            border: 1.5px solid rgba(16, 185, 129, 0.45) !important;
            box-shadow: 0 4px 18px rgba(16, 185, 129, 0.25);
        }
        .stop-details {
            flex: 1;
            min-width: 0;
        }
        .stop-label {
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            display: block;
            margin-bottom: 2px;
        }
        .origin-label      { color: #38BDF8; }
        .destination-label { color: #10B981; }

        .stop-name {
            font-size: 1.15rem;
            font-weight: 800;
            line-height: 1.25;
            color: #F8FAFC;
        }
        .country-name {
            color: #94A3B8;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .route-connector {
            padding-left: 23px;
            height: 38px;
            display: flex;
            align-items: center;
            position: relative;
        }
        .connector-line {
            position: absolute;
            left: 23px;
            top: -2px;
            bottom: -2px;
            width: 2px;
            background: linear-gradient(180deg, #06B6D4 0%, #10B981 100%);
            opacity: 0.5;
        }
        .connector-arrow {
            position: relative;
            z-index: 2;
            transform: translateX(-7px);
            background: #1E293B;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255,255,255,0.15);
        }

        /* ─── Specifications Grid Box ─── */
        .info-card-box {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .box-sec-title {
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: var(--inv-cyan-light);
            display: flex;
            align-items: center;
            gap: 8px;
            padding-bottom: 12px;
            margin-bottom: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .spec-item {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 14px;
            padding: 14px 16px;
            height: 100%;
        }
        .spec-item-label {
            font-size: 0.72rem;
            font-weight: 700;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 4px;
        }
        .spec-item-value {
            font-size: 0.95rem;
            font-weight: 700;
            color: #F8FAFC;
        }

        /* ─── Notes Callout Box ─── */
        .notes-callout {
            background: rgba(6, 182, 212, 0.06);
            border: 1px solid rgba(6, 182, 212, 0.25);
            border-radius: 16px;
            padding: 18px 20px;
            margin-bottom: 24px;
        }

        /* ─── Action Buttons ─── */
        .btn-accept {
            background: linear-gradient(135deg, #10B981, #34D399);
            color: #0F172A;
            font-weight: 800;
            font-size: 1.05rem;
            border: none;
            padding: 15px 32px;
            border-radius: 14px;
            transition: all 0.25s;
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.35);
        }
        .btn-accept:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(16, 185, 129, 0.5);
            color: #0F172A;
        }

        .btn-reject {
            background: rgba(244, 63, 94, 0.1);
            color: #F43F5E;
            font-weight: 700;
            font-size: 1rem;
            border: 1.5px solid rgba(244, 63, 94, 0.35);
            padding: 15px 32px;
            border-radius: 14px;
            transition: all 0.25s;
        }
        .btn-reject:hover {
            background: rgba(244, 63, 94, 0.2);
            border-color: #F43F5E;
            color: #FB7185;
            transform: translateY(-2px);
        }

        .support-box {
            text-align: center;
            font-size: 0.8rem;
            color: #64748B;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        /* ─── Customer / Shipper Info Box ─── */
        .customer-card-box {
            background: rgba(6, 182, 212, 0.04);
            border: 1px solid rgba(6, 182, 212, 0.22);
            border-radius: 20px;
            padding: 22px 24px;
            margin-bottom: 24px;
        }

        .customer-contact-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #38BDF8;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }
        .customer-contact-pill:hover {
            background: rgba(6, 182, 212, 0.15);
            border-color: #38BDF8;
            color: #FFFFFF;
        }

        /* ─── Station Sub-Card (Pickup & Dropoff) ─── */
        .station-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            padding: 18px 20px;
            height: 100%;
        }
        .station-card.pickup-station {
            border-left: 3px solid #06B6D4;
        }
        .station-card.dropoff-station {
            border-left: 3px solid #10B981;
        }

        /* ─── Special Equipment Badges ─── */
        .equipment-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.35);
            color: #34D399;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* ─── Document Card ─── */
        .doc-item-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            transition: all 0.2s;
        }
        .doc-item-card:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(6, 182, 212, 0.35);
        }
        .doc-view-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(6, 182, 212, 0.15);
            border: 1px solid rgba(6, 182, 212, 0.4);
            color: #38BDF8;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .doc-view-btn:hover {
            background: #06B6D4;
            color: #0F172A;
        }

        /* ─── Fragile Alert Badge ─── */
        .fragile-badge-warning {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(245, 158, 11, 0.15);
            border: 1.5px solid rgba(245, 158, 11, 0.45);
            color: #FCD34D;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .map-quick-link {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: rgba(6, 182, 212, 0.12);
            border: 1px solid rgba(6, 182, 212, 0.3);
            color: #38BDF8;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }
        .map-quick-link:hover {
            background: #06B6D4;
            color: #0F172A;
        }
    </style>
</head>
<body>

<div class="inv-page-card">

    {{-- ════ 1. TOP HEADER ════ --}}
    <div class="inv-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="logo-wrap">
                <img src="{{ asset('backend/assets/images/salasil-logo.svg') }}" alt="SALASIL Logo">
            </div>
            <div>
                <h5 class="fw-bold mb-0 text-white">Scheduled Trip Invitation</h5>
                <small style="color: #94A3B8;">SALASIL Logistics Platform</small>
            </div>
        </div>

        <div>
            @if($invitation->status === 'accepted')
                <span class="status-pill accepted">✓ Accepted</span>
            @elseif($invitation->status === 'rejected')
                <span class="status-pill rejected">✕ Declined</span>
            @elseif($invitation->status === 'canceled')
                <span class="status-pill canceled">⊘ Canceled</span>
            @else
                <span class="status-pill pending">● Pending Response</span>
            @endif
        </div>
    </div>

    <div class="inv-body">

        {{-- ════ FLASH ALERTS ════ --}}
        @if(session('success_message'))
            <div class="alert custom-alert-success fw-bold rounded-4 mb-4 p-3 d-flex align-items-center gap-2">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <span>{{ session('success_message') }}</span>
            </div>
        @endif

        @if($invitation->status === 'accepted')
            <div class="alert custom-alert-success fw-semibold rounded-4 mb-4 p-3 d-flex align-items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span>You have accepted this scheduled trip invitation. Thank you! Operations will contact you for dispatch.</span>
            </div>
        @elseif($invitation->status === 'rejected')
            <div class="alert custom-alert-danger fw-semibold rounded-4 mb-4 p-3 d-flex align-items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
                <span>You declined this invitation. Reason: {{ $invitation->rejection_reason ?: 'No reason specified' }}</span>
            </div>
        @elseif($invitation->status === 'canceled')
            <div class="alert custom-alert-secondary fw-semibold rounded-4 mb-4 p-3 d-flex align-items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
                </svg>
                <span>This invitation was canceled by the administrator.</span>
            </div>
        @endif

        {{-- ════ 2. DRIVER HERO GREETING CARD ════ --}}
        <div class="driver-hero-card">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <h4 class="fw-bold text-white mb-0">
                    Hello, {{ $invitation->driver->fname ?? 'Driver' }} {{ $invitation->driver->lname ?? '' }} 👋
                </h4>
                <div class="d-flex align-items-center gap-2">
                    <span class="driver-id-badge">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        ID: {{ $invitation->driver->client_code ?: ('#' . $invitation->driver->id) }}
                    </span>
                    <span class="driver-phone-badge">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                        {{ $invitation->driver->phone }}
                    </span>
                </div>
            </div>
            <p style="color: #94A3B8; font-size: 0.93rem; margin-bottom: 0; line-height: 1.5;">
                You have been selected and invited by SALASIL Operations to haul this scheduled freight journey. Please inspect the comprehensive route, schedule, cargo, and payout details below to decide on your response.
            </p>
        </div>

        @php
            $trip = $invitation->scheduledTrip;
            $order = $invitation->scheduledTripOrder;

            $originCity = $order->pickupCity->name_en ?? ($trip->route->originCity->name_en ?? 'Origin City');
            $originCountry = $order->pickupCountry->name_en ?? ($trip->route->originCountry->name_en ?? '');
            $destCity = $order->dropoffCity->name_en ?? ($trip->route->destinationCity->name_en ?? 'Destination City');
            $destCountry = $order->dropoffCountry->name_en ?? ($trip->route->destinationCountry->name_en ?? '');
            $isInternational = ($trip->route->quote_type ?? '') === 'international';
            $distanceKm = $trip->route->estimated_distance ?? 0;
            $approxHoursMin = $distanceKm > 0 ? round($distanceKm / 75) : 0;
            $approxHoursMax = $distanceKm > 0 ? round($distanceKm / 60) : 0;
        @endphp

        {{-- ════ 3. FINANCIAL COMPENSATION HERO BOX ════ --}}
        <div class="payout-hero-box mb-4">
            <div class="payout-tag">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2.5">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
                <span>OFFERED DRIVER COMPENSATION</span>
            </div>

            <div class="payout-amount">
                <span class="currency">KWD</span>
                <span class="value">{{ number_format($invitation->offered_price, 2) }}</span>
            </div>

            <div class="d-flex justify-content-center flex-wrap gap-2 my-1">
                <span class="payout-benchmark text-success border-success border-opacity-25">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <span>Net Driver Payout</span>
                </span>
            </div>

            <div class="payout-guarantee">
                Guaranteed direct payment processed to your driver wallet upon successful arrival, unloading, and delivery sign-off.
            </div>
        </div>

        {{-- ════ 4. CUSTOMER & SHIPPER DETAILS ════ --}}
        @if($order && $order->customer)
            <div class="customer-card-box">
                <div class="box-sec-title d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2.2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span>Customer & Shipper Details</span>
                    </div>
                    @if($order->customer->client_code)
                        <span class="badge" style="background: rgba(6, 182, 212, 0.15); color: #38BDF8; border: 1px solid rgba(6, 182, 212, 0.35);">
                            Client: {{ $order->customer->client_code }}
                        </span>
                    @endif
                </div>

                <div class="row g-3 align-items-center">
                    <div class="col-12 col-md-6">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 46px; height: 46px; border-radius: 14px; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.3); display: flex; align-items: center; justify-content: center; color: #38BDF8; font-weight: 800; font-size: 1.15rem;">
                                {{ strtoupper(substr($order->customer->fname ?? 'C', 0, 1)) }}
                            </div>
                            <div>
                                <h5 class="fw-bold text-white mb-0">
                                    {{ $order->customer->fname }} {{ $order->customer->lname }}
                                </h5>
                                @if($order->customer->companyProfile && $order->customer->companyProfile->company_name)
                                    <div class="text-info fs-8 fw-semibold">
                                        <i class="bx bx-buildings me-1"></i>{{ $order->customer->companyProfile->company_name }}
                                    </div>
                                @endif
                                <div class="text-slate-400 fs-9 text-capitalize">
                                    {{ str_replace('_', ' ', $order->customer->role ?? 'Customer') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 d-flex align-items-center justify-content-md-end flex-wrap gap-2">
                        @if($order->customer->phone)
                            <a href="tel:{{ $order->customer->phone }}" class="customer-contact-pill">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                                <span>{{ $order->customer->phone }}</span>
                            </a>
                        @endif
                        @if($order->customer->email)
                            <a href="mailto:{{ $order->customer->email }}" class="customer-contact-pill">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                                <span>{{ $order->customer->email }}</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- ════ 5. TRIP ROUTE TIMELINE & SUMMARY ════ --}}
        <div class="route-box mb-4">
            <div class="route-header">
                <div class="d-flex align-items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="6" cy="19" r="3"></circle>
                        <path d="M9 19h8.5a3.5 3.5 0 0 0 0-7h-11a3.5 3.5 0 0 1 0-7H15"></path>
                        <circle cx="18" cy="5" r="3"></circle>
                    </svg>
                    <span class="route-title">Trip Route & Distance</span>
                </div>

                <div class="route-badges">
                    @if($order)
                        <span class="route-badge" style="background: rgba(6, 182, 212, 0.2); color: #38BDF8; border-color: rgba(6, 182, 212, 0.4);">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                <line x1="6" y1="12" x2="18" y2="12"></line>
                            </svg>
                            <span>{{ $order->order_number ?: ('#STO-' . $order->id) }}</span>
                        </span>
                    @endif
                    @if($isInternational)
                        <span class="route-badge route-type-international">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="2" y1="12" x2="22" y2="12"></line>
                                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                            </svg>
                            <span>International Cross-Border</span>
                        </span>
                    @else
                        <span class="route-badge route-type-local">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="1" y="3" width="15" height="13"></rect>
                                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                <circle cx="18.5" cy="18.5" r="2.5"></circle>
                            </svg>
                            <span>Domestic Route</span>
                        </span>
                    @endif

                    @if($distanceKm > 0)
                        <span class="route-badge">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <span>{{ number_format($distanceKm, 1) }} km</span>
                        </span>
                    @endif

                    @if($approxHoursMin > 0)
                        <span class="route-badge">
                            <span>Est. ~{{ $approxHoursMin }}-{{ $approxHoursMax }} hrs</span>
                        </span>
                    @endif
                </div>
            </div>

            <div class="route-timeline">
                {{-- Origin Stop --}}
                <div class="route-stop">
                    <div class="stop-icon origin-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </div>
                    <div class="stop-details">
                        <span class="stop-label origin-label">ORIGIN / PICKUP POINT</span>
                        <h4 class="stop-name text-white mb-0">
                            {{ $originCity }} 
                            @if($originCountry)
                                <span class="country-name">({{ $originCountry }})</span>
                            @endif
                        </h4>
                        @if($order && $order->pickup_address)
                            <div class="mt-1 text-slate-300 fs-8 d-flex align-items-center gap-1">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                <span>{{ $order->pickup_address }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Connector Line --}}
                <div class="route-connector">
                    <div class="connector-line"></div>
                    <div class="connector-arrow">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                </div>

                {{-- Destination Stop --}}
                <div class="route-stop">
                    <div class="stop-icon destination-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="3 11 22 2 13 21 11 13 3 11"></polygon>
                        </svg>
                    </div>
                    <div class="stop-details">
                        <span class="stop-label destination-label">DESTINATION / DELIVERY POINT</span>
                        <h4 class="stop-name text-white mb-0">
                            {{ $destCity }} 
                            @if($destCountry)
                                <span class="country-name">({{ $destCountry }})</span>
                            @endif
                        </h4>
                        @if($order && $order->dropoff_address)
                            <div class="mt-1 text-slate-300 fs-8 d-flex align-items-center gap-1">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2"><polygon points="3 11 22 2 13 21 11 13 3 11"></polygon></svg>
                                <span>{{ $order->dropoff_address }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ════ 6. DETAILED PICKUP & DELIVERY STATIONS ════ --}}
        <div class="row g-3 mb-4">
            {{-- 1. Loading Station Card --}}
            <div class="col-12 col-md-6">
                <div class="station-card pickup-station">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-bold text-info fs-8 text-uppercase" style="letter-spacing: 0.6px;">
                            <i class="bx bx-map-pin me-1"></i>1. Loading & Pickup Station
                        </span>
                        @php
                            $pickupLat = $order->pickup_lat ?? null;
                            $pickupLng = $order->pickup_lng ?? null;
                            $pickupQuery = ($pickupLat && $pickupLng) ? "{$pickupLat},{$pickupLng}" : urlencode(($order->pickup_address ?? '') . ' ' . $originCity . ' ' . $originCountry);
                        @endphp
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $pickupQuery }}" target="_blank" class="map-quick-link">
                            <i class="bx bx-directions"></i> Maps
                        </a>
                    </div>

                    <h5 class="text-white fw-bold mb-1">
                        {{ $originCity }} 
                        @if($originCountry)
                            <span class="text-slate-400 font-normal fs-7">({{ $originCountry }})</span>
                        @endif
                    </h5>

                    @if($order && ($order->pickup_address || $order->pickup_area))
                        <div class="text-slate-300 fs-8 mb-2 d-flex align-items-start gap-1">
                            <i class="bx bx-current-location text-info mt-1"></i>
                            <span>{{ $order->pickup_address }}{{ $order->pickup_area ? ' (' . $order->pickup_area . ')' : '' }}</span>
                        </div>
                    @endif

                    <div class="p-2.5 rounded-3 mb-2" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                        <div class="d-flex align-items-center justify-content-between fs-8">
                            <span class="text-slate-400">Loading Date:</span>
                            <span class="text-warning fw-bold">
                                {{ $order && $order->loading_date ? date('D, M j, Y', strtotime($order->loading_date)) : ($trip->trip_date ? date('D, M j, Y', strtotime($trip->trip_date)) : 'Flexible') }}
                            </span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between fs-8 mt-1">
                            <span class="text-slate-400">Loading Time:</span>
                            <span class="text-white fw-semibold">
                                {{ $order && $order->loading_time ? $order->loading_time : ($trip->trip_time ? date('h:i A', strtotime($trip->trip_time)) : 'Open Window') }}
                                @if($order && $order->loading_time_slot)
                                    <span class="text-info">({{ $order->loading_time_slot }})</span>
                                @endif
                            </span>
                        </div>
                    </div>

                    @if($order && ($order->loading_contact_name || $order->loading_contact_phone))
                        <div class="d-flex align-items-center justify-content-between fs-8 pt-2 border-top border-secondary border-opacity-25">
                            <div>
                                <span class="text-slate-400 d-block fs-9">Loading Contact:</span>
                                <strong class="text-white">{{ $order->loading_contact_name ?: 'On-Site Staff' }}</strong>
                            </div>
                            @if($order->loading_contact_phone)
                                <a href="tel:{{ $order->loading_contact_phone }}" class="customer-contact-pill py-1 px-2.5 fs-9">
                                    <i class="bx bx-phone-call"></i> {{ $order->loading_contact_phone }}
                                </a>
                            @endif
                        </div>
                    @endif

                    @if($order && $order->pickup_instructions)
                        <div class="mt-2 text-slate-300 fs-9 p-2 rounded-2" style="background: rgba(6,182,212,0.06);">
                            <strong class="text-info">Instructions:</strong> {{ $order->pickup_instructions }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- 2. Delivery Station Card --}}
            <div class="col-12 col-md-6">
                <div class="station-card dropoff-station">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-bold text-success fs-8 text-uppercase" style="letter-spacing: 0.6px;">
                            <i class="bx bx-check-shield me-1"></i>2. Delivery & Unloading Station
                        </span>
                        @php
                            $dropoffLat = $order->dropoff_lat ?? null;
                            $dropoffLng = $order->dropoff_lng ?? null;
                            $dropoffQuery = ($dropoffLat && $dropoffLng) ? "{$dropoffLat},{$dropoffLng}" : urlencode(($order->dropoff_address ?? '') . ' ' . $destCity . ' ' . $destCountry);
                        @endphp
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $dropoffQuery }}" target="_blank" class="map-quick-link" style="border-color: rgba(16,185,129,0.3); color:#34D399; background: rgba(16,185,129,0.12);">
                            <i class="bx bx-directions"></i> Maps
                        </a>
                    </div>

                    <h5 class="text-white fw-bold mb-1">
                        {{ $destCity }} 
                        @if($destCountry)
                            <span class="text-slate-400 font-normal fs-7">({{ $destCountry }})</span>
                        @endif
                    </h5>

                    @if($order && ($order->dropoff_address || $order->dropoff_area))
                        <div class="text-slate-300 fs-8 mb-2 d-flex align-items-start gap-1">
                            <i class="bx bx-current-location text-success mt-1"></i>
                            <span>{{ $order->dropoff_address }}{{ $order->dropoff_area ? ' (' . $order->dropoff_area . ')' : '' }}</span>
                        </div>
                    @endif

                    <div class="p-2.5 rounded-3 mb-2" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                        <div class="d-flex align-items-center justify-content-between fs-8">
                            <span class="text-slate-400">Receiving Date:</span>
                            <span class="text-success fw-bold">
                                {{ $order && $order->receiving_date ? date('D, M j, Y', strtotime($order->receiving_date)) : 'Upon Arrival' }}
                            </span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between fs-8 mt-1">
                            <span class="text-slate-400">Receiving Time:</span>
                            <span class="text-white fw-semibold">
                                {{ $order && $order->receiving_time ? $order->receiving_time : 'Standard Hours' }}
                                @if($order && $order->receiving_time_slot)
                                    <span class="text-success">({{ $order->receiving_time_slot }})</span>
                                @endif
                            </span>
                        </div>
                    </div>

                    @if($order && ($order->arrival_contact_name || $order->arrival_contact_phone))
                        <div class="d-flex align-items-center justify-content-between fs-8 pt-2 border-top border-secondary border-opacity-25">
                            <div>
                                <span class="text-slate-400 d-block fs-9">Receiving Contact:</span>
                                <strong class="text-white">{{ $order->arrival_contact_name ?: 'Receiving Manager' }}</strong>
                            </div>
                            @if($order->arrival_contact_phone)
                                <a href="tel:{{ $order->arrival_contact_phone }}" class="customer-contact-pill py-1 px-2.5 fs-9" style="color: #34D399; border-color: rgba(16,185,129,0.3); background: rgba(16,185,129,0.1);">
                                    <i class="bx bx-phone-call"></i> {{ $order->arrival_contact_phone }}
                                </a>
                            @endif
                        </div>
                    @endif

                    @if($order && $order->delivery_instructions)
                        <div class="mt-2 text-slate-300 fs-9 p-2 rounded-2" style="background: rgba(16,185,129,0.06);">
                            <strong class="text-success">Instructions:</strong> {{ $order->delivery_instructions }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ════ 7. CARGO SPECIFICATIONS & FREIGHT DETAILS ════ --}}
        <div class="info-card-box">
            <div class="box-sec-title d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                    <span>Cargo Specifications & Freight Details</span>
                </div>
                @if($order && $order->is_fragile)
                    <span class="fragile-badge-warning">
                        <i class="bx bx-error-circle"></i> Fragile Cargo &bull; Handle With Care
                    </span>
                @endif
            </div>

            <div class="row g-3">
                <div class="col-12 col-sm-6">
                    <div class="spec-item">
                        <span class="spec-item-label">Shipment Name / Commodity</span>
                        <div class="spec-item-value text-white">
                            {{ $order->shipment_name ?? ($trip->shipmentType->name_en ?? 'Scheduled Freight') }}
                        </div>
                        @if($order && $order->goods_description)
                            <div class="text-slate-400 fs-9 mt-1">{{ $order->goods_description }}</div>
                        @endif
                    </div>
                </div>

                <div class="col-6 col-sm-3">
                    <div class="spec-item">
                        <span class="spec-item-label">Shipment Category</span>
                        <div class="spec-item-value text-white">
                            {{ ($order->shipmentType->name_en ?? null) ?: ($trip->shipmentType->name_en ?? 'General Cargo') }}
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-3">
                    <div class="spec-item">
                        <span class="spec-item-label">Cargo Nature</span>
                        <div class="spec-item-value text-white">
                            {{ ($order->shipmentNature->name_en ?? null) ?: ($trip->shipmentNature->name_en ?? 'Standard') }}
                        </div>
                    </div>
                </div>

                {{-- Dimensions --}}
                <div class="col-12 col-sm-6">
                    <div class="spec-item">
                        <span class="spec-item-label">Dimensions (L × W × H)</span>
                        <div class="spec-item-value text-info font-monospace">
                            @if($order && ($order->length || $order->width || $order->height))
                                {{ number_format($order->length ?? 0, 2) }}m × {{ number_format($order->width ?? 0, 2) }}m × {{ number_format($order->height ?? 0, 2) }}m
                                @php
                                    $volume = ($order->length ?? 0) * ($order->width ?? 0) * ($order->height ?? 0);
                                @endphp
                                @if($volume > 0)
                                    <span class="text-slate-400 font-sans fs-8">({{ number_format($volume, 2) }} m³)</span>
                                @endif
                            @else
                                Standard Full Container / Truckload
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Weight --}}
                <div class="col-6 col-sm-3">
                    <div class="spec-item">
                        <span class="spec-item-label">Total Weight</span>
                        <div class="spec-item-value text-white font-monospace">
                            @if($order && $order->weight)
                                {{ number_format($order->weight, 2) }} kg
                            @elseif($trip->total_weight_ton)
                                {{ number_format($trip->total_weight_ton, 2) }} Tons
                            @else
                                As per manifest
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Packages --}}
                <div class="col-6 col-sm-3">
                    <div class="spec-item">
                        <span class="spec-item-label">Packages Count</span>
                        <div class="spec-item-value text-white font-monospace">
                            {{ $order->packages_count ?? 1 }} Unit(s)
                        </div>
                    </div>
                </div>

                @if($order && ($order->hs_code || $order->hs_code_description))
                    <div class="col-12">
                        <div class="spec-item" style="background: rgba(255,255,255,0.015);">
                            <span class="spec-item-label">Customs HS Code</span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-secondary font-monospace">{{ $order->hs_code }}</span>
                                <span class="text-slate-300 fs-8">{{ $order->hs_code_description }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ════ 8. TRUCK & CAPACITY REQUIREMENTS ════ --}}
        <div class="info-card-box">
            <div class="box-sec-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2">
                    <rect x="1" y="3" width="15" height="13"></rect>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                </svg>
                <span>Truck & Capacity Requirements</span>
            </div>

            <div class="row g-3">
                <div class="col-12 col-sm-6">
                    <div class="spec-item">
                        <span class="spec-item-label">Required Truck Type</span>
                        <div class="spec-item-value text-white">
                            {{ ($order->truckType->name_en ?? null) ?: ($trip->truckType->name_en ?? 'Standard Trailer') }}
                            @if(($order->truckSubType ?? null) || ($trip->truckSubType ?? null))
                                <span class="text-info fs-8 fw-normal">({{ ($order->truckSubType->name_en ?? null) ?: $trip->truckSubType->name_en }})</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6">
                    <div class="spec-item">
                        <span class="spec-item-label">Fleet Quantity / Capacity Booked</span>
                        <div class="spec-item-value text-white">
                            @if($order && ($order->number_of_trucks || $order->booked_capacity))
                                {{ $order->number_of_trucks ?: $order->booked_capacity }} Truck(s) / Unit(s) Booked
                            @else
                                {{ $trip->number_of_trucks ?? 1 }} Truck(s) Needed
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Special Equipment --}}
                @php
                    $specialEquip = ($order && is_array($order->special_equipment)) ? $order->special_equipment : (($trip && is_array($trip->special_equipment)) ? $trip->special_equipment : []);
                @endphp
                @if(count($specialEquip) > 0)
                    <div class="col-12">
                        <div class="spec-item" style="background: rgba(16, 185, 129, 0.04); border-color: rgba(16, 185, 129, 0.2);">
                            <span class="spec-item-label text-success">Required Special Equipment</span>
                            <div class="d-flex flex-wrap gap-2 mt-1">
                                @foreach($specialEquip as $equip)
                                    <span class="equipment-chip">
                                        <i class="bx bx-check-circle"></i> {{ $equip }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Driver Assigned Truck --}}
                @if($invitation->driverTruck)
                    <div class="col-12">
                        <div class="spec-item" style="background: rgba(6, 182, 212, 0.08); border-color: rgba(6, 182, 212, 0.25);">
                            <span class="spec-item-label text-info">Your Designated Truck for this Trip</span>
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="text-white fw-bold">
                                    <i class="bx bx-truck me-1 text-info"></i>
                                    {{ $invitation->driverTruck->truckType->name_en ?? 'Truck' }}
                                    @if($invitation->driverTruck->truckBrand)
                                        — {{ $invitation->driverTruck->truckBrand->name_en }} {{ $invitation->driverTruck->truckModel->name_en ?? '' }}
                                    @endif
                                    @if($invitation->driverTruck->manufacturing_year)
                                        <span class="text-slate-400 font-normal">({{ $invitation->driverTruck->manufacturing_year }})</span>
                                    @endif
                                </div>
                                <span class="badge bg-dark border border-secondary text-white px-2.5 py-1 font-monospace">
                                    Plate: {{ $invitation->driverTruck->plate_number }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif

                @if($order && $order->truck_notes)
                    <div class="col-12">
                        <div class="spec-item">
                            <span class="spec-item-label">Truck & Loading Notes</span>
                            <div class="text-white fs-8">{{ $order->truck_notes }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ════ 9. SHIPPING DOCUMENTS & DRIVER COMPLIANCE ════ --}}
        <div class="info-card-box">
            <div class="box-sec-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
                <span>Shipping Documents & Cargo Clearance</span>
            </div>

            @php
                $orderDocs = ($order && is_array($order->documents)) ? $order->documents : [];
            @endphp

            @if(count($orderDocs) > 0)
                <div class="mb-4">
                    <span class="spec-item-label mb-2">Customer-Uploaded Shipment Documents:</span>
                    <div class="d-flex flex-column gap-2">
                        @foreach($orderDocs as $doc)
                            @php
                                $docTitle = $doc['title'] ?? 'Shipment Document';
                                $docUrl = $doc['file_url'] ?? asset($doc['file_path'] ?? '');
                                $docSize = $doc['file_size'] ?? '';
                                $docType = strtoupper($doc['file_type'] ?? 'FILE');
                            @endphp
                            <div class="doc-item-card">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(56, 189, 248, 0.12); border: 1px solid rgba(56, 189, 248, 0.25); display: flex; align-items: center; justify-content: center; color: #38BDF8; font-size: 1.2rem;">
                                        <i class="bx bxs-file-doc"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-white fw-bold mb-0 fs-8">{{ $docTitle }}</h6>
                                        <div class="text-slate-400 fs-9">
                                            <span class="badge bg-secondary me-1 py-0 px-1 font-monospace">{{ $docType }}</span>
                                            {{ $docSize ? $docSize . ' • ' : '' }} Attached to Order
                                        </div>
                                    </div>
                                </div>
                                <a href="{{ $docUrl }}" target="_blank" class="doc-view-btn">
                                    <i class="bx bx-show"></i> View Document
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <span class="spec-item-label mb-2">Required Driver Credentials & Transit Permits:</span>
            <div class="row g-2">
                <div class="col-12 col-sm-6">
                    <div class="d-flex align-items-center gap-2 p-2.5 rounded-3 border" style="background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.08) !important;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span class="text-white fs-8">Valid Heavy Driving License & Civil ID</span>
                    </div>
                </div>
                <div class="col-12 col-sm-6">
                    <div class="d-flex align-items-center gap-2 p-2.5 rounded-3 border" style="background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.08) !important;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span class="text-white fs-8">Truck Registration (Mulkiya) & Insurance</span>
                    </div>
                </div>
                @if($isInternational)
                    <div class="col-12 col-sm-6">
                        <div class="d-flex align-items-center gap-2 p-2.5 rounded-3 border" style="background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.08) !important;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <span class="text-white fs-8">Valid Passport & GCC Cross-Border Transit Visa</span>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="d-flex align-items-center gap-2 p-2.5 rounded-3 border" style="background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.08) !important;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <span class="text-white fs-8">Customs Clearance & Bayan Manifest (Provided)</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ════ 10. CUSTOMS CLEARANCE & TRANSIT PROTOCOL ════ --}}
        <div class="info-card-box">
            <div class="box-sec-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
                <span>Customs Clearance & Transit Protocol</span>
            </div>

            <div class="row g-3">
                <div class="col-12 col-sm-6">
                    <div class="spec-item">
                        <span class="spec-item-label">Customs Clearance Support</span>
                        <div class="spec-item-value">
                            @if(($order && $order->customs_clearance) || ($trip && $trip->customs_clearance))
                                <span class="text-success fw-bold d-flex align-items-center gap-1.5">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg>
                                    <span>Included — Handled by SALASIL Customs Brokerage</span>
                                </span>
                            @else
                                <span class="text-slate-300">Standard Domestic / Direct Transit</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6">
                    <div class="spec-item">
                        <span class="spec-item-label">Border Transit Status</span>
                        <div class="spec-item-value text-white">
                            @if($isInternational)
                                <span class="text-info fw-semibold d-flex align-items-center gap-1">
                                    <i class="bx bx-globe"></i> Cross-Border Transit Manifest Provided
                                </span>
                            @else
                                <span class="text-slate-300">Domestic Freight Clearance</span>
                            @endif
                        </div>
                    </div>
                </div>

                @php
                    $allNotes = trim(($order->notes ?? '') . ($order && $order->notes && $trip->notes ? "\n" : '') . ($trip->notes ?? ''));
                @endphp
                @if(!empty($allNotes))
                    <div class="col-12">
                        <div class="notes-callout mb-0">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2.5">
                                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                                </svg>
                                <span class="fw-bold text-info fs-8 text-uppercase" style="letter-spacing: 0.6px;">Special Instructions & Order Notes</span>
                            </div>
                            <div class="text-white fs-7" style="line-height: 1.6; white-space: pre-wrap;">{{ $allNotes }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ════ 10. ACTION BUTTONS (FOR PENDING INVITATIONS) ════ --}}
        @if($invitation->status === 'pending')
            <div class="pt-2">
                <form method="POST" action="{{ route('driver.trip.invitation.respond', $invitation->token) }}" id="respondTripForm">
                    @csrf
                    <input type="hidden" name="action" id="actionInput" value="accept">

                    <div class="d-flex flex-column flex-sm-row gap-3">
                        <button type="button" class="btn btn-accept flex-fill d-inline-flex align-items-center justify-content-center gap-2" onclick="submitTripResponse('accept')">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0F172A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <span>Accept Trip (KWD {{ number_format($invitation->offered_price, 2) }})</span>
                        </button>

                        <button type="button" class="btn btn-reject flex-fill d-inline-flex align-items-center justify-content-center gap-2" onclick="openRejectDialog()">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#F43F5E" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                            <span>Decline Invitation</span>
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- ════ 11. DISPATCH SUPPORT & FOOTER ════ --}}
        <div class="support-box">
            <div class="mb-1 text-slate-400">
                Have questions regarding border transit, loading times, or route permits before accepting?
            </div>
            <div>
                Contact SALASIL Operations 24/7 Hotline: <strong class="text-info">+965 2220 0000</strong> &bull; support@salasil.com
            </div>
            <div class="mt-3 text-slate-500 fs-9">
                SALASIL Logistics Platform &copy; 2026. All rights reserved.
            </div>
        </div>

    </div>
</div>

{{-- ════ DECLINE DIALOG MODAL ════ --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#1E293B; border:1.5px solid rgba(244,63,94,0.35); border-radius:22px; color:#F8FAFC; box-shadow: 0 25px 60px rgba(0,0,0,0.65);">
            <div class="modal-header border-bottom border-secondary border-opacity-25 px-4 py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="modal-danger-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#F43F5E" stroke-width="2.5">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <h5 class="modal-title fw-bold text-white mb-0">Decline Scheduled Trip</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('driver.trip.invitation.respond', $invitation->token) }}">
                @csrf
                <input type="hidden" name="action" value="reject">
                <div class="modal-body p-4">
                    <p class="text-slate-300 fs-7 mb-3">
                        Please specify the reason why you are declining this scheduled trip invitation:
                    </p>
                    <textarea name="rejection_reason" class="form-control" rows="3" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:#F8FAFC; border-radius:12px; padding:12px;" placeholder="e.g. Schedule conflict, truck undergoing maintenance, out of service area..." required></textarea>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-3 px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-3 px-4 fw-bold">Confirm Decline</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function submitTripResponse(action) {
    if (confirm("Are you sure you want to accept this scheduled trip invitation?")) {
        document.getElementById('actionInput').value = action;
        document.getElementById('respondTripForm').submit();
    }
}
function openRejectDialog() {
    var modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}
</script>
</body>
</html>
