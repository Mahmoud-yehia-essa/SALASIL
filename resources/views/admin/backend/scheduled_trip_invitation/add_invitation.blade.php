@extends('admin.master_admin')
@section('admin')

{{-- ==============================================================
     INVITE DRIVER TO SCHEDULED TRIP ORDER (DISPATCH USER'S ORDER)
============================================================== --}}

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

/* ─── Dynamic Info Panels ─── */
.info-display-panel {
    transition: all 0.4s cubic-bezier(0.22,1,0.36,1);
    overflow: hidden;
    max-height: 0;
    opacity: 0;
    margin-top: 14px;
}
.info-display-panel.visible {
    max-height: 1400px;
    opacity: 1;
}

.panel-card {
    background: rgba(6,182,212,0.04);
    border: 1px dashed rgba(6,182,212,0.3);
    border-radius: 16px;
    padding: 22px;
}

/* ─── Truck Select Cards ─── */
.truck-select-card {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 14px;
    padding: 14px 18px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.truck-select-card:hover {
    background: rgba(6,182,212,0.08);
    border-color: rgba(6,182,212,0.4);
}
.truck-select-card.selected {
    background: rgba(6,182,212,0.14);
    border-color: var(--inv-cyan);
    box-shadow: 0 0 15px rgba(6,182,212,0.25);
}
.truck-select-card .check-icon {
    font-size: 20px;
    color: var(--inv-cyan);
    opacity: 0;
    transition: all 0.2s;
}
.truck-select-card.selected .check-icon { opacity: 1; }

/* ─── Channel Select Cards ─── */
.channel-card {
    background: rgba(255,255,255,0.03);
    border: 1.5px solid rgba(255,255,255,0.08);
    border-radius: 16px;
    padding: 18px;
    cursor: pointer;
    transition: all 0.25s;
    text-align: center;
    position: relative;
}
.channel-card:hover {
    background: rgba(255,255,255,0.06);
    border-color: rgba(255,255,255,0.2);
    transform: translateY(-2px);
}
.channel-card.selected {
    background: rgba(6,182,212,0.12);
    border-color: var(--inv-cyan);
    box-shadow: 0 8px 25px rgba(6,182,212,0.25);
}
.channel-card i {
    font-size: 32px;
    margin-bottom: 8px;
    display: inline-block;
}
.channel-card.ch-inapp i { color: #38BDF8; }
.channel-card.ch-whatsapp i { color: #22C55E; }
.channel-card.ch-sms i { color: #F59E0B; }
.channel-card .ch-title { font-weight: 700; color: #F8FAFC; font-size: 0.95rem; margin-bottom: 2px; }
.channel-card .ch-sub { font-size: 0.78rem; color: #94A3B8; }

/* ─── Modal Details Drawer ─── */
.modal-sh-detail .modal-dialog { max-width: 850px; }
.modal-sh-detail .modal-content {
    background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%);
    border: 1px solid rgba(255,255,255,0.12);
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
    overflow: hidden;
}
.detail-row {
    display: flex;
    gap: 12px;
    margin-bottom: 10px;
    align-items: flex-start;
    min-width: 0;
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
    flex: 1;
    min-width: 0;
    word-break: break-word;
    overflow-wrap: anywhere;
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

.order-sub-card {
    background: rgba(15, 23, 42, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #F8FAFC;
}

/* ═════════════════════════════════════════════════════════════
   LIGHT MODE OVERRIDES
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
html.light-theme .inv-card-header h4 { color: #0F172A !important; }
html.light-theme .inv-card-header p { color: #64748B !important; }
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
html.light-theme .inv-card .form-select option { background: #FFFFFF !important; color: #0F172A !important; }
html.light-theme .inv-card .form-label { color: #334155 !important; }
html.light-theme .panel-card {
    background: #F0F9FF !important;
    border-color: #BAE6FD !important;
}
html.light-theme .panel-card .text-white { color: #0F172A !important; }
html.light-theme .panel-card .text-secondary { color: #64748B !important; }
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
html.light-theme .channel-card .ch-title { color: #0F172A !important; }
html.light-theme .channel-card .ch-sub { color: #64748B !important; }
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
html.light-theme .truck-select-card .fw-bold { color: #0F172A !important; }
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
html.light-theme .modal-sh-detail .modal-header .modal-title { color: #0F172A !important; }
html.light-theme .modal-sh-detail .modal-header .text-muted { color: #64748B !important; }
html.light-theme .modal-sh-detail .btn-close-white { filter: invert(1) grayscale(100%) brightness(200%); }
html.light-theme .modal-sh-detail .modal-footer {
    background: #F8FAFC !important;
    border-top-color: #E2E8F0 !important;
}
html.light-theme .detail-sec-title { color: #0284C7 !important; }
html.light-theme .detail-sec-title::after { background: linear-gradient(90deg, rgba(2,132,199,0.3), transparent) !important; }
html.light-theme .detail-card-grid {
    background: #F8FAFC !important;
    border-color: #E2E8F0 !important;
}
html.light-theme .detail-row .d-label { color: #475569 !important; font-weight: 800 !important; }
html.light-theme .detail-row .d-value { color: #0F172A !important; font-weight: 700 !important; }
html.light-theme .order-sub-card {
    background: #FFFFFF !important;
    border-color: #CBD5E1 !important;
    color: #0F172A !important;
    box-shadow: 0 4px 15px rgba(0,0,0,0.04) !important;
}
html.light-theme .order-sub-card .text-white,
html.light-theme .order-sub-card .text-slate-200,
html.light-theme .order-sub-card strong,
html.light-theme .order-sub-card span {
    color: #0F172A !important;
}
html.light-theme .order-sub-card .border-top { border-top-color: #E2E8F0 !important; }
</style>

{{-- Page Header --}}
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold mb-1 header-title">
            <i class="bx bx-calendar-event text-info me-2"></i>Invite Driver to Scheduled Trip Order
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Select a driver, inspect their registered trucks, choose a customer's scheduled trip order from Scheduled Trip Orders, and send an invitation.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('all.scheduled.trip.orders') }}" class="btn btn-outline-secondary rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-receipt fs-5"></i>
            <span>Scheduled Trip Orders</span>
        </a>
        <a href="{{ route('all.scheduled.trip.invitations') }}" class="btn btn-outline-info rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-list-ul fs-5"></i>
            <span>All Trip Invitations</span>
        </a>
    </div>
</div>

{{-- Main Form Card --}}
<div class="inv-card">
    <div class="inv-card-header">
        <div class="icon-wrap"><i class="bx bx-paper-plane"></i></div>
        <div>
            <h4 class="fw-bold text-white mb-0">Driver Scheduled Trip Order Invitation Form</h4>
            <small class="text-muted">Dispatch a booked scheduled trip order from customers to a certified driver</small>
        </div>
    </div>
    <div class="inv-card-body">
        <form method="POST" action="{{ route('store.scheduled.trip.invitation') }}" id="invitationForm" autocomplete="off">
            @csrf

            {{-- ════ 1. SELECT DRIVER ════ --}}
            <div class="inv-section-title"><i class="bx bx-user me-1"></i>Step 1: Select Driver</div>
            
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-8">
                    <label class="form-label">Driver <span class="text-danger">*</span></label>
                    <select name="driver_id" id="driver_id" class="form-select" required>
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

            {{-- ════ 2. SELECT SCHEDULED TRIP ORDER & SET DRIVER OFFERED PRICE ════ --}}
            <div class="inv-section-title"><i class="bx bx-receipt me-1"></i>Step 2: Select Scheduled Trip Order & Offered Price</div>

            <div class="row g-4 mb-4">
                <div class="col-12 col-md-7">
                    <label class="form-label">Scheduled Trip Order / طلب الرحلة المجدولة <span class="text-danger">*</span></label>
                    <select name="scheduled_trip_order_id" id="scheduled_trip_order_id" class="form-select" required>
                        <option value="">-- Select a Scheduled Trip Order (from Scheduled Trip Orders) --</option>
                        @foreach($orders as $o)
                            @php
                                $orderNum   = $o->order_number ?: ('#STO-' . $o->id);
                                $customer   = $o->customer ? ($o->customer->fname . ' ' . $o->customer->lname) : 'Customer';
                                $originCity = $o->pickupCity->name_en ?? ($o->scheduledTrip->route->originCity->name_en ?? 'Origin');
                                $destCity   = $o->dropoffCity->name_en ?? ($o->scheduledTrip->route->destinationCity->name_en ?? 'Destination');
                                $dateStr    = $o->loading_date ? date('Y-m-d', strtotime($o->loading_date)) : ($o->scheduledTrip->trip_date ? date('Y-m-d', strtotime($o->scheduledTrip->trip_date)) : 'Open Date');
                                $truckName  = $o->truckType->name_en ?? ($o->scheduledTrip->truckType->name_en ?? 'Truck');
                                $priceVal   = $o->total_price ?: ($o->trip_price ?: 0);
                                $isPreselected = ($selectedOrderId && ($selectedOrderId == $o->id || $selectedOrderId == $o->scheduled_trip_id));
                            @endphp
                            <option value="{{ $o->id }}" 
                                    data-price="{{ $priceVal }}" 
                                    {{ $isPreselected ? 'selected' : '' }}>
                                {{ $orderNum }} — {{ $customer }} | {{ $originCity }} ➔ {{ $destCity }} ({{ $dateStr }} | {{ $truckName }} | KWD {{ number_format($priceVal, 2) }})
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
                    <small class="text-slate-400" style="font-size: 0.78rem;">Mandatory: This price will be presented directly to the driver in the invitation.</small>
                </div>
            </div>

            {{-- Dynamic Scheduled Trip Order Details Panel --}}
            <div class="info-display-panel" id="orderInfoPanel">
                <div class="panel-card">
                    <div id="orderLoader" class="text-center py-3" style="display:none;">
                        <span class="spinner-border spinner-border-sm text-info me-2"></span> Loading customer order specifications & cargo details...
                    </div>
                    <div id="orderPanelContent"></div>
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

{{-- ════ FULL DETAILS MODAL DRAWER ════ --}}
<div class="modal fade modal-sh-detail" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold text-white mb-0 d-flex align-items-center gap-2" id="modalOrderTitle">
                        <i class="bx bx-calendar-event text-info"></i>
                        <span>Scheduled Trip Order Details</span>
                    </h5>
                    <small class="text-muted" id="modalOrderSubtitle">Complete customer cargo & route breakdown</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modalLoader" class="text-center py-5">
                    <span class="spinner-border text-info me-2"></span> Loading order specifications...
                </div>
                <div id="modalDataWrap" style="display:none;"></div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <span class="text-muted fs-8">SALASIL Scheduled Trip Order Dossier</span>
                <button type="button" class="btn btn-outline-secondary rounded-3 px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
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
            url: '{{ route("get.scheduled.trip.driver.trucks.ajax", ":id") }}'.replace(':id', driverId),
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

    // ── Scheduled Trip Order Selection Handler ──
    $('#scheduled_trip_order_id').on('change', function() {
        var orderId = $(this).val();
        var selectedOpt = $(this).find('option:selected');
        var defaultPrice = selectedOpt.data('price') || selectedOpt.attr('data-price');
        
        if (defaultPrice) {
            $('#offered_price').val(parseFloat(defaultPrice).toFixed(2));
        }

        if (!orderId) {
            $('#orderInfoPanel').removeClass('visible');
            $('#orderPanelContent').empty();
            return;
        }

        $('#orderInfoPanel').addClass('visible');
        $('#orderLoader').show();
        $('#orderPanelContent').empty();

        $.ajax({
            url: '{{ route("get.scheduled.trip.order.data.ajax", ":id") }}'.replace(':id', orderId),
            type: 'GET',
            success: function(res) {
                $('#orderLoader').hide();
                if (res.status === 'success') {
                    renderOrderInfo(res.order);
                }
            },
            error: function() {
                $('#orderLoader').hide();
                $('#orderPanelContent').html('<div class="text-danger">Failed to fetch scheduled trip order specifications.</div>');
            }
        });
    });

    $('#offered_price').on('input', function() {
        var val = parseFloat($(this).val()) || 0;
        $('#offered_price_preview').html('<i class="bx bx-money me-1"></i>Offered to Driver: KWD ' + val.toFixed(2));
    });

    function renderOrderInfo(o) {
        var html = '<div class="row g-3">';

        html += '<div class="col-12 col-md-6">';
        html += '<div class="d-flex align-items-center justify-content-between mb-2">';
        html += '<div class="fw-bold text-white" style="font-size:1.05rem;"><i class="bx bx-receipt text-info me-2"></i>' + o.order_number + ' — ' + o.route_title + '</div>';
        html += '<button type="button" class="btn btn-sm btn-outline-info rounded-3 px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1" onclick="openOrderDetails(' + o.id + ')" title="View Full Order & Cargo Specs"><i class="bx bx-show fs-5"></i> <span>Full Order Dossier</span></button>';
        html += '</div>';

        html += '<div class="text-muted mb-2" style="font-size:0.85rem;"><i class="bx bx-user me-1 text-info"></i>Customer: <strong class="text-white">' + o.customer_name + '</strong>' + (o.customer_company ? ' (' + o.customer_company + ')' : '') + ' &bull; <i class="bx bx-phone ms-1 text-info"></i>' + o.customer_phone + '</div>';
        html += '<div class="text-muted mb-2" style="font-size:0.85rem;"><i class="bx bx-trip me-1 text-info"></i>Route: <strong>' + o.origin_city + ', ' + o.origin_country + '</strong> ➔ <strong>' + o.destination_city + ', ' + o.destination_country + '</strong> (' + o.estimated_distance + ')</div>';

        html += '<div class="d-flex flex-wrap gap-2 mb-2">';
        html += '<span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-25">Truck: ' + o.truck_type + (o.truck_sub_type !== '—' ? ' (' + o.truck_sub_type + ')' : '') + '</span>';
        html += '<span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25"><i class="bx bx-calendar me-1"></i>Pickup: ' + o.loading_date + '</span>';
        if (o.loading_time !== 'Flexible Time') {
            html += '<span class="badge bg-secondary"><i class="bx bx-time me-1"></i>' + o.loading_time + '</span>';
        }
        if (o.customs_clearance) {
            html += '<span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25"><i class="bx bx-check-shield me-1"></i>Customs Clearance</span>';
        }
        if (o.is_fragile) {
            html += '<span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-25"><i class="bx bx-error me-1"></i>Fragile Cargo</span>';
        }
        if (o.weight !== '—') {
            html += '<span class="badge bg-secondary">Weight: ' + o.weight + '</span>';
        }
        html += '</div>';
        html += '</div>';

        html += '<div class="col-12 col-md-6">';
        html += '<div class="p-3 rounded-3 order-sub-card">';
        html += '<div class="d-flex align-items-center gap-2 mb-2"><i class="bx bx-map-pin text-info fs-5"></i><strong>Pickup Address:</strong> <span class="text-white">' + o.pickup_address + ' (' + o.origin_city + ')</span></div>';
        html += '<div class="d-flex align-items-center gap-2 mb-2"><i class="bx bx-navigation text-success fs-5"></i><strong>Delivery Address:</strong> <span class="text-white">' + o.dropoff_address + ' (' + o.destination_city + ')</span></div>';
        html += '<div class="d-flex align-items-center gap-2 mb-2"><i class="bx bx-box text-warning fs-5"></i><strong>Cargo:</strong> <span class="text-white">' + o.shipment_name + ' (' + o.packages_count + ' Packages, ' + o.dimensions + ')</span></div>';

        html += '<div class="d-flex align-items-center justify-content-between pt-2 border-top border-secondary border-opacity-25 flex-wrap gap-2">';
        html += '<span>Status: <strong class="text-slate-200 text-capitalize">' + o.status + '</strong></span>';
        var offeredVal = parseFloat($('#offered_price').val()) || parseFloat(o.total_price_raw) || 0;
        html += '<div class="d-flex align-items-center gap-2">';
        html += '<span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-30 px-2.5 py-1.5 fw-bold fs-7" title="Customer Order Total Price"><i class="bx bx-receipt me-1"></i>Order Total: KWD ' + parseFloat(o.total_price_raw).toFixed(2) + '</span>';
        html += '<span class="text-warning fw-bold fs-6" id="offered_price_preview"><i class="bx bx-money me-1"></i>Offered to Driver: KWD ' + offeredVal.toFixed(2) + '</span>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        html += '</div>';
        $('#orderPanelContent').html(html);
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

    // Auto-trigger if order preselected
    if ($('#scheduled_trip_order_id').val()) {
        $('#scheduled_trip_order_id').trigger('change');
    }

});

// ── Open Scheduled Trip Order Details Modal via AJAX ──
function openOrderDetails(id) {
    var modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    $('#modalLoader').show();
    $('#modalDataWrap').hide().empty();
    modal.show();

    $.ajax({
        url: '{{ route("get.scheduled.trip.order.data.ajax", ":id") }}'.replace(':id', id),
        type: 'GET',
        success: function(res) {
            $('#modalLoader').hide();
            if (res.status === 'success') {
                renderDetailsModal(res.order);
                $('#modalDataWrap').show();
            } else {
                $('#modalDataWrap').html('<div class="alert alert-danger">Failed to load order specifications.</div>').show();
            }
        },
        error: function() {
            $('#modalLoader').hide();
            $('#modalDataWrap').html('<div class="alert alert-danger">Error communicating with server.</div>').show();
        }
    });
}

function renderDetailsModal(o) {
    var html = '';

    // Order & Customer Overview
    html += '<div class="detail-sec-title"><i class="bx bx-receipt me-1"></i>Order & Customer Overview</div>';
    html += '<div class="detail-card-grid">';
    html += '<div class="row g-2">';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Order Number:</span><span class="d-value text-info fw-bold">' + o.order_number + '</span></div></div>';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Customer Name:</span><span class="d-value text-white fw-bold">' + o.customer_name + '</span></div></div>';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Customer Phone:</span><span class="d-value">' + o.customer_phone + '</span></div></div>';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Company:</span><span class="d-value">' + (o.customer_company || 'Individual Account') + '</span></div></div>';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Order Status:</span><span class="d-value badge bg-info bg-opacity-20 text-info text-capitalize">' + o.status + '</span></div></div>';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Payment Status:</span><span class="d-value text-capitalize fw-bold text-success">' + o.payment_status + '</span></div></div>';
    html += '</div></div>';

    // Route & Schedule Breakdown
    html += '<div class="detail-sec-title"><i class="bx bx-trip me-1"></i>Route, Schedule & Addresses</div>';
    html += '<div class="detail-card-grid">';
    html += '<div class="row g-2">';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Origin:</span><span class="d-value text-info fw-bold">' + o.origin_city + ' (' + o.origin_country + ')</span></div></div>';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Destination:</span><span class="d-value text-success fw-bold">' + o.destination_city + ' (' + o.destination_country + ')</span></div></div>';
    html += '<div class="col-12"><div class="detail-row"><span class="d-label">Pickup Address:</span><span class="d-value text-white">' + o.pickup_address + '</span></div></div>';
    html += '<div class="col-12"><div class="detail-row"><span class="d-label">Delivery Address:</span><span class="d-value text-white">' + o.dropoff_address + '</span></div></div>';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Loading Date/Time:</span><span class="d-value text-warning fw-bold">' + o.loading_date + ' &bull; ' + o.loading_time + '</span></div></div>';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Receiving Date/Time:</span><span class="d-value text-white fw-bold">' + o.receiving_date + ' &bull; ' + o.receiving_time + '</span></div></div>';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Loading Contact:</span><span class="d-value">' + o.loading_contact + '</span></div></div>';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Arrival Contact:</span><span class="d-value">' + o.arrival_contact + '</span></div></div>';
    html += '</div></div>';

    // Cargo & Truck Specifications
    html += '<div class="detail-sec-title"><i class="bx bx-box me-1"></i>Cargo & Truck Requirements</div>';
    html += '<div class="detail-card-grid">';
    html += '<div class="row g-2">';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Shipment Name:</span><span class="d-value text-white fw-bold">' + o.shipment_name + '</span></div></div>';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Shipment Nature/Type:</span><span class="d-value">' + o.shipment_nature + ' / ' + o.shipment_type + '</span></div></div>';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Weight:</span><span class="d-value">' + o.weight + '</span></div></div>';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Packages Count:</span><span class="d-value">' + o.packages_count + ' Packages</span></div></div>';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Dimensions:</span><span class="d-value">' + o.dimensions + '</span></div></div>';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Truck Required:</span><span class="d-value text-white fw-bold">' + o.truck_type + (o.truck_sub_type !== '—' ? ' (' + o.truck_sub_type + ')' : '') + '</span></div></div>';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Customs Clearance:</span><span class="d-value">' + (o.customs_clearance ? '<span class="text-success fw-bold"><i class="bx bx-check-circle me-1"></i>Yes, Included</span>' : '<span class="text-muted">No</span>') + '</span></div></div>';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Fragile Goods:</span><span class="d-value">' + (o.is_fragile ? '<span class="text-danger fw-bold"><i class="bx bx-error me-1"></i>Yes, Fragile Cargo</span>' : '<span class="text-muted">No</span>') + '</span></div></div>';
    html += '</div></div>';

    // Pricing & Special Notes
    html += '<div class="detail-sec-title"><i class="bx bx-money me-1"></i>Commercial Pricing & Instructions</div>';
    html += '<div class="detail-card-grid">';
    html += '<div class="row g-2">';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Trip Unit Price:</span><span class="d-value text-info fw-bold">KWD ' + o.trip_price + '</span></div></div>';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Total Order Value:</span><span class="d-value text-warning fw-bold fs-6">KWD ' + o.total_price + '</span></div></div>';
    html += '<div class="col-12"><div class="detail-row"><span class="d-label">Pickup Notes:</span><span class="d-value">' + o.pickup_instructions + '</span></div></div>';
    html += '<div class="col-12"><div class="detail-row"><span class="d-label">Delivery Notes:</span><span class="d-value">' + o.delivery_instructions + '</span></div></div>';
    html += '<div class="col-12"><div class="detail-row"><span class="d-label">General Notes:</span><span class="d-value">' + o.notes + '</span></div></div>';
    html += '</div></div>';

    $('#modalDataWrap').html(html);
}
</script>

@endsection
