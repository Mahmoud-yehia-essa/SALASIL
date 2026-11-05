@extends('admin.master_admin')
@section('admin')

{{-- =====================================
     EDIT SCHEDULED TRIP DRIVER INVITATION
===================================== --}}

<style>
:root {
    --inv-cyan:       #06B6D4;
    --inv-cyan-light: #38BDF8;
    --inv-navy:       #0F172A;
    --inv-card:       #1E293B;
    --inv-border:     rgba(255,255,255,0.08);
    --inv-success:    #10B981;
    --inv-warning:    #F59E0B;
}

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
    background: rgba(6,182,212,0.06);
    border: 1px solid rgba(6,182,212,0.2);
    border-radius: 16px;
    padding: 20px 24px;
    backdrop-filter: blur(8px);
}

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

.channel-card {
    background: rgba(255,255,255,0.03);
    border: 1.5px solid rgba(255,255,255,0.08);
    border-radius: 16px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.25s;
    text-align: center;
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
    font-size: 28px;
    margin-bottom: 6px;
    display: inline-block;
}
.channel-card.ch-inapp i { color: #38BDF8; }
.channel-card.ch-whatsapp i { color: #22C55E; }
.channel-card.ch-sms i { color: #F59E0B; }
.channel-card .ch-title { font-weight: 700; color: #F8FAFC; font-size: 0.9rem; margin-bottom: 2px; }

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

/* Light mode overrides */
html.light-theme .inv-card { background: #FFFFFF !important; border-color: #E2E8F0 !important; }
html.light-theme .inv-card-header { background: linear-gradient(135deg, rgba(2,132,199,0.08) 0%, rgba(3,105,161,0.04) 100%) !important; border-bottom-color: #E2E8F0 !important; }
html.light-theme .inv-card-header h4 { color: #0F172A !important; }
html.light-theme .inv-card .form-control, html.light-theme .inv-card .form-select { background: #FFFFFF !important; border-color: #CBD5E1 !important; color: #0F172A !important; }
html.light-theme .inv-card .form-select option { background: #FFFFFF !important; color: #0F172A !important; }
html.light-theme .inv-card .form-label { color: #334155 !important; }
html.light-theme .panel-card { background: #F0F9FF !important; border-color: #BAE6FD !important; }
html.light-theme .channel-card { background: #F8FAFC !important; border-color: #E2E8F0 !important; }
html.light-theme .channel-card.selected { background: #E0F2FE !important; border-color: #0284C7 !important; }
html.light-theme .channel-card .ch-title { color: #0F172A !important; }
html.light-theme .truck-select-card { background: #F8FAFC !important; border-color: #E2E8F0 !important; color: #0F172A !important; }
html.light-theme .truck-select-card.selected { background: #E0F2FE !important; border-color: #0284C7 !important; }
html.light-theme .order-sub-card { background: #FFFFFF !important; border-color: #CBD5E1 !important; color: #0F172A !important; }
</style>

{{-- Page Header --}}
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold mb-1 header-title">
            <i class="bx bx-edit text-info me-2"></i>Edit Scheduled Trip Driver Invitation
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Update driver, scheduled trip order, offered price, channel, or status for this invitation record.
        </p>
    </div>
    <div>
        <a href="{{ route('all.scheduled.trip.invitations') }}" class="btn btn-outline-info rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-left-arrow-alt fs-5"></i>
            <span>Back to All Invitations</span>
        </a>
    </div>
</div>

{{-- Main Form Card --}}
<div class="inv-card">
    <div class="inv-card-header">
        <div class="icon-wrap"><i class="bx bx-edit-alt"></i></div>
        <div>
            <h4 class="fw-bold text-white mb-0">Update Scheduled Trip Invitation Record</h4>
            <small class="text-muted">Invitation #{{ $invitation->id }}</small>
        </div>
    </div>
    <div class="inv-card-body">
        <form method="POST" action="{{ route('update.scheduled.trip.invitation') }}" id="invitationForm" autocomplete="off">
            @csrf
            <input type="hidden" name="id" value="{{ $invitation->id }}">

            {{-- ════ 1. SELECT DRIVER ════ --}}
            <div class="inv-section-title"><i class="bx bx-user me-1"></i>Driver Selection</div>
            
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-8">
                    <label class="form-label">Driver <span class="text-danger">*</span></label>
                    <select name="driver_id" id="driver_id" class="form-select" required>
                        <option value="">-- Select a Driver --</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ $invitation->driver_id == $driver->id ? 'selected' : '' }}>
                                {{ $driver->fname }} {{ $driver->lname }} ({{ $driver->phone }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Dynamic Driver Info & Registered Trucks Panel --}}
            <input type="hidden" name="driver_truck_id" id="driver_truck_id" value="{{ $invitation->driver_truck_id }}">
            <div class="info-display-panel visible" id="driverInfoPanel">
                <div class="panel-card">
                    <div id="driverLoader" class="text-center py-3" style="display:none;">
                        <span class="spinner-border spinner-border-sm text-info me-2"></span> Loading driver trucks...
                    </div>
                    <div id="driverPanelContent"></div>
                </div>
            </div>

            <div class="my-4" style="height:1px;background:rgba(255,255,255,0.06);"></div>

            {{-- ════ 2. SELECT SCHEDULED TRIP ORDER & SET DRIVER OFFERED PRICE ════ --}}
            <div class="inv-section-title"><i class="bx bx-receipt me-1"></i>Scheduled Trip Order & Offered Price</div>

            <div class="row g-4 mb-4">
                <div class="col-12 col-md-7">
                    <label class="form-label">Scheduled Trip Order <span class="text-danger">*</span></label>
                    <select name="scheduled_trip_order_id" id="scheduled_trip_order_id" class="form-select" required>
                        <option value="">-- Select a Scheduled Trip Order --</option>
                        @foreach($orders as $o)
                            @php
                                $orderNum   = $o->order_number ?: ('#STO-' . $o->id);
                                $customer   = $o->customer ? ($o->customer->fname . ' ' . $o->customer->lname) : 'Customer';
                                $originCity = $o->pickupCity->name_en ?? ($o->scheduledTrip->route->originCity->name_en ?? 'Origin');
                                $destCity   = $o->dropoffCity->name_en ?? ($o->scheduledTrip->route->destinationCity->name_en ?? 'Destination');
                                $dateStr    = $o->loading_date ? date('Y-m-d', strtotime($o->loading_date)) : ($o->scheduledTrip->trip_date ? date('Y-m-d', strtotime($o->scheduledTrip->trip_date)) : 'Open Date');
                                $truckName  = $o->truckType->name_en ?? ($o->scheduledTrip->truckType->name_en ?? 'Truck');
                                $priceVal   = $o->total_price ?: ($o->trip_price ?: 0);
                                $isSelected = ($invitation->scheduled_trip_order_id == $o->id || (!$invitation->scheduled_trip_order_id && $invitation->scheduled_trip_id == $o->scheduled_trip_id));
                            @endphp
                            <option value="{{ $o->id }}" 
                                    data-price="{{ $priceVal }}" 
                                    {{ $isSelected ? 'selected' : '' }}>
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
                        <input type="number" step="0.01" min="0" name="offered_price" id="offered_price" class="form-control text-warning fw-bold fs-5" placeholder="e.g. 150.00" required value="{{ old('offered_price', $invitation->offered_price) }}">
                    </div>
                    <small class="text-slate-400" style="font-size: 0.78rem;">Mandatory: This price will be presented directly to the driver.</small>
                </div>
            </div>

            {{-- Dynamic Scheduled Trip Order Details Panel --}}
            <div class="info-display-panel visible" id="orderInfoPanel">
                <div class="panel-card">
                    <div id="orderLoader" class="text-center py-3" style="display:none;">
                        <span class="spinner-border spinner-border-sm text-info me-2"></span> Loading scheduled trip order details...
                    </div>
                    <div id="orderPanelContent"></div>
                </div>
            </div>

            <div class="my-4" style="height:1px;background:rgba(255,255,255,0.06);"></div>

            {{-- ════ 3. INVITATION CHANNEL & STATUS ════ --}}
            <div class="inv-section-title"><i class="bx bx-cog me-1"></i>Channel & Status Settings</div>

            <input type="hidden" name="channel" id="selectedChannel" value="{{ $invitation->channel }}">

            <div class="row g-4 mb-4">
                <div class="col-12 col-md-8">
                    <label class="form-label">Invitation Channel <span class="text-danger">*</span></label>
                    <div class="row g-3">
                        <div class="col-4">
                            <div class="channel-card ch-inapp {{ $invitation->channel === 'in_app' ? 'selected' : '' }}" onclick="selectChannel(this, 'in_app')">
                                <i class="bx bx-bell"></i>
                                <div class="ch-title">In-App Alert</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="channel-card ch-whatsapp {{ $invitation->channel === 'whatsapp' ? 'selected' : '' }}" onclick="selectChannel(this, 'whatsapp')">
                                <i class="bx bxl-whatsapp"></i>
                                <div class="ch-title">WhatsApp</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="channel-card ch-sms {{ $invitation->channel === 'sms' ? 'selected' : '' }}" onclick="selectChannel(this, 'sms')">
                                <i class="bx bx-message-square-dots"></i>
                                <div class="ch-title">SMS</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label">Invitation Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select">
                        <option value="pending" {{ $invitation->status==='pending' ? 'selected' : '' }}>Pending</option>
                        <option value="accepted" {{ $invitation->status==='accepted' ? 'selected' : '' }}>Accepted</option>
                        <option value="rejected" {{ $invitation->status==='rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="canceled" {{ $invitation->status==='canceled' ? 'selected' : '' }}>Canceled</option>
                    </select>
                </div>

                <div class="col-12" id="rejection_reason_wrap" style="{{ $invitation->status === 'rejected' ? '' : 'display:none;' }}">
                    <label class="form-label">Rejection Reason (سبب الرفض)</label>
                    <textarea name="rejection_reason" class="form-control" rows="2" placeholder="Specify why driver declined scheduled trip...">{{ $invitation->rejection_reason }}</textarea>
                </div>
            </div>

            {{-- Submit Action --}}
            <div class="text-end pt-3">
                <button type="submit" class="btn btn-send-invitation d-inline-flex align-items-center gap-2" id="submitBtn">
                    <i class="bx bx-save fs-5"></i>
                    <span>Update Invitation Record</span>
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
                        <i class="bx bx-receipt text-info"></i>
                        <span>Scheduled Trip Order Details</span>
                    </h5>
                    <small class="text-muted" id="modalOrderSubtitle">Complete specifications & route breakdown</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modalLoader" class="text-center py-5">
                    <span class="spinner-border text-info me-2"></span> Loading order specs...
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

    // Toggle rejection reason textarea
    $('#status').on('change', function() {
        if ($(this).val() === 'rejected') {
            $('#rejection_reason_wrap').slideDown(200);
        } else {
            $('#rejection_reason_wrap').slideUp(200);
        }
    });

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
        $('#offered_price_preview').html('<i class="bx bx-money me-1"></i>Offered: KWD ' + val.toFixed(2));
    });

    function renderOrderInfo(o) {
        var html = '<div class="row g-3">';

        html += '<div class="col-12 col-md-6">';
        html += '<div class="d-flex align-items-center justify-content-between mb-2">';
        html += '<div class="fw-bold text-white" style="font-size:1.05rem;"><i class="bx bx-receipt text-info me-2"></i>' + o.order_number + ' — ' + o.route_title + '</div>';
        html += '<button type="button" class="btn btn-sm btn-outline-info rounded-3 px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1" onclick="openOrderDetails(' + o.id + ')" title="View Full Order Specs"><i class="bx bx-show fs-5"></i> <span>View Full Details</span></button>';
        html += '</div>';
        html += '<div class="text-muted mb-2" style="font-size:0.85rem;"><i class="bx bx-user me-1 text-info"></i>Customer: <strong>' + o.customer_name + '</strong>' + (o.customer_company ? ' (' + o.customer_company + ')' : '') + ' &bull; ' + o.customer_phone + '</div>';
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
        html += '</div>';
        html += '</div>';

        html += '<div class="col-12 col-md-6">';
        html += '<div class="p-3 rounded-3 order-sub-card">';
        html += '<div class="d-flex align-items-center gap-2 mb-2"><i class="bx bx-map-pin text-info fs-5"></i><strong>Pickup:</strong> <span class="text-white">' + o.pickup_address + ' (' + o.origin_city + ')</span></div>';
        html += '<div class="d-flex align-items-center gap-2 mb-2"><i class="bx bx-navigation text-success fs-5"></i><strong>Delivery:</strong> <span class="text-white">' + o.dropoff_address + ' (' + o.destination_city + ')</span></div>';
        html += '<div class="d-flex align-items-center justify-content-between pt-2 border-top border-secondary border-opacity-25 flex-wrap gap-2">';
        html += '<span>Status: <strong class="text-slate-200 text-capitalize">' + o.status + '</strong></span>';
        var offeredVal = parseFloat($('#offered_price').val()) || parseFloat(o.total_price_raw) || 0;
        html += '<div class="d-flex align-items-center gap-2">';
        html += '<span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-30 px-2.5 py-1.5 fw-bold fs-7" title="Order Total Value"><i class="bx bx-receipt me-1"></i>Total: KWD ' + parseFloat(o.total_price_raw).toFixed(2) + '</span>';
        html += '<span class="text-warning fw-bold fs-6" id="offered_price_preview"><i class="bx bx-money me-1"></i>Offered: KWD ' + offeredVal.toFixed(2) + '</span>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        html += '</div>';
        $('#orderPanelContent').html(html);
    }

    window.selectChannel = function(el, ch) {
        $('.channel-card').removeClass('selected');
        $(el).addClass('selected');
        $('#selectedChannel').val(ch);
    };

    // Trigger initial renders
    $('#driver_id').trigger('change');
    $('#scheduled_trip_order_id').trigger('change');

});

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
    html += '<div class="detail-sec-title"><i class="bx bx-receipt me-1"></i>Order Specifications</div>';
    html += '<div class="detail-card-grid"><div class="row g-2">';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Order Number:</span><span class="d-value text-info fw-bold">' + o.order_number + '</span></div></div>';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Customer:</span><span class="d-value text-white fw-bold">' + o.customer_name + '</span></div></div>';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Origin:</span><span class="d-value">' + o.origin_city + ' (' + o.origin_country + ')</span></div></div>';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Destination:</span><span class="d-value">' + o.destination_city + ' (' + o.destination_country + ')</span></div></div>';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Loading Date:</span><span class="d-value text-warning fw-bold">' + o.loading_date + '</span></div></div>';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Cargo:</span><span class="d-value">' + o.shipment_name + ' (' + o.weight + ')</span></div></div>';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Truck Type:</span><span class="d-value text-white fw-bold">' + o.truck_type + '</span></div></div>';
    html += '<div class="col-12 col-md-6"><div class="detail-row"><span class="d-label">Order Value:</span><span class="d-value text-info fw-bold">KWD ' + o.total_price + '</span></div></div>';
    html += '<div class="col-12"><div class="detail-row"><span class="d-label">Notes:</span><span class="d-value">' + o.notes + '</span></div></div>';
    html += '</div></div>';

    $('#modalDataWrap').html(html);
}
</script>

@endsection
