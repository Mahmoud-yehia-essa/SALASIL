<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipment Invitation #{{ $invitation->id }} - SALASIL Logistics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        :root {
            --inv-cyan: #06B6D4;
            --inv-cyan-light: #38BDF8;
            --inv-navy: #0F172A;
            --inv-card: #1E293B;
            --inv-border: rgba(255, 255, 255, 0.08);
            --inv-success: #10B981;
            --inv-warning: #F59E0B;
            --inv-danger: #F43F5E;
        }

        body {
            background: #0F172A;
            color: #F8FAFC;
            font-family: system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            padding: 24px 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .inv-page-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%);
            border: 1px solid var(--inv-border);
            border-radius: 24px;
            max-width: 680px;
            width: 100%;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5);
        }

        .inv-header {
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.15) 0%, rgba(56, 189, 248, 0.08) 100%);
            border-bottom: 1px solid rgba(6, 182, 212, 0.2);
            padding: 24px 28px;
        }

        .inv-body {
            padding: 28px;
        }

        .spec-box {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .route-card {
            background: rgba(6, 182, 212, 0.06);
            border: 1px solid rgba(6, 182, 212, 0.2);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .btn-accept {
            background: linear-gradient(135deg, #10B981, #34D399);
            color: #0F172A;
            font-weight: 800;
            font-size: 1.05rem;
            border: none;
            padding: 14px 28px;
            border-radius: 14px;
            transition: all 0.25s;
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
        }

        .btn-accept:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(16, 185, 129, 0.45);
            color: #0F172A;
        }

        .btn-reject {
            background: rgba(244, 63, 94, 0.1);
            color: #F43F5E;
            font-weight: 700;
            border: 1px solid rgba(244, 63, 94, 0.3);
            padding: 14px 28px;
            border-radius: 14px;
            transition: all 0.25s;
        }

        .btn-reject:hover {
            background: rgba(244, 63, 94, 0.2);
            border-color: #F43F5E;
            color: #F43F5E;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 800;
            text-transform: uppercase;
        }
        .status-pill.pending { background: rgba(245, 158, 11, 0.18); color: #F59E0B; border: 1px solid rgba(245, 158, 11, 0.4); }
        .status-pill.accepted { background: rgba(16, 185, 129, 0.18); color: #34D399; border: 1px solid rgba(16, 185, 129, 0.4); }
        .status-pill.rejected { background: rgba(244, 63, 94, 0.18); color: #F43F5E; border: 1px solid rgba(244, 63, 94, 0.4); }
    </style>
</head>
<body>

    <div class="inv-page-card">
        {{-- Header --}}
        <div class="inv-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-white px-2.5 py-1.5 shadow-sm d-flex align-items-center justify-content-center" style="height: 46px; border: 1px solid rgba(255,255,255,0.2);">
                    <img src="{{ asset('backend/assets/images/salasil-logo.svg') }}" style="max-height: 34px; width: auto;" alt="SALASIL Logo">
                </div>
                <div>
                    <h5 class="fw-bold text-white mb-0">SALASIL Logistics Platform</h5>
                    <small style="color: #94A3B8;">Shipment Invitation #{{ $invitation->id }}</small>
                </div>
            </div>
            <div>
                <span class="status-pill {{ $invitation->status }}">
                    <i class="bx bx-circle me-1"></i> {{ strtoupper($invitation->status) }}
                </span>
            </div>
        </div>

        {{-- Body --}}
        <div class="inv-body">
            @if(session('success_message'))
                <div class="alert alert-success border-0 rounded-4 p-3 mb-4 d-flex align-items-center gap-2" style="background: rgba(16, 185, 129, 0.15); color: #34D399; border: 1px solid rgba(16, 185, 129, 0.3) !important;">
                    <i class="bx bx-check-circle fs-4 me-1"></i>
                    <span>{{ session('success_message') }}</span>
                </div>
            @endif

            <!-- Driver Greeting -->
            <div class="mb-4">
                <h4 class="fw-bold text-white mb-1">
                    Hello, {{ $invitation->driver->fname ?? 'Driver' }} {{ $invitation->driver->lname ?? '' }} 👋
                </h4>
                <p style="color: #94A3B8; font-size: 0.95rem;">
                    You have been invited to carry out the following shipment order. Please inspect details below and confirm your response.
                </p>
            </div>

            <!-- Detailed Pickup & Delivery Locations -->
            <div class="spec-box mb-3">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom border-secondary border-opacity-25 pb-2 flex-wrap gap-2">
                    <h6 class="fw-bold mb-0 d-flex align-items-center gap-2 fs-7 text-uppercase" style="letter-spacing: 0.5px; color: #38BDF8 !important;">
                        <i class="bx bx-map-pin text-info fs-5"></i> Pickup & Delivery Locations
                    </h6>
                    <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill fs-6" style="box-shadow: 0 4px 15px rgba(245,158,11,0.3);">
                        <i class="bx bx-money me-1"></i> Offered Payout: KWD {{ number_format($invitation->offered_price ?: ($invitation->shipment->initial_price ?? 0), 2) }}
                    </span>
                </div>

                <div class="row g-3">
                    {{-- Pickup Location Column --}}
                    <div class="col-12 col-md-6 border-end-md border-secondary border-opacity-25 pe-md-3">
                        <h6 class="fw-bold text-white mb-3 fs-7 d-flex align-items-center gap-1.5">
                            <i class="bx bx-anchor text-info fs-5"></i> Pickup Location
                        </h6>

                        <div class="mb-2.5">
                            <small class="d-block text-uppercase fw-bold" style="color: #64748B; font-size: 0.75rem;">Country / City</small>
                            <span class="text-white fw-bold fs-7 d-block">
                                {{ $invitation->shipment->pickupCountry->name_en ?? ($invitation->shipment->pickupCountry->name ?? 'Kuwait') }} / 
                                {{ $invitation->shipment->pickupCity->name_en ?? ($invitation->shipment->pickupCity->name ?? '—') }}
                            </span>
                        </div>

                        <div class="mb-2.5">
                            <small class="d-block text-uppercase fw-bold" style="color: #64748B; font-size: 0.75rem;">Area</small>
                            <span class="text-white fw-bold fs-7 d-block">{{ $invitation->shipment->pickup_area ?: '—' }}</span>
                        </div>

                        <div class="mb-2.5">
                            <small class="d-block text-uppercase fw-bold" style="color: #64748B; font-size: 0.75rem;">Address</small>
                            <span class="text-white fw-bold fs-7 d-block">{{ $invitation->shipment->pickup_address ?: '—' }}</span>
                        </div>

                        <div class="mb-1">
                            <small class="d-block text-uppercase fw-bold" style="color: #64748B; font-size: 0.75rem;">Coordinates</small>
                            <span class="text-white fw-bold fs-7 d-block">
                                {{ $invitation->shipment->pickup_lat && $invitation->shipment->pickup_lng ? $invitation->shipment->pickup_lat . ', ' . $invitation->shipment->pickup_lng : '—' }}
                            </span>
                            @if($invitation->shipment->pickup_lat && $invitation->shipment->pickup_lng)
                                <a href="https://maps.google.com/?q={{ $invitation->shipment->pickup_lat }},{{ $invitation->shipment->pickup_lng }}" target="_blank" class="fw-bold fs-7 text-decoration-none d-inline-flex align-items-center gap-1 mt-1" style="color: #38BDF8;">
                                    <i class="bx bx-link-external"></i> Map
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Delivery Location Column --}}
                    <div class="col-12 col-md-6 ps-md-3">
                        <h6 class="fw-bold text-white mb-3 fs-7 d-flex align-items-center gap-1.5">
                            <i class="bx bx-navigation text-success fs-5"></i> Delivery Location
                        </h6>

                        <div class="mb-2.5">
                            <small class="d-block text-uppercase fw-bold" style="color: #64748B; font-size: 0.75rem;">Country / City</small>
                            <span class="text-white fw-bold fs-7 d-block">
                                {{ $invitation->shipment->dropoffCountry->name_en ?? ($invitation->shipment->dropoffCountry->name ?? 'Kuwait') }} / 
                                {{ $invitation->shipment->dropoffCity->name_en ?? ($invitation->shipment->dropoffCity->name ?? '—') }}
                            </span>
                        </div>

                        <div class="mb-2.5">
                            <small class="d-block text-uppercase fw-bold" style="color: #64748B; font-size: 0.75rem;">Area</small>
                            <span class="text-white fw-bold fs-7 d-block">{{ $invitation->shipment->dropoff_area ?: '—' }}</span>
                        </div>

                        <div class="mb-2.5">
                            <small class="d-block text-uppercase fw-bold" style="color: #64748B; font-size: 0.75rem;">Address</small>
                            <span class="text-white fw-bold fs-7 d-block">{{ $invitation->shipment->dropoff_address ?: '—' }}</span>
                        </div>

                        <div class="mb-1">
                            <small class="d-block text-uppercase fw-bold" style="color: #64748B; font-size: 0.75rem;">Coordinates</small>
                            <span class="text-white fw-bold fs-7 d-block">
                                {{ $invitation->shipment->dropoff_lat && $invitation->shipment->dropoff_lng ? $invitation->shipment->dropoff_lat . ', ' . $invitation->shipment->dropoff_lng : '—' }}
                            </span>
                            @if($invitation->shipment->dropoff_lat && $invitation->shipment->dropoff_lng)
                                <a href="https://maps.google.com/?q={{ $invitation->shipment->dropoff_lat }},{{ $invitation->shipment->dropoff_lng }}" target="_blank" class="fw-bold fs-7 text-decoration-none d-inline-flex align-items-center gap-1 mt-1" style="color: #34D399;">
                                    <i class="bx bx-link-external"></i> Map
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                @if($invitation->shipment->pickup_lat && $invitation->shipment->pickup_lng && $invitation->shipment->dropoff_lat && $invitation->shipment->dropoff_lng)
                <!-- Interactive Live Route Map -->
                <div class="mt-3 pt-3 border-top border-secondary border-opacity-25">
                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                        <span class="fw-bold text-white fs-7 d-flex align-items-center gap-1.5">
                            <i class="bx bx-map-alt text-info fs-5"></i> Live Interactive Route Map (الخريطة التفاعلية والمسار المباشر)
                        </span>
                        <a href="https://www.google.com/maps/dir/?api=1&origin={{ $invitation->shipment->pickup_lat }},{{ $invitation->shipment->pickup_lng }}&destination={{ $invitation->shipment->dropoff_lat }},{{ $invitation->shipment->dropoff_lng }}" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 py-1 fs-8 fw-bold text-decoration-none d-inline-flex align-items-center gap-1" style="box-shadow: 0 4px 15px rgba(16,185,129,0.3);">
                            <i class="bx bx-navigation fs-6"></i> Open Google Navigation (فتح التوجيه على غوغل مابس)
                        </a>
                    </div>

                    <div id="driverRouteMap" class="rounded-4 overflow-hidden border border-secondary border-opacity-25" style="height: 350px; width: 100%; background: #0F172A; z-index: 1;"></div>
                </div>
                @endif
            </div>

            <!-- Cargo Specifications -->
            <div class="spec-box">
                <h6 class="fw-bold text-white mb-3 d-flex align-items-center gap-2 fs-7 text-uppercase" style="letter-spacing: 0.5px; color: #38BDF8 !important;">
                    <i class="bx bx-package text-info"></i> Cargo & Truck Specs
                </h6>
                <div class="row g-3">
                    <div class="col-6 col-md-4">
                        <small class="d-block text-uppercase fw-bold" style="color: #64748B; font-size: 0.75rem;">Shipment Name</small>
                        <span class="text-white fw-semibold fs-7">{{ $invitation->shipment->shipment_name ?: 'Shipment Order' }}</span>
                    </div>
                    <div class="col-6 col-md-4">
                        <small class="d-block text-uppercase fw-bold" style="color: #64748B; font-size: 0.75rem;">Truck Type Required</small>
                        <span class="text-info fw-semibold fs-7">{{ $invitation->shipment->truckType->name_en ?? 'Any Truck' }}</span>
                    </div>
                    <div class="col-6 col-md-4">
                        <small class="d-block text-uppercase fw-bold" style="color: #64748B; font-size: 0.75rem;">Cargo Weight</small>
                        <span class="text-white fw-semibold fs-7">{{ $invitation->shipment->weight ? $invitation->shipment->weight . ' kg' : 'Standard' }}</span>
                    </div>
                    @if($invitation->shipment->hs_code)
                    @php
                        if (!isset($hsDetails) || !$hsDetails) {
                            try {
                                $hsDetails = app(\App\Services\HsCodeLookupService::class)->lookup($invitation->shipment->hs_code);
                            } catch (\Exception $e) {
                                $hsDetails = null;
                            }
                        }
                    @endphp

                    <div class="col-12 mt-2 pt-2 border-top border-secondary border-opacity-25">
                        <div class="p-3 rounded-4" style="background: rgba(6, 182, 212, 0.06); border: 1px solid rgba(6, 182, 212, 0.25);">
                            {{-- Header --}}
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2 pb-2 border-bottom border-secondary border-opacity-25">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill fs-7">
                                        <i class="bx bx-barcode-reader me-1"></i> HS Code: {{ $invitation->shipment->hs_code }}
                                    </span>

                                    @if($invitation->shipment->is_fragile || (!empty($hsDetails['analysis']['flags']['is_fragile'])))
                                        <span class="badge px-3 py-1.5 rounded-pill fs-8 fw-bold d-inline-flex align-items-center" style="background: rgba(220, 38, 38, 0.25); color: #FECACA !important; border: 1px solid #EF4444;">
                                            <i class="bx bx-wine me-1"></i>Fragile / قابل للكسر
                                        </span>
                                    @endif

                                    @if(!empty($hsDetails['analysis']['flags']['is_hazardous']))
                                        <span class="badge px-3 py-1.5 rounded-pill fs-8 fw-bold d-inline-flex align-items-center" style="background: rgba(234, 88, 12, 0.25); color: #FED7AA !important; border: 1px solid #EA580C;">
                                            <i class="bx bx-error-circle me-1"></i>Hazmat / مواد خطرة
                                        </span>
                                    @endif

                                    @if(!empty($hsDetails['analysis']['flags']['requires_cold_chain']) || !empty($hsDetails['analysis']['flags']['is_temperature_controlled']))
                                        <span class="badge px-3 py-1.5 rounded-pill fs-8 fw-bold d-inline-flex align-items-center" style="background: rgba(14, 165, 233, 0.25); color: #BAE6FD !important; border: 1px solid #0EA5E9;">
                                            <i class="bx bx-snowflake me-1"></i>Cold Chain / تبريد
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Summary Description & Read More Button --}}
                            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
                                <div>
                                    <h6 class="fw-bold text-white mb-0.5 fs-7">
                                        {{ $hsDetails['description'] ?? ($invitation->shipment->hs_code_description ?: 'Harmonized System Freight Item') }}
                                    </h6>
                                    @if(!empty($hsDetails['description_ar']))
                                        <div class="fw-bold text-info fs-7" style="font-family: system-ui, sans-serif;">
                                            {{ $hsDetails['description_ar'] }}
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1 fs-8 fw-semibold text-nowrap align-self-start align-self-sm-center" onclick="toggleHsDetails()" id="btnHsToggle">
                                    <i class="bx bx-chevron-down me-1" id="hsToggleIcon"></i> <span id="hsToggleText">المزيد (View Details)</span>
                                </button>
                            </div>

                            {{-- Collapsable Details Wrap --}}
                            <div id="hsFullDetailsWrap" class="mt-3 pt-3 border-top border-secondary border-opacity-25" style="display: none;">

                                {{-- Tariff Metrics Grid --}}
                                <div class="row g-2 mb-3">
                                    <div class="col-6 col-md-3">
                                        <div class="p-2 rounded-3 border" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.08) !important;">
                                            <small class="d-block text-slate-400 fs-8 mb-0.5"><i class="bx bx-dollar-circle text-warning me-1"></i> Duty Rate (الجمارك)</small>
                                            <strong class="text-warning fs-7">{{ $hsDetails['duty_rate'] ?? '5.0%' }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="p-2 rounded-3 border" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.08) !important;">
                                            <small class="d-block text-slate-400 fs-8 mb-0.5"><i class="bx bx-receipt text-info me-1"></i> VAT Rate (الضريبة)</small>
                                            <strong class="text-info fs-7">{{ $hsDetails['vat_rate'] ?? '15.0%' }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="p-2 rounded-3 border" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.08) !important;">
                                            <small class="d-block text-slate-400 fs-8 mb-0.5"><i class="bx bx-category text-info me-1"></i> Category</small>
                                            <strong class="text-white fs-8 text-truncate d-block">{{ $hsDetails['category'] ?? 'General Cargo' }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="p-2 rounded-3 border" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.08) !important;">
                                            <small class="d-block text-slate-400 fs-8 mb-0.5"><i class="bx bx-layer text-info me-1"></i> Section</small>
                                            <strong class="text-white fs-8 text-truncate d-block">{{ $hsDetails['section'] ?? 'Harmonized Section' }}</strong>
                                        </div>
                                    </div>
                                </div>

                                {{-- Import Restrictions & Clearance Checklist --}}
                                @if(!empty($hsDetails['restrictions']))
                                    <div class="p-2.5 rounded-3 mb-3" style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25);">
                                        <div class="fw-bold text-warning mb-1.5 fs-8 d-flex align-items-center gap-1">
                                            <i class="bx bx-shield-quarter fs-6"></i> Customs Clearance & Import Restrictions (شروط وتصاريح الاستيراد):
                                        </div>
                                        <ul class="mb-0 ps-3 text-slate-200 fs-8" style="font-size: 0.8rem !important;">
                                            @foreach($hsDetails['restrictions'] as $r)
                                                <li class="mb-0.5">
                                                    {{ is_array($r) ? ($r['en'] ?? '') : $r }}
                                                    @if(is_array($r) && !empty($r['ar']))
                                                        <span class="text-info font-normal">({{ $r['ar'] }})</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                {{-- Advanced Freight Safety Analysis Engine --}}
                                @if(!empty($hsDetails['analysis']))
                                    @php
                                        $a = $hsDetails['analysis'];
                                        $riskBg = ($a['risk_level'] ?? '') === 'HIGH' ? 'bg-danger' : ((($a['risk_level'] ?? '') === 'MEDIUM') ? 'bg-warning text-dark' : 'bg-success');
                                    @endphp
                                    <div class="p-2.5 rounded-3" style="background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(78, 205, 196, 0.25);">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                            <div class="fw-bold text-white fs-8 d-flex align-items-center gap-1">
                                                <i class="bx bx-analyse text-info fs-6"></i> Freight Safety & Handling Analysis (تحليل سلامة ومخاطر النقل):
                                            </div>
                                            <span class="badge {{ $riskBg }} fw-bold px-2 py-0.5 fs-9">Risk: {{ $a['risk_level'] ?? 'LOW' }}</span>
                                        </div>

                                        @if(!empty($a['flags']))
                                            <div class="d-flex flex-wrap gap-1.5 mb-2">
                                                @if(!empty($a['flags']['is_fragile']))
                                                    <span class="badge px-2.5 py-1 rounded-pill fs-9 fw-bold d-inline-flex align-items-center" style="background: rgba(220, 38, 38, 0.25); color: #FECACA !important; border: 1px solid #EF4444;"><i class="bx bx-wine me-1"></i>Fragile / قابل للكسر</span>
                                                @endif
                                                @if(!empty($a['flags']['is_hazardous']))
                                                    <span class="badge px-2.5 py-1 rounded-pill fs-9 fw-bold d-inline-flex align-items-center" style="background: rgba(234, 88, 12, 0.25); color: #FED7AA !important; border: 1px solid #EA580C;"><i class="bx bx-error-circle me-1"></i>Hazmat / مواد خطرة</span>
                                                @endif
                                                @if(!empty($a['flags']['requires_cold_chain']) || !empty($a['flags']['is_temperature_controlled']))
                                                    <span class="badge px-2.5 py-1 rounded-pill fs-9 fw-bold d-inline-flex align-items-center" style="background: rgba(14, 165, 233, 0.25); color: #BAE6FD !important; border: 1px solid #0EA5E9;"><i class="bx bx-snowflake me-1"></i>Cold Chain / تبريد</span>
                                                @endif
                                                @if(!empty($a['flags']['is_perishable']))
                                                    <span class="badge px-2.5 py-1 rounded-pill fs-9 fw-bold d-inline-flex align-items-center" style="background: rgba(22, 163, 74, 0.25); color: #BBF7D0 !important; border: 1px solid #16A34A;"><i class="bx bx-food-tag me-1"></i>Perishable Cargo / مواد غذائية</span>
                                                @endif
                                            </div>
                                        @endif

                                        @if(!empty($a['handling_instructions']))
                                            <div class="text-slate-300 fs-8 mb-1"><strong class="text-info"><i class="bx bx-box me-1"></i>Transport Recommendations (تعليمات المناولة والنقل للسائق):</strong></div>
                                            <ul class="mb-0 ps-3 text-slate-300 fs-8" style="font-size: 0.8rem !important;">
                                                @foreach($a['handling_instructions'] as $h)
                                                    <li class="mb-1">
                                                        <strong>{{ is_array($h) ? ($h['en'] ?? '') : $h }}</strong>
                                                        @if(is_array($h) && !empty($h['ar']))
                                                            <span class="text-info ms-1">({{ $h['ar'] }})</span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Action Response Section -->
            @if($invitation->status === 'pending')
                <div class="pt-2">
                    <form method="POST" action="{{ route('driver.invitation.respond', $invitation->token) }}" id="respondForm">
                        @csrf
                        <input type="hidden" name="action" id="actionInput" value="accept">

                        <div class="d-flex flex-column flex-sm-row gap-3">
                            <button type="button" class="btn btn-accept flex-fill d-inline-flex align-items-center justify-content-center gap-2" onclick="submitResponse('accept')">
                                <i class="bx bx-check-circle fs-4"></i>
                                <span>Accept Shipment</span>
                            </button>
                            <button type="button" class="btn btn-reject flex-fill d-inline-flex align-items-center justify-content-center gap-2" onclick="toggleDeclineArea()">
                                <i class="bx bx-x-circle fs-4"></i>
                                <span>Decline Shipment</span>
                            </button>
                        </div>

                        <!-- Decline Reason Collapse Area -->
                        <div id="declineArea" class="mt-3 p-3 rounded-4" style="display:none; background: rgba(244, 63, 94, 0.08); border: 1px solid rgba(244, 63, 94, 0.25);">
                            <label class="form-label text-white fw-bold fs-7 mb-2">Please tell us why you are declining (Optional):</label>
                            <textarea name="rejection_reason" id="rejection_reason" class="form-control bg-dark border-secondary text-white rounded-3 mb-3" rows="3" placeholder="Reason for declining (e.g. unavailable, price too low, wrong truck type)..."></textarea>
                            <div class="text-end">
                                <button type="button" class="btn btn-danger px-4 rounded-3 fw-bold" onclick="submitResponse('reject')">
                                    Confirm Decline
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            @else
                <div class="p-3 rounded-4 text-center mt-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                    <p class="mb-0 text-slate-300 fs-7">
                        <i class="bx bx-info-circle text-info me-1"></i> You have already responded to this invitation with status: <strong class="text-white">{{ strtoupper($invitation->status) }}</strong>.
                    </p>
                    @if($invitation->rejection_reason)
                        <div class="mt-2 text-start p-2 rounded bg-dark border border-secondary text-danger fs-8">
                            <strong>Reason:</strong> {{ $invitation->rejection_reason }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <script>
        function toggleDeclineArea() {
            var area = document.getElementById('declineArea');
            if (area.style.display === 'none') {
                area.style.display = 'block';
            } else {
                area.style.display = 'none';
            }
        }

        function toggleHsDetails() {
            var wrap = document.getElementById('hsFullDetailsWrap');
            var btn = document.getElementById('btnHsToggle');
            var textSpan = document.getElementById('hsToggleText');
            var icon = document.getElementById('hsToggleIcon');

            if (!wrap) return;

            if (wrap.style.display === 'none' || wrap.style.display === '') {
                wrap.style.display = 'block';
                if (textSpan) textSpan.innerText = 'إخفاء (Hide Details)';
                if (icon) icon.className = 'bx bx-chevron-up me-1';
                if (btn) {
                    btn.classList.remove('btn-outline-info');
                    btn.classList.add('btn-info', 'text-dark');
                }
            } else {
                wrap.style.display = 'none';
                if (textSpan) textSpan.innerText = 'المزيد (View Details)';
                if (icon) icon.className = 'bx bx-chevron-down me-1';
                if (btn) {
                    btn.classList.remove('btn-info', 'text-dark');
                    btn.classList.add('btn-outline-info');
                }
            }
        }

        function submitResponse(action) {
            document.getElementById('actionInput').value = action;
            document.getElementById('respondForm').submit();
        }

        @if($invitation->shipment->pickup_lat && $invitation->shipment->pickup_lng && $invitation->shipment->dropoff_lat && $invitation->shipment->dropoff_lng)
        document.addEventListener("DOMContentLoaded", function() {
            var pickupLat = {{ $invitation->shipment->pickup_lat }};
            var pickupLng = {{ $invitation->shipment->pickup_lng }};
            var dropoffLat = {{ $invitation->shipment->dropoff_lat }};
            var dropoffLng = {{ $invitation->shipment->dropoff_lng }};

            var map = L.map('driverRouteMap', {
                zoomControl: true,
                scrollWheelZoom: false
            });

            // CartoDB Voyager Tile Layer (High contrast, modern map style)
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap &copy; CARTO',
                subdomains: 'abcd',
                maxZoom: 19
            }).addTo(map);

            // Custom Leaflet Pickup Marker Pin
            var pickupIcon = L.divIcon({
                className: 'custom-leaflet-marker',
                html: '<div style="background: #06B6D4; width: 34px; height: 34px; border-radius: 50%; border: 3px solid #FFFFFF; box-shadow: 0 0 15px rgba(6,182,212,0.8); display: flex; align-items: center; justify-content: center; color: white;"><i class="bx bx-map-pin fs-5"></i></div>',
                iconSize: [34, 34],
                iconAnchor: [17, 17]
            });

            // Custom Leaflet Dropoff Marker Pin
            var dropoffIcon = L.divIcon({
                className: 'custom-leaflet-marker',
                html: '<div style="background: #10B981; width: 34px; height: 34px; border-radius: 50%; border: 3px solid #FFFFFF; box-shadow: 0 0 15px rgba(16,185,129,0.8); display: flex; align-items: center; justify-content: center; color: white;"><i class="bx bx-navigation fs-5"></i></div>',
                iconSize: [34, 34],
                iconAnchor: [17, 17]
            });

            var pMarker = L.marker([pickupLat, pickupLng], { icon: pickupIcon }).addTo(map);
            pMarker.bindPopup('<strong style="color:#06B6D4;">⚓ Pickup Location:</strong><br>' + "{{ addslashes($invitation->shipment->pickup_address ?? 'Pickup') }}");

            var dMarker = L.marker([dropoffLat, dropoffLng], { icon: dropoffIcon }).addTo(map);
            dMarker.bindPopup('<strong style="color:#10B981;">🏁 Delivery Destination:</strong><br>' + "{{ addslashes($invitation->shipment->dropoff_address ?? 'Dropoff') }}");

            // Dashed Polyline Route
            var latlngs = [
                [pickupLat, pickupLng],
                [dropoffLat, dropoffLng]
            ];

            var polyline = L.polyline(latlngs, {
                color: '#06B6D4',
                weight: 5,
                opacity: 0.9,
                dashArray: '10, 10'
            }).addTo(map);

            // Automatically fit bounds so both pickup and delivery locations are visible
            var bounds = L.latLngBounds(latlngs);
            map.fitBounds(bounds, { padding: [50, 50] });
        });
        @endif
    </script>
</body>
</html>
