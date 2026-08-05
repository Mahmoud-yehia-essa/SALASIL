@extends('admin.master_admin')
@section('admin')

<style>
    /* Default Dark Mode Styles */
    .hscode-card {
        background: rgba(20, 30, 71, 0.75);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(78, 205, 196, 0.25);
        border-radius: 1rem;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        transition: all 0.3s ease;
    }

    .preset-badge {
        cursor: pointer;
        transition: all 0.25s ease;
        background: rgba(15, 26, 69, 0.6);
        border: 1px solid rgba(78, 205, 196, 0.3);
        color: #72E8E0;
    }
    .preset-badge:hover {
        background: rgba(78, 205, 196, 0.2);
        border-color: #4ECDC4;
        color: #ffffff;
        transform: translateY(-2px);
    }

    .info-tile {
        background: rgba(8, 14, 36, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 0.75rem;
        padding: 1.25rem;
        transition: all 0.3s ease;
    }

    .pulse-dot {
        width: 10px;
        height: 10px;
        background-color: #4ECDC4;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 0 rgba(78, 205, 196, 0.7);
        animation: pulse-ring 1.8s infinite;
    }

    @keyframes pulse-ring {
        0% { box-shadow: 0 0 0 0 rgba(78, 205, 196, 0.7); }
        70% { box-shadow: 0 0 0 8px rgba(78, 205, 196, 0); }
        100% { box-shadow: 0 0 0 0 rgba(78, 205, 196, 0); }
    }

    /* Light Theme Adaptive Rules (html.light-theme) */
    html.light-theme .hscode-card {
        background: #FFFFFF !important;
        border: 1px solid #E2E8F0 !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05) !important;
    }

    html.light-theme .preset-badge {
        background: #F1F5F9 !important;
        border: 1px solid #CBD5E1 !important;
        color: #0F172A !important;
    }
    html.light-theme .preset-badge:hover {
        background: #0EA5E9 !important;
        border-color: #0284C7 !important;
        color: #FFFFFF !important;
    }

    html.light-theme .info-tile {
        background: #F8FAFC !important;
        border: 1px solid #E2E8F0 !important;
    }

    #hs_code_input {
        padding-left: 55px !important;
    }

    html.light-theme #hs_code_input {
        background-color: #F8FAFC !important;
        color: #0F172A !important;
        border-color: #CBD5E1 !important;
        padding-left: 55px !important;
    }
    html.light-theme #hs_code_input::placeholder {
        color: #94A3B8 !important;
    }

    html.light-theme .breadcrumb-title,
    html.light-theme .hscode-card h3,
    html.light-theme .hscode-card h4,
    html.light-theme .hscode-card h5,
    html.light-theme .hscode-card h6,
    html.light-theme .hscode-card .text-white {
        color: #0F172A !important;
    }

    html.light-theme .text-slate-300,
    html.light-theme .text-slate-400 {
        color: #475569 !important;
    }

    html.light-theme .bg-dark {
        background-color: #E2E8F0 !important;
        color: #0F172A !important;
    }

    html.light-theme .btn-outline-light {
        border-color: #CBD5E1 !important;
        color: #334155 !important;
    }
    html.light-theme .btn-outline-light:hover {
        background-color: #0284C7 !important;
        border-color: #0284C7 !important;
        color: #FFFFFF !important;
    }

    html.light-theme .list-group-item.text-slate-300 {
        color: #334155 !important;
        border-color: #E2E8F0 !important;
    }

    html.light-theme #error_container {
        background: rgba(244, 63, 94, 0.08) !important;
        border-color: rgba(244, 63, 94, 0.25) !important;
        color: #E11D48 !important;
    }
    html.light-theme #error_container h6 {
        color: #9F1239 !important;
    }
</style>

