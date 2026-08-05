@extends('admin.master_admin')
@section('admin')

{{-- ========================
     INVITE DRIVER TO SHIPMENT
======================== --}}

<style>
:root {
    --inv-cyan:       #06B6D4;
    --inv-cyan-light: #38BDF8;
    --inv-navy:       #0F172A;
    --inv-card:       #1E293B;
    --inv-border:     rgba(255,255,255,0.08);
    --inv-success:    #10B981;
    --inv-warning:    #F59E0B;
    --inv-purple:     #8B5CF6;
}

/* ─── Card Styling ─── */
.inv-card {
    background: linear-gradient(135deg, rgba(30,41,59,0.95) 0%, rgba(15,23,42,0.98) 100%);
    border: 1px solid var(--inv-border);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0,0,0,0.4);
}
.inv-card-header {
    background: linear-gradient(135deg, rgba(6,182,212,0.15) 0%, rgba(56,189,248,0.08) 100%);
    border-bottom: 1px solid rgba(6,182,212,0.2);
    padding: 24px 32px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.inv-card-header .icon-wrap {
    width: 52px; height: 52px;
    background: linear-gradient(135deg, var(--inv-cyan), var(--inv-cyan-light));
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: #fff;
    box-shadow: 0 8px 20px rgba(6,182,212,0.35);
    flex-shrink: 0;
}
.inv-card-body { padding: 32px; }

/* ─── Form Controls ─── */
.inv-card .form-control,
.inv-card .form-select {
    background: rgba(255,255,255,0.05) !important;
    border: 1px solid rgba(255,255,255,0.12) !important;
    color: #F8FAFC !important;
    border-radius: 12px !important;
    padding: 12px 16px !important;
    transition: all 0.25s !important;
    font-size: 0.92rem !important;
}
.inv-card .form-control:focus,
.inv-card .form-select:focus {
    background: rgba(6,182,212,0.08) !important;
    border-color: var(--inv-cyan) !important;
    box-shadow: 0 0 0 3px rgba(6,182,212,0.15) !important;
    color: #F8FAFC !important;
}
.inv-card .form-select option { background: #1E293B; color: #F8FAFC; }
.inv-card .form-label { color: #CBD5E1 !important; font-weight: 700 !important; font-size: 0.88rem !important; margin-bottom: 8px; }

/* ─── Section Title ─── */
.inv-section-title {
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--inv-cyan-light);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.inv-section-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, rgba(6,182,212,0.4), transparent);
}

/* ─── Dynamic Info Panels (Animated Show/Hide) ─── */
.info-display-panel {
    transition: all 0.4s cubic-bezier(0.22,1,0.36,1);
    overflow: hidden;
    max-height: 0;
    opacity: 0;
    margin-top: 14px;
}
.info-display-panel.visible {
    max-height: 1000px;
    opacity: 1;
}

.panel-card {
    background: rgba(6,182,212,0.06);
    border: 1px solid rgba(6,182,212,0.2);
    border-radius: 16px;
    padding: 20px 24px;
    backdrop-filter: blur(8px);
}

/* ─── Channel Cards ─── */
.channel-card {
    background: rgba(255,255,255,0.03);
    border: 2px solid rgba(255,255,255,0.08);
    border-radius: 14px;
    padding: 18px 20px;
    cursor: pointer;
    transition: all 0.25s;
    text-align: center;
    height: 100%;
}
.channel-card:hover {
    border-color: rgba(6,182,212,0.4);
    background: rgba(6,182,212,0.06);
}
.channel-card.selected {
    border-color: var(--inv-cyan);
    background: rgba(6,182,212,0.12);
    box-shadow: 0 0 20px rgba(6,182,212,0.2);
}
.channel-card i { font-size: 2rem; margin-bottom: 8px; color: var(--inv-cyan-light); }
.channel-card.ch-whatsapp.selected i { color: #25D366; }
.channel-card.ch-sms.selected i      { color: #F59E0B; }
.channel-card .ch-title { font-weight: 700; color: #F8FAFC; font-size: 0.9rem; }
.channel-card .ch-sub   { font-size: 0.78rem; color: #94A3B8; margin-top: 2px; }

/* ─── Selectable Truck Cards ─── */
.truck-select-card {
    background: rgba(255,255,255,0.04);
    border: 2px solid rgba(255,255,255,0.12);
    border-radius: 12px;
    padding: 12px 16px;
    cursor: pointer;
    transition: all 0.25s ease;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.truck-select-card:hover {
    border-color: var(--inv-cyan);
    background: rgba(6,182,212,0.08);
}
.truck-select-card.selected {
    border-color: var(--inv-cyan);
    background: rgba(6,182,212,0.15);
    box-shadow: 0 0 15px rgba(6,182,212,0.25);
}
.truck-select-card .check-icon {
    display: none;
    font-size: 20px;
    color: var(--inv-cyan-light);
}
.truck-select-card.selected .check-icon {
    display: block;
}

/* ─── Full Details Modal / Drawer ─── */
.modal-sh-detail .modal-dialog { max-width: 900px; }
.modal-sh-detail .modal-content {
    background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0,0,0,0.6);
    color: #F8FAFC;
}
.modal-sh-detail .modal-header {
    background: linear-gradient(135deg, rgba(6,182,212,0.15) 0%, rgba(56,189,248,0.08) 100%);
    border-bottom: 1px solid rgba(6,182,212,0.2);
    padding: 24px 32px;
}
.modal-sh-detail .modal-body { padding: 32px; max-height: 80vh; overflow-y: auto; }
.modal-sh-detail .modal-footer { border-top: 1px solid rgba(255,255,255,0.08); padding: 20px 32px; background: rgba(0,0,0,0.15); }

.detail-sec-title {
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: var(--inv-cyan-light);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.detail-sec-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, rgba(6,182,212,0.4), transparent);
}

.detail-card-grid {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 14px;
    padding: 18px 20px;
    margin-bottom: 20px;
}
.detail-row {
    display: flex;
    gap: 12px;
    margin-bottom: 10px;
    align-items: flex-start;
}
.detail-row:last-child { margin-bottom: 0; }
.detail-row .d-label {
    font-size: 0.78rem;
    font-weight: 700;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    min-width: 140px;
    flex-shrink: 0;
}
.detail-row .d-value {
    font-size: 0.88rem;
    color: #E2E8F0;
    font-weight: 500;
}

/* ─── Submit Button ─── */
.btn-send-invitation {
    background: linear-gradient(135deg, var(--inv-cyan), var(--inv-cyan-light));
    border: none;
    color: #0F172A;
    font-weight: 800;
    font-size: 1.05rem;
    padding: 14px 44px;
    border-radius: 14px;
    transition: all 0.25s;
    box-shadow: 0 6px 25px rgba(6,182,212,0.35);
}
.btn-send-invitation:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(6,182,212,0.5);
    color: #0F172A;
}

/* ─── Truck Badge inside panel ─── */
/* ═════════════════════════════════════════════════════════════
   LIGHT MODE OVERRIDES FOR ADD SHIPMENT INVITATION PAGE
═════════════════════════════════════════════════════════════ */
html.light-theme .inv-card {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 15px 45px rgba(0,0,0,0.06) !important;
}
html.light-theme .inv-card-header {
    background: linear-gradient(135deg, rgba(2,132,199,0.08) 0%, rgba(3,105,161,0.04) 100%) !important;
    border-bottom-color: #E2E8F0 !important;
}
html.light-theme .inv-card-header h4 {
    color: #0F172A !important;
}
html.light-theme .inv-card-header p {
    color: #64748B !important;
}
html.light-theme .inv-card .form-control,
html.light-theme .inv-card .form-select {
    background: #FFFFFF !important;
    border-color: #CBD5E1 !important;
    color: #0F172A !important;
}
html.light-theme .inv-card .form-control:focus,
html.light-theme .inv-card .form-select:focus {
    background: #FFFFFF !important;
    border-color: #0284C7 !important;
    box-shadow: 0 0 0 3px rgba(2,132,199,0.15) !important;
    color: #0F172A !important;
}
html.light-theme .inv-card .form-select option {
    background: #FFFFFF !important;
    color: #0F172A !important;
}
html.light-theme .inv-card .form-label {
    color: #334155 !important;
}
html.light-theme .panel-card {
    background: #F0F9FF !important;
    border-color: #BAE6FD !important;
}
html.light-theme .panel-card .text-white {
    color: #0F172A !important;
}
html.light-theme .panel-card .text-secondary {
    color: #64748B !important;
}
html.light-theme .channel-card {
    background: #F8FAFC !important;
    border-color: #E2E8F0 !important;
}
html.light-theme .channel-card:hover {
    background: #F0F9FF !important;
    border-color: #0284C7 !important;
}
html.light-theme .channel-card.selected {
    background: #E0F2FE !important;
    border-color: #0284C7 !important;
}
html.light-theme .channel-card .ch-title {
    color: #0F172A !important;
}
html.light-theme .channel-card .ch-sub {
    color: #64748B !important;
}
html.light-theme .truck-select-card {
    background: #F8FAFC !important;
    border-color: #E2E8F0 !important;
    color: #0F172A !important;
}
html.light-theme .truck-select-card:hover {
    background: #F0F9FF !important;
    border-color: #0284C7 !important;
}
html.light-theme .truck-select-card.selected {
    background: #E0F2FE !important;
    border-color: #0284C7 !important;
}
html.light-theme .truck-select-card .fw-bold {
    color: #0F172A !important;
}
html.light-theme .truck-badge {
    background: #F1F5F9 !important;
    border-color: #CBD5E1 !important;
    color: #0F172A !important;
}
html.light-theme .modal-sh-detail .modal-content {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    color: #0F172A !important;
    box-shadow: 0 25px 60px rgba(0,0,0,0.15) !important;
}
html.light-theme .modal-sh-detail .modal-header {
    background: linear-gradient(135deg, rgba(2,132,199,0.08) 0%, rgba(3,105,161,0.04) 100%) !important;
    border-bottom-color: #E2E8F0 !important;
}
html.light-theme .modal-sh-detail .modal-header h5,
html.light-theme .modal-sh-detail .modal-header .modal-title {
    color: #0F172A !important;
}
html.light-theme .modal-sh-detail .modal-header .text-muted {
    color: #64748B !important;
}
html.light-theme .modal-sh-detail .btn-close-white {
    filter: invert(1) grayscale(100%) brightness(200%);
}
html.light-theme .modal-sh-detail .modal-footer {
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
.shipment-sub-card {
    background: rgba(15, 23, 42, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #F8FAFC;
}
html.light-theme .shipment-sub-card {
    background: #FFFFFF !important;
    border-color: #CBD5E1 !important;
    color: #0F172A !important;
    box-shadow: 0 4px 15px rgba(0,0,0,0.04) !important;
}
html.light-theme .shipment-sub-card .text-white,
html.light-theme .shipment-sub-card .text-slate-200,
html.light-theme .shipment-sub-card strong,
html.light-theme .shipment-sub-card span {
    color: #0F172A !important;
}
html.light-theme .shipment-sub-card .border-top {
    border-top-color: #E2E8F0 !important;
}
</style>

{{-- Page Header --}}
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold mb-1 header-title">
            <i class="bx bx-send text-info me-2"></i>Invite Driver to Shipment
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Select a driver, inspect their registered trucks, choose a shipment, and send an invitation via your preferred channel.
        </p>
    </div>
    <div>
        <a href="{{ route('all.shipment.invitations') }}" class="btn btn-outline-info rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-list-ul fs-5"></i>
            <span>All Invitations</span>
        </a>
    </div>
</div>

{{-- Main Form Card --}}
<div class="inv-card">
    <div class="inv-card-header">
        <div class="icon-wrap"><i class="bx bx-paper-plane"></i></div>
        <div>
            <h4 class="fw-bold text-white mb-0">Driver Shipment Invitation Form</h4>
            <small class="text-muted">Fill in driver and shipment choices below</small>
        </div>
    </div>
    <div class="inv-card-body">
        <form method="POST" action="{{ route('store.shipment.invitation') }}" id="invitationForm" autocomplete="off">
            @csrf

            {{-- ════ 1. SELECT DRIVER ════ --}}
            <div class="inv-section-title"><i class="bx bx-user me-1"></i>Step 1: Select Driver</div>
            
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-8">
                    <label class="form-label">Driver <span class="text-danger">*</span></label>
                    <select name="driver_id" id="driver_id" class="form-select">
                        <option value="">-- Select a Driver --</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}">
                                {{ $driver->fname }} {{ $driver->lname }} ({{ $driver->phone }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Dynamic Driver Info & Registered Trucks Panel --}}
            <input type="hidden" name="driver_truck_id" id="driver_truck_id" value="">
            <div class="info-display-panel" id="driverInfoPanel">
                <div class="panel-card">
                    <div id="driverLoader" class="text-center py-3" style="display:none;">
                        <span class="spinner-border spinner-border-sm text-info me-2"></span> Loading driver trucks...
                    </div>
                    <div id="driverPanelContent"></div>
                </div>
            </div>

            <div class="my-4" style="height:1px;background:rgba(255,255,255,0.06);"></div>

            {{-- ════ 2. SELECT SHIPMENT & SET DRIVER OFFERED PRICE ════ --}}
            <div class="inv-section-title"><i class="bx bx-package me-1"></i>Step 2: Select Shipment Order & Offered Price</div>

            <div class="row g-4 mb-4">
                <div class="col-12 col-md-7">
                    <label class="form-label">Shipment Order <span class="text-danger">*</span></label>
                    <select name="shipment_id" id="shipment_id" class="form-select">
                        <option value="">-- Select a Shipment Order --</option>
                        @foreach($shipments as $sh)
                            <option value="{{ $sh->id }}" data-price="{{ $sh->initial_price }}">
                                #{{ $sh->id }} — {{ $sh->shipment_name ?: 'Shipment #' . $sh->id }} ({{ $sh->pickupCity->name_en ?? 'Pickup' }} ➔ {{ $sh->dropoffCity->name_en ?? 'Dropoff' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Mandatory Offered Price to Driver Input Field --}}
                <div class="col-12 col-md-5">
                    <label class="form-label text-warning fw-bold">
                        <i class="bx bx-money me-1"></i>Offered Price to Driver (KWD) / السعر المعروض للسائق <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-warning fw-bold">KWD</span>
                        <input type="number" step="0.01" min="0" name="offered_price" id="offered_price" class="form-control text-warning fw-bold fs-5" placeholder="e.g. 150.00" required value="{{ old('offered_price') }}">
                    </div>
                    <small class="text-slate-400" style="font-size: 0.78rem;">Mandatory: This price will be sent and displayed to the driver.</small>
                </div>
            </div>

            {{-- Dynamic Shipment Details Panel --}}
            <div class="info-display-panel" id="shipmentInfoPanel">
                <div class="panel-card">
                    <div id="shipmentLoader" class="text-center py-3" style="display:none;">
                        <span class="spinner-border spinner-border-sm text-info me-2"></span> Loading shipment specifications...
                    </div>
                    <div id="shipmentPanelContent"></div>
                </div>
            </div>

            <div class="my-4" style="height:1px;background:rgba(255,255,255,0.06);"></div>

            {{-- ════ 3. INVITATION CHANNEL ════ --}}
            <div class="inv-section-title"><i class="bx bx-broadcast me-1"></i>Step 3: Choose Invitation Channel</div>

            <input type="hidden" name="channel" id="selectedChannel" value="in_app">

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-4">
                    <div class="channel-card ch-inapp selected" onclick="selectChannel(this, 'in_app')">
                        <i class="bx bx-bell"></i>
                        <div class="ch-title">In-App Notification</div>
                        <div class="ch-sub">Send direct app alert to driver</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="channel-card ch-whatsapp" onclick="selectChannel(this, 'whatsapp')">
                        <i class="bx bxl-whatsapp"></i>
                        <div class="ch-title">WhatsApp Message</div>
                        <div class="ch-sub">Send instant WhatsApp invitation</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="channel-card ch-sms" onclick="selectChannel(this, 'sms')">
                        <i class="bx bx-message-square-dots"></i>
                        <div class="ch-title">SMS Text Message</div>
                        <div class="ch-sub">Send traditional mobile SMS</div>
                    </div>
                </div>
            </div>

            {{-- Submit Action --}}
            <div class="text-end pt-3">
                <button type="submit" class="btn btn-send-invitation d-inline-flex align-items-center gap-2" id="submitBtn">
                    <i class="bx bx-paper-plane fs-5"></i>
                    <span>Send Driver Invitation</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {

    // ── Driver Selection Handler ──
    $('#driver_id').on('change', function() {
        var driverId = $(this).val();
        if (!driverId) {
            $('#driverInfoPanel').removeClass('visible');
            $('#driverPanelContent').empty();
            return;
        }

        $('#driverInfoPanel').addClass('visible');
        $('#driverLoader').show();
        $('#driverPanelContent').empty();

        $.ajax({
            url: '{{ route("get.driver.trucks.ajax", ":id") }}'.replace(':id', driverId),
            type: 'GET',
            success: function(res) {
                $('#driverLoader').hide();
                if (res.status === 'success') {
                    renderDriverTrucks(res.trucks);
                }
            },
            error: function() {
                $('#driverLoader').hide();
                $('#driverPanelContent').html('<div class="text-danger">Failed to fetch driver truck records.</div>');
            }
        });
    });

    function renderDriverTrucks(trucks) {
        var selectedTruckId = $('#driver_truck_id').val();
        var html = '<div class="fw-bold text-white mb-2 d-flex align-items-center justify-content-between"><span><i class="bx bx-truck text-info me-2"></i>Select Driver Truck (from driver_trucks):</span><small class="text-muted" style="font-weight:400;">(Click a truck to select or unselect)</small></div>';
        
        html += '<div class="row g-2 mb-2">';
        
        // Option 1: No Specific Truck
        var isNoneSelected = !selectedTruckId;
        html += '<div class="col-12 col-md-6">';
        html += '<div class="truck-select-card ' + (isNoneSelected ? 'selected' : '') + '" onclick="selectDriverTruck(this, \'\')">';
        html += '<div class="d-flex align-items-center gap-2"><i class="bx bx-minus-circle text-muted fs-4"></i><div><strong class="text-white">No Specific Truck</strong><div class="text-muted" style="font-size:0.78rem;">Proceed without specifying a truck</div></div></div>';
        html += '<i class="bx bx-check-circle check-icon"></i>';
        html += '</div></div>';

        if (trucks && trucks.length > 0) {
            trucks.forEach(function(t) {
                var isSelected = (selectedTruckId && selectedTruckId == t.id);
                var typeName   = t.truck_type ? (t.truck_type.name_en || t.truck_type.name_ar) : 'Truck';
                var subName    = t.truck_sub_type ? (t.truck_sub_type.name_en || t.truck_sub_type.name_ar) : '';
                var brandName  = t.truck_brand ? (t.truck_brand.name_en || t.truck_brand.name_ar) : '';
                var modelName  = t.truck_model ? (t.truck_model.name_en || t.truck_model.name_ar) : '';
                var mfgYear    = t.manufacturing_year ? t.manufacturing_year : '';
                var axles      = t.axles_count ? t.axles_count + ' Axles' : '';
                var plate      = t.plate_number || 'No Plate';

                var brandModelStr = '';
                if (brandName || modelName) {
                    brandModelStr = '<span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-30 me-1" style="font-size:0.75rem;"><i class="bx bx-badge-check me-1"></i>' + brandName + (modelName ? ' ' + modelName : '') + '</span>';
                }
                var specsStr = '';
                if (mfgYear || axles) {
                    specsStr = '<span class="badge bg-secondary bg-opacity-30 text-slate-300 me-1" style="font-size:0.75rem;">' + (mfgYear ? mfgYear + ' ' : '') + (axles ? '(' + axles + ')' : '') + '</span>';
                }

                html += '<div class="col-12 col-md-6">';
                html += '<div class="truck-select-card ' + (isSelected ? 'selected' : '') + '" onclick="selectDriverTruck(this, ' + t.id + ')">';
                html += '<div class="d-flex align-items-center gap-2"><i class="bx bx-truck text-info fs-4"></i><div><div><strong class="text-white">' + typeName + '</strong> ' + (subName ? '<span class="text-info" style="font-size:0.8rem;">(' + subName + ')</span>' : '') + '</div>';
                if (brandModelStr || specsStr) {
                    html += '<div class="mt-1">' + brandModelStr + specsStr + '</div>';
                }
                html += '<div class="text-muted mt-1" style="font-size:0.78rem;"><i class="bx bx-id-card me-1"></i>Plate: ' + plate + '</div></div></div>';
                html += '<i class="bx bx-check-circle check-icon"></i>';
                html += '</div></div>';
            });
        } else {
            html += '<div class="col-12"><div class="p-2 text-warning" style="font-size:0.85rem;"><i class="bx bx-info-circle me-1"></i>No registered trucks found for this driver in driver_trucks table.</div></div>';
        }
        html += '</div>';

        $('#driverPanelContent').html(html);
    }

    window.selectDriverTruck = function(el, truckId) {
        $('.truck-select-card').removeClass('selected');
        $(el).addClass('selected');
        $('#driver_truck_id').val(truckId);
    };

    // ── Shipment Selection Handler ──
    $('#shipment_id').on('change', function() {
        var shipmentId = $(this).val();
        var selectedOpt = $(this).find('option:selected');
        var defaultPrice = selectedOpt.data('price') || selectedOpt.attr('data-price');
        
        if (defaultPrice) {
            $('#offered_price').val(parseFloat(defaultPrice).toFixed(2));
        }

        if (!shipmentId) {
            $('#shipmentInfoPanel').removeClass('visible');
            $('#shipmentPanelContent').empty();
            return;
        }

        $('#shipmentInfoPanel').addClass('visible');
        $('#shipmentLoader').show();
        $('#shipmentPanelContent').empty();

        $.ajax({
            url: '{{ route("get.shipment.data.ajax", ":id") }}'.replace(':id', shipmentId),
            type: 'GET',
            success: function(res) {
                $('#shipmentLoader').hide();
                if (res.status === 'success') {
                    renderShipmentInfo(res.shipment);
                }
            },
            error: function() {
                $('#shipmentLoader').hide();
                $('#shipmentPanelContent').html('<div class="text-danger">Failed to fetch shipment details.</div>');
            }
        });
    });

    $('#offered_price').on('input', function() {
        var val = parseFloat($(this).val()) || 0;
        $('#offered_price_preview').html('<i class="bx bx-money me-1"></i>Offered: KWD ' + val.toFixed(2));
    });

    function renderShipmentInfo(s) {
        var html = '<div class="row g-3">';

        html += '<div class="col-12 col-md-6">';
        html += '<div class="d-flex align-items-center justify-content-between mb-2">';
        html += '<div class="fw-bold text-white" style="font-size:1.05rem;"><i class="bx bx-package text-info me-2"></i>' + s.shipment_name + '</div>';
        html += '<button type="button" class="btn btn-sm btn-outline-info rounded-3 px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1" onclick="openShipmentDetails(' + s.id + ')" title="View Full Specs & 40+ Fields"><i class="bx bx-show fs-5"></i> <span>View Full Details</span></button>';
        html += '</div>';
        html += '<div class="text-muted mb-2" style="font-size:0.85rem;">' + s.shipment_description + '</div>';
        html += '<div class="d-flex flex-wrap gap-2 mb-2">';
        html += '<span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-25">Truck: ' + s.truck_type + '</span>';
        if (s.hs_code) {
            html += '<span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-25" title="' + (s.hs_code_description || '') + '"><i class="bx bx-barcode-reader me-1"></i>HS: ' + s.hs_code + '</span>';
        }
        if (s.weight !== '—') html += '<span class="badge bg-secondary">Weight: ' + s.weight + ' kg</span>';
        if (s.is_fragile) html += '<span class="badge bg-danger">Fragile</span>';
        html += '</div>';
        html += '</div>';

        html += '<div class="col-12 col-md-6">';
        html += '<div class="p-3 rounded-3 shipment-sub-card">';
        html += '<div class="d-flex align-items-center gap-2 mb-2"><i class="bx bx-map-pin text-info fs-5"></i><strong>Pickup:</strong> <span class="text-white">' + s.pickup_city + ' (' + s.pickup_address + ')</span></div>';
        html += '<div class="d-flex align-items-center gap-2 mb-2"><i class="bx bx-navigation text-success fs-5"></i><strong>Dropoff:</strong> <span class="text-white">' + s.dropoff_city + ' (' + s.dropoff_address + ')</span></div>';
        html += '<div class="d-flex align-items-center justify-content-between pt-2 border-top border-secondary border-opacity-25 flex-wrap gap-2">';
        html += '<span>Customer: <strong class="text-slate-200">' + s.customer_name + '</strong></span>';
        var offeredVal = parseFloat($('#offered_price').val()) || parseFloat(s.initial_price) || 0;
        html += '<div class="d-flex align-items-center gap-2">';
        html += '<span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-30 px-2.5 py-1.5 fw-bold fs-7" title="Original Shipment Price"><i class="bx bx-receipt me-1"></i>Shipment Price: KWD ' + parseFloat(s.initial_price).toFixed(2) + '</span>';
        html += '<span class="text-warning fw-bold fs-6" id="offered_price_preview"><i class="bx bx-money me-1"></i>Offered: KWD ' + offeredVal.toFixed(2) + '</span>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        html += '</div>';
        $('#shipmentPanelContent').html(html);
    }

    // ── Channel Card Selection ──
    window.selectChannel = function(el, ch) {
        $('.channel-card').removeClass('selected');
        $(el).addClass('selected');
        $('#selectedChannel').val(ch);
    };

    // Form submit state
    $('#invitationForm').on('submit', function() {
        $('#submitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Sending Invitation...');
    });

});

// ── Open Shipment Details Modal via AJAX ──
function openShipmentDetails(id) {
    var modal = new bootstrap.Modal(document.getElementById('shipmentDetailsModal'));
    $('#modalLoader').show();
    $('#modalDataWrap').hide().empty();
    modal.show();

    $.ajax({
        url: '{{ route("get.shipment.details.ajax", ":id") }}'.replace(':id', id),
        type: 'GET',
        success: function(res) {
            $('#modalLoader').hide();
            if (res.status === 'success') {
                renderDetailsModal(res.shipment);
                $('#modalDataWrap').show();
            } else {
                $('#modalDataWrap').html('<div class="alert alert-danger">Failed to load details.</div>').show();
            }
        },
        error: function() {
            $('#modalLoader').hide();
            $('#modalDataWrap').html('<div class="alert alert-danger">Error fetching shipment data.</div>').show();
        }
    });
}

function renderDetailsModal(s) {
    $('#modalTitle').text((s.shipment_name && s.shipment_name !== '—' ? s.shipment_name : 'Shipment Order') + ' #' + s.id);

    var html = '';

    // SECTION 1: General & Classification
    html += '<div class="detail-sec-title"><i class="bx bx-info-circle me-1"></i>General Specification & Classification</div>';
    html += '<div class="detail-card-grid"><div class="row g-2">';
    html += dRow('Shipment ID', '#' + s.id);
    html += dRow('Shipment Name', s.shipment_name);
    html += dRow('Status', '<span class="badge bg-info text-dark fw-bold">' + formatStatus(s.status) + '</span>');
    html += dRow('Shipment Type', s.shipment_type);
    html += dRow('Shipment Nature', s.shipment_nature);
    if (s.hs_code) {
        html += dRow('HS Tariff Code', '<span class="badge bg-info text-dark fw-bold me-1">HS: ' + s.hs_code + '</span>' + (s.hs_code_description ? '<small class="text-slate-300 d-block mt-0.5">' + s.hs_code_description + '</small>' : ''));
    }
    html += dRow('Fragile Cargo', s.is_fragile ? '<span class="text-danger fw-bold"><i class="bx bx-error-circle me-1"></i>YES</span>' : 'NO');
    html += dRow('Description', s.shipment_description);
    html += dRow('Created At', s.created_at);
    html += '</div></div>';

    // SECTION 2: Customer & Driver Information
    html += '<div class="detail-sec-title"><i class="bx bx-user me-1"></i>Customer & Driver Profiles</div>';
    html += '<div class="detail-card-grid"><div class="row g-2">';
    if (s.customer) {
        html += dRow('Customer Name', s.customer.name);
        html += dRow('Customer Phone', s.customer.phone);
        html += dRow('Customer Email', s.customer.email);
        html += dRow('Customer Type', s.customer.role === 'company_customer' ? 'Company (' + (s.customer.company_name || '—') + ')' : 'Individual');
    } else {
        html += dRow('Customer', '—');
    }
    if (s.driver) {
        html += dRow('Assigned Driver', s.driver.name + ' (' + s.driver.phone + ')');
    } else {
        html += dRow('Assigned Driver', '<span class="text-warning">Unassigned</span>');
    }
    html += '</div></div>';

    // SECTION 3: Route & Locations
    html += '<div class="detail-sec-title"><i class="bx bx-map me-1"></i>Pickup & Delivery Locations</div>';
    html += '<div class="detail-card-grid"><div class="row g-3">';
    html += '<div class="col-12 col-md-6 border-end border-secondary border-opacity-25 pe-3">';
    html += '<h6 class="text-info font-bold mb-2"><i class="bx bx-map-pin me-1"></i>Pickup Location</h6>';
    html += dRow('Country / City', s.pickup.country + ' / ' + s.pickup.city);
    html += dRow('Area', s.pickup.area);
    html += dRow('Address', s.pickup.address);
    if (s.pickup.lat && s.pickup.lng) {
        html += dRow('Coordinates', s.pickup.lat + ', ' + s.pickup.lng + ' <a href="https://maps.google.com/?q=' + s.pickup.lat + ',' + s.pickup.lng + '" target="_blank" class="text-info ms-1"><i class="bx bx-link-external"></i> Map</a>');
    }
    html += '</div>';

    html += '<div class="col-12 col-md-6 ps-3">';
    html += '<h6 class="text-success font-bold mb-2"><i class="bx bx-navigation me-1"></i>Delivery Location</h6>';
    html += dRow('Country / City', s.dropoff.country + ' / ' + s.dropoff.city);
    html += dRow('Area', s.dropoff.area);
    html += dRow('Address', s.dropoff.address);
    if (s.dropoff.lat && s.dropoff.lng) {
        html += dRow('Coordinates', s.dropoff.lat + ', ' + s.dropoff.lng + ' <a href="https://maps.google.com/?q=' + s.dropoff.lat + ',' + s.dropoff.lng + '" target="_blank" class="text-success ms-1"><i class="bx bx-link-external"></i> Map</a>');
    }
    html += '</div>';
    html += '</div></div>';

    // SECTION 4: Loading & Receiving Contacts
    html += '<div class="detail-sec-title"><i class="bx bx-phone me-1"></i>On-Site Contacts</div>';
    html += '<div class="detail-card-grid"><div class="row g-2">';
    html += dRow('Loading Contact Name', s.loading_contact_name);
    html += dRow('Loading Contact Phone', s.loading_contact_phone);
    html += dRow('Receiving Contact Name', s.arrival_contact_name);
    html += dRow('Receiving Contact Phone', s.arrival_contact_phone);
    html += '</div></div>';

    // SECTION 5: Cargo Specs & Pricing
    html += '<div class="detail-sec-title"><i class="bx bx-box me-1"></i>Cargo Specs & Pricing</div>';
    html += '<div class="detail-card-grid"><div class="row g-2">';
    html += dRow('Truck Type', s.truck_type);
    html += dRow('Truck Sub-Type', s.truck_sub_type);
    html += dRow('Dimensions (L×W×H)', s.dimensions);
    html += dRow('Weight', s.weight !== '—' ? s.weight + ' kg' : '—');
    html += dRow('No. of Pieces', s.packages_count);
    html += dRow('Goods Description', s.goods_description);
    html += dRow('Estimated Price', '<strong class="text-warning">KWD ' + s.initial_price + '</strong>');
    html += dRow('Payment Status', '<span class="badge bg-secondary">' + s.payment_status + '</span>');
    html += dRow('Payment Method', s.payment_method);
    html += '</div></div>';

    // SECTION 6: Timestamps & Delays
    html += '<div class="detail-sec-title"><i class="bx bx-time me-1"></i>Timestamps & Tracking Events</div>';
    html += '<div class="detail-card-grid"><div class="row g-2">';
    html += dRow('Scheduled Date', s.scheduled_date);
    html += dRow('Driver Arrival at Loading', s.driver_arrival_at_loading);
    html += dRow('Loading Start / End', s.loading_start_at + ' / ' + s.loading_end_at);
    html += dRow('Trip Start', s.trip_start_at);
    html += dRow('Unloading Start / End', s.unloading_start_at + ' / ' + s.unloading_end_at);
    html += dRow('Delay Reason', s.delay_reason);
    html += '</div></div>';

    $('#modalDataWrap').html(html);
}

function dRow(label, value) {
    return '<div class="col-12 col-md-6"><div class="detail-row"><div class="d-label">' + label + '</div><div class="d-value">' + (value || '—') + '</div></div></div>';
}

function formatStatus(st) {
    if (!st) return '—';
    return st.replace(/_/g, ' ').toUpperCase();
}
</script>

{{-- FULL DETAILS MODAL DRAWER --}}
<div class="modal fade modal-sh-detail" id="shipmentDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-info bg-opacity-10 p-2 border border-info border-opacity-25 text-info">
                        <i class="bx bx-package fs-3"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="modalTitle">Shipment Details</h5>
                        <small class="text-muted" id="modalSubtitle">Full specs & 40+ database fields</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBodyContent">
                <div class="text-center py-5" id="modalLoader">
                    <div class="spinner-border text-info" role="status"></div>
                    <div class="text-info mt-2">Loading full shipment specification...</div>
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