<div class="page-content">
    <!-- Breadcrumb Header -->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
        <div class="breadcrumb-title pe-3 text-white fw-bold fs-5">Shipment HS Code Lookup</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt text-info"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('all.shipments') }}" class="text-slate-300">Shipments</a></li>
                    <li class="breadcrumb-item active text-info" aria-current="page">HS Code Tariff Lookup</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Main Container -->
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">

            <!-- Search Card -->
            <div class="hscode-card p-4 p-md-5 mb-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                    <div>
                        <h4 class="fw-bold text-white mb-1 d-flex align-items-center gap-2">
                            <i class="bx bx-barcode text-info fs-3"></i>
                            <span>Harmonized Customs Tariff Lookup (HS Code)</span>
                        </h4>
                        <p class="text-slate-300 fs-7 mb-0">
                            Search international & GCC customs tariff codes, duty rates, VAT, and shipment compliance rules.
                        </p>
                    </div>
                </div>

                <!-- Search Input Form -->
                <form id="hscode_search_form" onsubmit="return false;">
                    @csrf
                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-12 col-md-9">
                            <div class="position-relative">
                                <input type="text" 
                                       id="hs_code_input" 
                                       name="hs_code" 
                                       class="form-control form-control-lg bg-dark bg-opacity-50 text-white border-secondary border-opacity-50 rounded-3 shadow-none" 
                                       style="padding-left: 55px !important; height: 54px; font-size: 1.1rem; letter-spacing: 0.5px;"
                                       placeholder="Enter HS Code (e.g., 8704.21, 8701.20, 2710.12)..."
                                       autocomplete="off"
                                       required>
                                <span class="position-absolute text-info d-flex align-items-center justify-content-center" style="left: 16px; top: 50%; transform: translateY(-50%); width: 28px; height: 28px; pointer-events: none; z-index: 5;">
                                    <i class="bx bx-search fs-3"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <button type="submit" id="btn_search_hscode" class="btn btn-info btn-lg w-100 fw-bold rounded-3 d-flex align-items-center justify-content-center gap-2" style="height: 54px; background: linear-gradient(135deg, #4ECDC4, #1B2B6B); border: none; color: #ffffff;">
                                <i class="bx bx-search-alt fs-4"></i>
                                <span id="btn_text">Search Tariff</span>
                                <span id="btn_spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Quick Presets -->
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="text-slate-400 fs-7 me-2 fw-semibold">Quick Freight Presets:</span>
                    <button type="button" class="badge preset-badge px-3 py-2 rounded-pill font-monospace border-0" onclick="quickSearch('7009')">
                        <i class="bx bx-window me-1"></i>7009 (Glass Mirrors / مرايا زجاجية)
                    </button>
                    <button type="button" class="badge preset-badge px-3 py-2 rounded-pill font-monospace border-0" onclick="quickSearch('6911')">
                        <i class="bx bx-dish me-1"></i>6911 (Porcelain / أواني خزفية)
                    </button>
                    <button type="button" class="badge preset-badge px-3 py-2 rounded-pill font-monospace border-0" onclick="quickSearch('8704.21')">
                        <i class="bx bx-truck me-1"></i>8704.21 (Heavy Freight Trucks)
                    </button>
                    <button type="button" class="badge preset-badge px-3 py-2 rounded-pill font-monospace border-0" onclick="quickSearch('8517')">
                        <i class="bx bx-mobile-alt me-1"></i>8517 (Smartphones & Telecom)
                    </button>
                    <button type="button" class="badge preset-badge px-3 py-2 rounded-pill font-monospace border-0" onclick="quickSearch('2710.12')">
                        <i class="bx bx-droplet me-1"></i>2710.12 (Petroleum & Fuels)
                    </button>
                </div>
            </div>

            <!-- Error Banner Container -->
            <div id="error_container" class="alert alert-danger border-0 rounded-4 shadow-sm mb-4 d-none" style="background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.3) !important; color: #FDA4AF;">
                <div class="d-flex align-items-center gap-3">
                    <i class="bx bx-error-circle fs-2 text-danger"></i>
                    <div>
                        <h6 class="fw-bold mb-1 text-white">Search Error</h6>
                        <span id="error_message_text">Unable to find details for the requested HS Code.</span>
                    </div>
                </div>
            </div>

            <!-- Results Card Container -->
            <div id="results_container" class="hscode-card p-4 p-md-5 d-none">
                <!-- Status & Cache Info Banner -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pb-3 mb-4 border-bottom border-secondary border-opacity-25">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-40 px-3 py-1.5 rounded-pill font-monospace fs-7">
                            <i class="bx bx-check-shield me-1"></i> Verified Customs Tariff Record
                        </span>
                    </div>
                    <span class="text-slate-400 fs-7">
                        <i class="bx bx-time me-1"></i> Cached until: <span id="res_cached_at" class="text-white font-monospace">--</span>
                    </span>
                </div>

                <!-- Primary Item Header -->
                <div class="row g-4 mb-4">
                    <div class="col-12 col-md-8">
                        <div class="mb-2">
                            <span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-40 px-3 py-1.5 rounded-pill font-monospace fs-6" id="res_code_badge">
                                HS Code: 8704.21.00
                            </span>
                            <button type="button" class="btn btn-sm btn-link text-info p-0 ms-2 text-decoration-none" onclick="copyHsCode()">
                                <i class="bx bx-copy fs-5" title="Copy HS Code"></i>
                            </button>
                        </div>
                        <h3 class="fw-bold text-white mb-2" id="res_description_en">
                            Motor vehicles for the transport of goods
                        </h3>
                        <h5 class="fw-bold text-info mb-0" style="font-family: system-ui, sans-serif;" id="res_description_ar">
                            مركبات نارية لنقل البضائع والمعدات الثقيلة
                        </h5>
                    </div>

                    <div class="col-12 col-md-4 text-md-end">
                        <button type="button" class="btn btn-outline-light px-3 py-2 rounded-3 me-2" onclick="window.print()">
                            <i class="bx bx-printer me-1"></i> Print / Export
                        </button>
                    </div>
                </div>

                <!-- Tariff Metrics Grid -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="info-tile">
                            <div class="text-slate-400 fs-7 mb-1"><i class="bx bx-dollar-circle me-1"></i> Customs Duty Rate (الجمارك)</div>
                            <div class="fs-3 fw-bold text-warning" id="res_duty_rate">5.0%</div>
                            <small class="text-slate-400 fs-8">Standard GCC Unified Tariff</small>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="info-tile">
                            <div class="text-slate-400 fs-7 mb-1"><i class="bx bx-receipt me-1"></i> Value Added Tax (VAT)</div>
                            <div class="fs-3 fw-bold text-info" id="res_vat_rate">15.0%</div>
                            <small class="text-slate-400 fs-8">Standard Saudi ZATCA VAT Rate</small>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="info-tile">
                            <div class="text-slate-400 fs-7 mb-1"><i class="bx bx-category me-1"></i> Classification Category</div>
                            <div class="fw-bold text-white fs-6 text-truncate" id="res_category">Vehicles & Transport</div>
                            <small class="text-slate-400 fs-8">Tariff Chapter Classification</small>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="info-tile">
                            <div class="text-slate-400 fs-7 mb-1"><i class="bx bx-layer me-1"></i> Customs Section</div>
                            <div class="fw-bold text-white fs-6 text-truncate" id="res_section">Section XVII</div>
                            <small class="text-slate-400 fs-8">Harmonized Section Reference</small>
                        </div>
                    </div>
                </div>

                <!-- Import Restrictions & Compliance List -->
                <div class="info-tile p-4 mb-4">
                    <h6 class="fw-bold text-white mb-3 d-flex align-items-center gap-2">
                        <i class="bx bx-shield-quarter text-warning fs-4"></i>
                        <span>Customs Clearance & Import Restrictions (شروط وإجراءات الاستيراد)</span>
                    </h6>
                    <ul class="list-group list-group-flush bg-transparent" id="res_restrictions_list">
                        <!-- Dynamic LI List -->
                    </ul>
                </div>

                <!-- Advanced Freight Analysis Engine Card (محرك تحليل خصائص ومخاطر الشحنة) -->
                <div class="info-tile p-4" style="border: 1px solid rgba(78, 205, 196, 0.35) !important;">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                        <h6 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
                            <i class="bx bx-analyse text-info fs-4"></i>
                            <span>Advanced Freight & Safety Analysis (محرك تحليل خصائص السلامة والمخاطر)</span>
                        </h6>
                        <span id="res_risk_badge" class="badge px-3 py-1.5 rounded-pill font-monospace fs-7">
                            Risk Assessment: LOW
                        </span>
                    </div>

                    <!-- Flags Badges Grid -->
                    <div class="mb-4">
                        <div class="text-slate-400 fs-7 mb-2 fw-semibold">Extracted Freight Safety Attributes (خصائص البضاعة الكاشفة):</div>
                        <div class="d-flex align-items-center gap-2 flex-wrap" id="res_flags_container">
                            <!-- Dynamic Flags Badges -->
                        </div>
                    </div>

                    <!-- Handling & Packaging Instructions -->
                    <div>
                        <div class="text-slate-400 fs-7 mb-2 fw-semibold"><i class="bx bx-box me-1"></i> Recommended Handling & Transport Instructions (تعليمات المناولة والتغليف الموصى بها):</div>
                        <ul class="list-group list-group-flush bg-transparent" id="res_handling_list">
                            <!-- Dynamic Handling Instructions -->
                        </ul>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document.body).ready(function() {
        $('#hscode_search_form').on('submit', function(e) {
            e.preventDefault();
            performLookup();
        });
    });

    function quickSearch(code) {
        $('#hs_code_input').val(code);
        performLookup();
    }

    function performLookup() {
        var hsCode = $('#hs_code_input').val().trim();

        if (!hsCode) {
            showError('Please enter a valid HS Code number.');
            return;
        }

        // Loading state
        $('#btn_search_hscode').prop('disabled', true);
        $('#btn_text').text('Searching...');
        $('#btn_spinner').removeClass('d-none');
        $('#error_container').addClass('d-none');
        $('#results_container').addClass('d-none');

        $.ajax({
            url: "{{ route('hscode.lookup.ajax') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                hs_code: hsCode
            },
            dataType: "json",
            success: function(response) {
                resetButtonState();

                if (response.status === 'success' && response.data) {
                    displayResults(response.data);
                } else {
                    showError(response.message || 'HS Code details not found.');
                }
            },
            error: function(xhr) {
                resetButtonState();
                var msg = 'Failed to retrieve HS Code details. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showError(msg);
            }
        });
    }

    function resetButtonState() {
        $('#btn_search_hscode').prop('disabled', false);
        $('#btn_text').text('Search Tariff');
        $('#btn_spinner').addClass('d-none');
    }

    function showError(message) {
        $('#error_message_text').text(message);
        $('#error_container').removeClass('d-none');
        $('#results_container').addClass('d-none');
    }

    function displayResults(data) {
        $('#error_container').addClass('d-none');

        $('#res_code_badge').html('<i class="bx bx-barcode me-1"></i> HS Code: ' + data.code);
        $('#res_description_en').text(data.description);
        $('#res_description_ar').text(data.description_ar || '');
        $('#res_duty_rate').text(data.duty_rate || '5.0%');
        $('#res_vat_rate').text(data.vat_rate || '15.0%');
        $('#res_category').text(data.category || 'General Cargo');
        $('#res_section').text(data.section || 'Section XVII');
        $('#res_cached_at').text(data.cached_at || 'Just now');
        $('#data_source_badge').text('Source: ' + (data.source || 'Tariff Engine'));

        // Render Restrictions List
        var $list = $('#res_restrictions_list');
        $list.empty();

        if (data.restrictions && data.restrictions.length > 0) {
            $.each(data.restrictions, function(index, rule) {
                $list.append(`
                    <li class="list-group-item bg-transparent text-slate-300 border-secondary border-opacity-25 px-0 py-2.5 d-flex align-items-start gap-2">
                        <i class="bx bx-check-circle text-info fs-5 mt-0.5"></i>
                        <span>${rule}</span>
                    </li>
                `);
            });
        } else {
            $list.append(`
                <li class="list-group-item bg-transparent text-slate-400 px-0 py-2">
                    Standard Commercial Shipment Customs Regulations apply.
                </li>
            `);
        }

        // Render Advanced Freight Safety & Risk Analysis Engine
        if (data.analysis) {
            var analysis = data.analysis;

            // Risk Level Badge
            var riskBadgeClass = 'bg-success bg-opacity-20 text-success border border-success border-opacity-40';
            if (analysis.risk_level === 'HIGH') {
                riskBadgeClass = 'bg-danger bg-opacity-20 text-danger border border-danger border-opacity-40';
            } else if (analysis.risk_level === 'MEDIUM') {
                riskBadgeClass = 'bg-warning bg-opacity-20 text-warning border border-warning border-opacity-40';
            }

            $('#res_risk_badge')
                .attr('class', 'badge px-3 py-1.5 rounded-pill font-monospace fs-7 ' + riskBadgeClass)
                .html('<i class="bx bx-shield-alt-2 me-1"></i> Risk Level: ' + analysis.risk_level + ' (' + analysis.risk_label_ar + ')');

            // Product Attribute Flags Badges
            var $flagsContainer = $('#res_flags_container');
            $flagsContainer.empty();

            var flagConfigs = [
                { key: 'is_hazardous', icon: 'bx-error-circle', label: 'Hazardous (خطرة)', badge: 'border-danger text-danger bg-danger bg-opacity-10' },
                { key: 'is_fragile', icon: 'bx-wine', label: 'Fragile (قابلة للكسر)', badge: 'border-warning text-warning bg-warning bg-opacity-10' },
                { key: 'is_perishable', icon: 'bx-food-tag', label: 'Perishable Cargo (طعام/قابل للتلف)', badge: 'border-success text-success bg-success bg-opacity-10' },
                { key: 'requires_cold_chain', icon: 'bx-snowflake', label: 'Cold Chain Required (تتطلب تبريد)', badge: 'border-info text-info bg-info bg-opacity-10' },
                { key: 'is_high_value', icon: 'bx-diamond', label: 'High-Value Cargo (عالية القيمة)', badge: 'border-primary text-primary bg-primary bg-opacity-10' },
                { key: 'is_liquid_or_gas', icon: 'bx-droplet', label: 'Liquid or Gas (سوائل/غازات)', badge: 'border-info text-info bg-info bg-opacity-10' },
            ];

            var activeCount = 0;
            $.each(flagConfigs, function(i, item) {
                if (analysis.flags[item.key]) {
                    activeCount++;
                    $flagsContainer.append(`
                        <span class="badge border px-3 py-1.5 rounded-pill fw-semibold font-monospace fs-7 d-flex align-items-center gap-1.5 ${item.badge}">
                            <i class="bx ${item.icon} fs-6"></i> ${item.label}
                        </span>
                    `);
                }
            });

            if (activeCount === 0) {
                $flagsContainer.append(`
                    <span class="badge border border-secondary text-slate-300 px-3 py-1.5 rounded-pill fw-semibold font-monospace fs-7">
                        <i class="bx bx-check-circle me-1 text-success"></i> Standard Non-Hazardous Cargo (بضائع عادية قياسية)
                    </span>
                `);
            }

            // Handling Instructions
            var $handlingList = $('#res_handling_list');
            $handlingList.empty();

            if (analysis.handling_instructions && analysis.handling_instructions.length > 0) {
                $.each(analysis.handling_instructions, function(idx, item) {
                    $handlingList.append(`
                        <li class="list-group-item bg-transparent text-slate-300 border-secondary border-opacity-25 px-0 py-2.5 d-flex align-items-start gap-2">
                            <i class="bx bx-right-arrow-alt text-info fs-5 mt-0.5"></i>
                            <div>
                                <div class="fw-semibold text-white">${item.en}</div>
                                <div class="text-info fs-7" style="font-family: system-ui, sans-serif;">${item.ar}</div>
                            </div>
                        </li>
                    `);
                });
            }
        }

        $('#results_container').removeClass('d-none');
    }

    function copyHsCode() {
        var code = $('#hs_code_input').val().trim();
        if (code) {
            navigator.clipboard.writeText(code).then(function() {
                if (typeof toastr !== 'undefined') {
                    toastr.success('HS Code copied to clipboard!');
                } else {
                    alert('HS Code copied: ' + code);
                }
            });
        }
    }
</script>

@endsection
