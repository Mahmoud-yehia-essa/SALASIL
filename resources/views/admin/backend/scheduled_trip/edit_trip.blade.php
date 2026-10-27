@extends('admin.master_admin')
@section('admin')

{{-- ========================
     EDIT SCHEDULED TRIP
======================== --}}

<style>
:root {
    --tp-cyan:       #06B6D4;
    --tp-cyan-light: #38BDF8;
    --tp-navy:       #0F172A;
    --tp-border:     rgba(255,255,255,0.08);
}

.tp-card {
    background: linear-gradient(135deg, rgba(30,41,59,0.95) 0%, rgba(15,23,42,0.98) 100%);
    border: 1px solid var(--tp-border);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0,0,0,0.4);
}
.tp-card-header {
    background: linear-gradient(135deg, rgba(6,182,212,0.15) 0%, rgba(56,189,248,0.08) 100%);
    border-bottom: 1px solid rgba(6,182,212,0.2);
    padding: 24px 32px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.tp-card-header .icon-wrap {
    width: 52px; height: 52px;
    background: linear-gradient(135deg, var(--tp-cyan), var(--tp-cyan-light));
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: #fff;
    box-shadow: 0 8px 20px rgba(6,182,212,0.35);
    flex-shrink: 0;
}
.tp-card-body { padding: 32px; }

.tp-card .form-control,
.tp-card .form-select {
    background: rgba(255,255,255,0.05) !important;
    border: 1px solid rgba(255,255,255,0.12) !important;
    color: #F8FAFC !important;
    border-radius: 12px !important;
    padding: 12px 16px !important;
    transition: all 0.25s !important;
    font-size: 0.92rem !important;
}
.tp-card .form-control:focus,
.tp-card .form-select:focus {
    background: rgba(6,182,212,0.08) !important;
    border-color: var(--tp-cyan) !important;
    box-shadow: 0 0 0 3px rgba(6,182,212,0.15) !important;
    color: #F8FAFC !important;
}
.tp-card .form-select option { background: #1E293B; color: #F8FAFC; }
.tp-card .form-label { color: #CBD5E1 !important; font-weight: 700 !important; font-size: 0.88rem !important; margin-bottom: 8px; }

.tp-section-title {
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--tp-cyan-light);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.tp-section-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, rgba(6,182,212,0.4), transparent);
}

.btn-save-trip {
    background: linear-gradient(135deg, var(--tp-cyan), var(--tp-cyan-light));
    border: none;
    color: #0F172A;
    font-weight: 800;
    font-size: 1.05rem;
    padding: 14px 44px;
    border-radius: 14px;
    transition: all 0.25s;
    box-shadow: 0 6px 25px rgba(6,182,212,0.35);
}
.btn-save-trip:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(6,182,212,0.5);
    color: #0F172A;
}

.tp-card input[type="date"],
.tp-card input[type="time"] {
    color-scheme: dark;
    cursor: pointer;
}
.tp-card input[type="date"]::-webkit-calendar-picker-indicator,
.tp-card input[type="time"]::-webkit-calendar-picker-indicator {
    filter: invert(1) sepia(100%) saturate(500%) hue-rotate(160deg);
    cursor: pointer;
    opacity: 0.9;
}

/* ─── Document Selection Cards ─── */
.doc-preset-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 14px;
    margin-bottom: 20px;
}
.doc-preset-card {
    background: rgba(255,255,255,0.04);
    border: 1.5px solid rgba(255,255,255,0.1);
    border-radius: 14px;
    padding: 14px 16px;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: flex-start;
    gap: 12px;
    position: relative;
    user-select: none;
}
.doc-preset-card:hover {
    border-color: rgba(6,182,212,0.45);
    background: rgba(6,182,212,0.06);
    transform: translateY(-2px);
}
.doc-preset-card.active {
    background: rgba(6,182,212,0.12);
    border-color: var(--tp-cyan);
    box-shadow: 0 4px 16px rgba(6,182,212,0.22);
}
.doc-preset-card .doc-icon-wrap {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: rgba(6,182,212,0.15);
    color: var(--tp-cyan-light);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
    transition: all 0.2s;
}
.doc-preset-card.active .doc-icon-wrap {
    background: var(--tp-cyan);
    color: #0F172A;
}
.doc-preset-card .doc-info {
    flex: 1;
}
.doc-preset-card .doc-title {
    font-weight: 700;
    font-size: 0.9rem;
    color: #F8FAFC;
    margin-bottom: 3px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.doc-preset-card .doc-sub {
    font-size: 0.76rem;
    color: #94A3B8;
    line-height: 1.35;
    margin-bottom: 0;
}
.doc-preset-card .doc-check {
    width: 18px;
    height: 18px;
    border-radius: 6px;
    border: 1.5px solid rgba(255,255,255,0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    color: transparent;
    transition: all 0.2s;
    flex-shrink: 0;
}
.doc-preset-card.active .doc-check {
    border-color: var(--tp-cyan);
    background: var(--tp-cyan);
    color: #0F172A;
}

html.light-theme .doc-preset-card {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
}
html.light-theme .doc-preset-card:hover {
    border-color: #38BDF8 !important;
    background: #F0F9FF !important;
}
html.light-theme .doc-preset-card.active {
    background: #F0FDF4 !important;
    border-color: #0284C7 !important;
}
html.light-theme .doc-preset-card .doc-title {
    color: #0F172A !important;
}
html.light-theme .doc-preset-card .doc-sub {
    color: #64748B !important;
}

/* ─── Customs Clearance Toggle (Matching App Screen) ─── */
.customs-toggle-group {
    display: flex;
    gap: 12px;
}
.btn-customs-toggle {
    flex: 1;
    background: rgba(255,255,255,0.04);
    border: 1.5px solid rgba(255,255,255,0.12);
    border-radius: 12px;
    padding: 10px 16px;
    color: #94A3B8;
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    transition: all 0.25s ease;
    user-select: none;
    text-align: left;
    min-height: 58px;
}
.btn-customs-toggle i {
    font-size: 1.6rem;
    transition: transform 0.2s;
    flex-shrink: 0;
}
.btn-customs-toggle:hover {
    background: rgba(255,255,255,0.07);
    border-color: rgba(255,255,255,0.25);
    color: #F8FAFC;
    transform: translateY(-1px);
}
.btn-customs-toggle.active-yes {
    background: rgba(6,182,212,0.12) !important;
    border-color: var(--tp-cyan) !important;
    color: #38BDF8 !important;
    box-shadow: 0 0 0 3px rgba(6,182,212,0.18);
}
.btn-customs-toggle.active-yes i {
    color: var(--tp-cyan);
    transform: scale(1.1);
}
.btn-customs-toggle.active-no {
    background: rgba(244,63,94,0.08) !important;
    border-color: var(--tp-danger) !important;
    color: #FB7185 !important;
    box-shadow: 0 0 0 3px rgba(244,63,94,0.18);
}
.btn-customs-toggle.active-no i {
    color: var(--tp-danger);
    transform: scale(1.1);
}

/* Light Theme Overrides for Customs Toggle */
html.light-theme .btn-customs-toggle {
    background: #F8FAFC !important;
    border-color: #CBD5E1 !important;
    color: #475569 !important;
}
html.light-theme .btn-customs-toggle:hover {
    background: #F1F5F9 !important;
    border-color: #94A3B8 !important;
    color: #0F172A !important;
}
html.light-theme .btn-customs-toggle.active-yes {
    background: #F0F9FF !important;
    border-color: #0284C7 !important;
    color: #0369A1 !important;
}
html.light-theme .btn-customs-toggle.active-no {
    background: #FFF1F2 !important;
    border-color: #E11D48 !important;
    color: #BE123C !important;
}
</style>

{{-- Page Header --}}
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold mb-1 header-title">
            <i class="bx bx-edit text-info me-2"></i>Edit Scheduled Trip #{{ $trip->id }}
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Update route, truck type, capacity, price, departure date, time, or status.
        </p>
    </div>
    <div>
        <a href="{{ route('all.scheduled.trips') }}" class="btn btn-outline-info rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-left-arrow-alt fs-5"></i>
            <span>Back to All Trips</span>
        </a>
    </div>
</div>

{{-- Main Form Card --}}
<div class="tp-card">
    <div class="tp-card-header">
        <div class="icon-wrap"><i class="bx bx-edit-alt"></i></div>
        <div>
            <h4 class="fw-bold text-white mb-0">Update Trip #{{ $trip->id }}</h4>
            <small class="text-muted">Modify scheduled trip details below</small>
        </div>
    </div>
    <div class="tp-card-body">
        @if($errors->any())
            <div class="alert alert-danger border-0 rounded-4 p-3 mb-4 shadow-sm" style="background: rgba(244,63,94,0.18); border: 1px solid rgba(244,63,94,0.4) !important;">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bx bx-error-circle fs-4 text-danger"></i>
                    <h6 class="fw-bold mb-0 text-danger">Validation Errors (تنبيه: يوجد أخطاء في البيانات المدخلة يرجى تصحيحها):</h6>
                </div>
                <ul class="mb-0 ps-3 text-white">
                    @foreach($errors->all() as $error)
                        <li style="margin-bottom: 4px;">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('update.scheduled.trip') }}" id="tripForm" autocomplete="off">
            @csrf
            <input type="hidden" name="id" value="{{ $trip->id }}">

            {{-- ════ 1. ROUTE SELECTION ════ --}}
            <div class="tp-section-title"><i class="bx bx-git-repo-forked me-1"></i>Step 1: Select Fixed Route</div>
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-8">
                    <label class="form-label">Route <span class="text-danger">*</span></label>
                    <select name="route_id" id="route_id" class="form-select">
                        <option value="">-- Select a Fixed Route --</option>
                        @foreach($routes as $rt)
                            <option value="{{ $rt->id }}" {{ $trip->route_id == $rt->id ? 'selected' : '' }}>
                                Route #{{ $rt->id }}: {{ $rt->originCity->name_en ?? 'Origin' }} ({{ $rt->originCountry->name_en ?? '' }}) ➔ {{ $rt->destinationCity->name_en ?? 'Destination' }} ({{ $rt->destinationCountry->name_en ?? '' }}) [{{ ucfirst($rt->quote_type) }}]
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Trip Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select">
                        <option value="published" {{ $trip->status === 'published' ? 'selected' : '' }}>Published (Open)</option>
                        <option value="boarding" {{ $trip->status === 'boarding' ? 'selected' : '' }}>Boarding</option>
                        <option value="in_transit" {{ $trip->status === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                        <option value="completed" {{ $trip->status === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="canceled" {{ $trip->status === 'canceled' ? 'selected' : '' }}>Canceled</option>
                    </select>
                </div>
            </div>

            <div class="my-4" style="height:1px;background:rgba(255,255,255,0.06);"></div>

            {{-- ════ 2. TRUCK & CARGO SPECS ════ --}}
            <div class="tp-section-title"><i class="bx bx-truck me-1"></i>Step 2: Truck Specifications & Quantities</div>
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6">
                    <label class="form-label">Truck Type <span class="text-danger">*</span></label>
                    <select name="truck_type_id" id="truck_type_id" class="form-select">
                        <option value="">-- Select Truck Type --</option>
                        @foreach($truckTypes as $type)
                            <option value="{{ $type->id }}" {{ $trip->truck_type_id == $type->id ? 'selected' : '' }}>{{ $type->name_en }} ({{ $type->name_ar }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Truck Sub-Type (Optional)</label>
                    <select name="truck_sub_type_id" id="truck_sub_type_id" class="form-select">
                        <option value="">-- Select Sub-Type (Optional) --</option>
                        @foreach($truckSubTypes as $sub)
                            <option value="{{ $sub->id }}" {{ $trip->truck_sub_type_id == $sub->id ? 'selected' : '' }}>{{ $sub->name_en }} ({{ $sub->name_ar || $sub->name_en }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Number of Trucks <span class="text-danger">*</span></label>
                    <input type="number" name="number_of_trucks" id="number_of_trucks" class="form-control" placeholder="1" value="{{ old('number_of_trucks', $trip->number_of_trucks) }}" min="1">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Total Approx. Weight (Tons)</label>
                    <div class="input-group">
                        <input type="number" step="0.1" name="total_weight_ton" class="form-control" placeholder="e.g. 24.5" value="{{ old('total_weight_ton', $trip->total_weight_ton) }}">
                        <span class="input-group-text bg-dark border-secondary border-opacity-25 text-info fw-bold">Tons</span>
                    </div>
                </div>

                {{-- Shipment Type & Shipment Nature --}}
                <div class="col-12 col-md-6">
                    <label class="form-label d-flex justify-content-between align-items-center">
                        <span><i class="bx bx-package text-info me-1"></i>Shipment Type (نوع الشحنة)</span>
                        <small class="text-muted">Optional</small>
                    </label>
                    <select name="shipment_type_id" id="shipment_type_id" class="form-select">
                        <option value="">-- All / General Shipment Types (الكل / عام) --</option>
                        @foreach($shipmentTypes as $st)
                            <option value="{{ $st->id }}" {{ old('shipment_type_id', $trip->shipment_type_id) == $st->id ? 'selected' : '' }}>
                                {{ $st->name_en }} @if(!empty($st->name_ar)) - {{ $st->name_ar }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label d-flex justify-content-between align-items-center">
                        <span><i class="bx bx-category text-info me-1"></i>Shipment Nature (طبيعة الشحنة)</span>
                        <small class="text-muted">Optional</small>
                    </label>
                    <select name="shipment_nature_id" id="shipment_nature_id" class="form-select">
                        <option value="">-- All / General Natures (الكل / عام) --</option>
                        @foreach($shipmentNatures as $sn)
                            <option value="{{ $sn->id }}" {{ old('shipment_nature_id', $trip->shipment_nature_id) == $sn->id ? 'selected' : '' }}>
                                {{ $sn->name_en }} @if(!empty($sn->name_ar)) - {{ $sn->name_ar }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Customs Clearance Toggle (As in App Search) --}}
                <div class="col-12 col-md-6">
                    <label class="form-label d-flex justify-content-between align-items-center">
                        <span><i class="bx bx-shield-quarter text-info me-1"></i>Customs Clearance <span class="text-info">(التخليص الجمركي)</span></span>
                        <small class="text-muted">Matches App Search</small>
                    </label>
                    @php $isCustoms = old('customs_clearance', $trip->customs_clearance ? 1 : 0); @endphp
                    <input type="hidden" name="customs_clearance" id="customs_clearance_input" value="{{ $isCustoms }}">
                    <div class="customs-toggle-group">
                        <button type="button" class="btn-customs-toggle {{ $isCustoms == 1 ? 'active-yes' : '' }}" data-val="1">
                            <i class="bx bx-check-circle"></i>
                            <div>
                                <div class="fw-bold">Yes</div>
                                <small style="font-size:0.74rem;opacity:0.85;">Customs Included (يشمل التخليص)</small>
                            </div>
                        </button>
                        <button type="button" class="btn-customs-toggle {{ $isCustoms == 0 ? 'active-no' : '' }}" data-val="0">
                            <i class="bx bx-x-circle"></i>
                            <div>
                                <div class="fw-bold">No</div>
                                <small style="font-size:0.74rem;opacity:0.85;">No Clearance (بدون تخليص)</small>
                            </div>
                        </button>
                    </div>
                </div>

                {{-- Cargo & Carrier Notes --}}
                <div class="col-12 col-md-6">
                    <label class="form-label">Cargo & Carrier Notes (ملاحظات الشحنة والرحلة)</label>
                    <textarea name="notes" class="form-control" rows="2" style="height:58px;" placeholder="Any extra details for the carrier (optional)...">{{ old('notes', $trip->notes) }}</textarea>
                </div>
            </div>

            <div class="my-4" style="height:1px;background:rgba(255,255,255,0.06);"></div>

            {{-- ════ 3. DRIVER & SCHEDULED TIME ════ --}}
            <div class="tp-section-title"><i class="bx bx-time me-1"></i>Step 3: Driver & Schedule Timing</div>
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-4">
                    <label class="form-label">Assigned Driver (Optional)</label>
                    <select name="driver_id" id="driver_id" class="form-select">
                        <option value="">-- Unassigned (Assign Later) --</option>
                        @foreach($drivers as $d)
                            <option value="{{ $d->id }}" {{ $trip->driver_id == $d->id ? 'selected' : '' }}>
                                {{ $d->fname }} {{ $d->lname }} ({{ $d->phone }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Trip Date <span class="badge bg-secondary bg-opacity-25 text-warning ms-1" style="font-size:0.72rem;">غير فعال / يحدد لاحقاً في الطلب</span></label>
                    <input type="date" name="trip_date" class="form-control" value="{{ old('trip_date', $trip->trip_date) }}" disabled style="background-color: rgba(255,255,255,0.05); cursor: not-allowed; opacity: 0.6;">
                    <div class="form-text text-muted" style="font-size:0.75rem;"><i class="bx bx-lock-alt me-1 text-warning"></i>تم تعطيل الحقل هنا، ويتم تحديد التاريخ لاحقاً عند إنشاء الطلب (Add Scheduled Trip Order).</div>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Trip Departure Time <span class="badge bg-secondary bg-opacity-25 text-warning ms-1" style="font-size:0.72rem;">غير فعال / يحدد لاحقاً</span></label>
                    <input type="time" name="trip_time" class="form-control" value="{{ old('trip_time', $trip->trip_time) }}" disabled style="background-color: rgba(255,255,255,0.05); cursor: not-allowed; opacity: 0.6;">
                    <div class="form-text text-muted" style="font-size:0.75rem;"><i class="bx bx-lock-alt me-1 text-warning"></i>تم تعطيل الحقل هنا، ويتم تحديد الوقت لاحقاً عند إنشاء الطلب.</div>
                </div>
            </div>

            <div class="my-4" style="height:1px;background:rgba(255,255,255,0.06);"></div>

            {{-- ════ 4. PRICING & FLEXIBLE ADDITIONS ════ --}}
            <div class="tp-section-title"><i class="bx bx-purchase-tag-alt me-1"></i>Step 4: Pricing & Flexible Additions (التسعير والإضافات المرنة)</div>
            <div class="row g-4 mb-3">
                <div class="col-12 col-md-6">
                    <label class="form-label">Price per Unit / Order (KWD) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="0" id="base_price_input" name="price" class="form-control" placeholder="0.00" value="{{ old('price', $trip->price) }}">
                        <span class="input-group-text bg-dark border-secondary border-opacity-25 text-warning fw-bold">KWD</span>
                    </div>
                    <div class="form-text text-muted" style="font-size:0.75rem;">السعر الأساسي لكل وحدة/شاحنة بالدينار الكويتي.</div>
                </div>
                {{-- Open Booking Capacity without limit --}}
                <input type="hidden" name="total_capacity" value="{{ $trip->total_capacity ?: 9999 }}">
                <input type="hidden" name="available_capacity" value="{{ $trip->available_capacity ?: 9999 }}">
            </div>

            {{-- Dynamic Pricing Additions Box --}}
            <div class="card border border-secondary border-opacity-25 rounded-3 mb-4" style="background: rgba(255, 255, 255, 0.02);">
                <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 py-3">
                    <div>
                        <h6 class="mb-0 text-white d-flex align-items-center">
                            <i class="bx bx-slider-alt text-warning me-2 fs-5"></i>
                            إضافات السعر المرنة (Flexible Price Additions)
                        </h6>
                        <small class="text-muted">أضف أي رسوم إضافية مثل نسبة ضرائب، نسبة ربح، رسوم جمركية، أو مبالغ مقطوعة تظهر للعميل في التطبيق بدقة.</small>
                    </div>
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 btn-add-preset" data-name="ضريبة القيمة المضافة (VAT)" data-type="percentage" data-val="15">
                            <i class="bx bx-plus me-1"></i>+ ضريبة 15%
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 btn-add-preset" data-name="هامش ربح تشغيلي" data-type="percentage" data-val="10">
                            <i class="bx bx-plus me-1"></i>+ ربح 10%
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 btn-add-preset" data-name="رسوم تخليص جمركي" data-type="fixed" data-val="25">
                            <i class="bx bx-plus me-1"></i>+ تخليص 25 KWD
                        </button>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" id="btnAddPricingRow">
                            <i class="bx bx-plus-circle me-1"></i>إضافة بند مخصص
                        </button>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-dark table-borderless align-middle mb-0" id="pricingAdditionsTable">
                            <thead>
                                <tr class="text-muted small border-bottom border-secondary border-opacity-25">
                                    <th style="min-width: 220px;">اسم الإضافة / البند (Name / Description)</th>
                                    <th style="min-width: 170px;">نوع الإضافة (Type)</th>
                                    <th style="min-width: 140px;">القيمة (Value)</th>
                                    <th style="min-width: 150px;">الأثر المحسوب (Impact)</th>
                                    <th style="width: 50px;" class="text-center">حذف</th>
                                </tr>
                            </thead>
                            <tbody id="pricingAdditionsBody">
                                <!-- Dynamic rows -->
                            </tbody>
                        </table>
                    </div>

                    <div id="noPricingAdditionsMsg" class="text-center py-3 text-muted small">
                        <i class="bx bx-info-circle me-1"></i>لا توجد إضافات سعر حالياً. السعر النهائي للرحلة سيكون السعر الأساسي فقط بدون أي إضافات.
                    </div>

                    <!-- Live Calculation Summary -->
                    <div class="mt-3 p-3 rounded-3" style="background: rgba(0, 0, 0, 0.25); border: 1px dashed rgba(255,255,255,0.1);">
                        <div class="row align-items-center g-2 text-center text-md-start">
                            <div class="col-12 col-md-4 text-muted small">
                                <span>السعر الأساسي: </span>
                                <strong class="text-white" id="summaryBasePrice">0.00 KWD</strong>
                            </div>
                            <div class="col-12 col-md-4 text-muted small">
                                <span>مجموع الإضافات (لكل وحدة): </span>
                                <strong class="text-warning" id="summaryAdditionsTotal">+0.00 KWD</strong>
                            </div>
                            <div class="col-12 col-md-4 text-md-end">
                                <span class="text-muted small">إجمالي السعر للوحدة الواحدة: </span>
                                <strong class="text-success fs-6" id="summaryFinalUnitPrice">0.00 KWD</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="my-4" style="height:1px;background:rgba(255,255,255,0.06);"></div>

            {{-- ════ 5. REQUIRED TRIP DOCUMENTS ════ --}}
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
                <div class="tp-section-title mb-0"><i class="bx bx-file me-1"></i>Step 5: Required Trip Documents (المستندات المطلوبة للرحلة)</div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3" id="btnSelectRecommendedDocs">
                        <i class="bx bx-check-double me-1"></i>Recommended (3)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" id="btnSelectAllDocs">
                        Select All
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="btnClearAllDocs">
                        Clear
                    </button>
                </div>
            </div>
            <p class="text-slate-400 small mb-3">
                Select the required documents that the customer / booking user must upload in the order wizard (Step 7: Required Documents).
            </p>

            <div class="doc-preset-grid" id="docPresetGrid">
                @php
                    $presetDocs = [
                        [
                            'name' => 'Commercial Invoice',
                            'name_ar' => 'الفاتورة التجارية',
                            'desc' => 'Official price & cargo value breakdown (PDF)',
                            'icon' => 'bx bx-receipt',
                            'rec' => true,
                        ],
                        [
                            'name' => 'Packing List',
                            'name_ar' => 'قائمة التعبئة',
                            'desc' => 'Detailed item inventory, dimensions & weight (PDF/XLS)',
                            'icon' => 'bx bx-list-check',
                            'rec' => true,
                        ],
                        [
                            'name' => 'Customs Clearance Certificate',
                            'name_ar' => 'شهادة التخليص الجمركي',
                            'desc' => 'Border / port customs import & export clearance (PDF)',
                            'icon' => 'bx bx-shield-quarter',
                            'rec' => true,
                        ],
                        [
                            'name' => 'Cargo Photo / Inspection Scan',
                            'name_ar' => 'صورة البضاعة / فحص الشحنة',
                            'desc' => 'Pre-loading visual inspection photo or cargo scan (JPG/PNG)',
                            'icon' => 'bx bx-camera',
                            'rec' => false,
                        ],
                        [
                            'name' => 'Bill of Lading / Waybill',
                            'name_ar' => 'بوليصة الشحن',
                            'desc' => 'Carrier contract, consignor receipt & manifest (PDF)',
                            'icon' => 'bx bx-file-blank',
                            'rec' => false,
                        ],
                        [
                            'name' => 'Certificate of Origin',
                            'name_ar' => 'شهادة المنشأ',
                            'desc' => 'Chamber of commerce country of origin certificate (PDF)',
                            'icon' => 'bx bx-badge-check',
                            'rec' => false,
                        ],
                        [
                            'name' => 'Dangerous Goods / Hazmat Declaration',
                            'name_ar' => 'إقرار المواد الخطرة',
                            'desc' => 'Mandatory handling declaration for regulated chemical goods',
                            'icon' => 'bx bx-error',
                            'rec' => false,
                        ],
                    ];
                    $savedDocs = is_array($trip->required_documents) ? $trip->required_documents : (json_decode($trip->required_documents, true) ?: []);
                    $oldDocs = old('required_documents', !empty($savedDocs) ? $savedDocs : ['Commercial Invoice', 'Packing List', 'Customs Clearance Certificate']);
                    $presetNames = array_column($presetDocs, 'name');
                    $customExistingDocs = array_diff($oldDocs ?? [], $presetNames);
                @endphp

                @foreach($presetDocs as $doc)
                    @php $isActive = in_array($doc['name'], $oldDocs ?? []); @endphp
                    <div class="doc-preset-card {{ $isActive ? 'active' : '' }}" data-name="{{ $doc['name'] }}" data-rec="{{ $doc['rec'] ? '1' : '0' }}">
                        <input type="checkbox" name="required_documents[]" value="{{ $doc['name'] }}" class="doc-checkbox d-none" {{ $isActive ? 'checked' : '' }}>
                        <div class="doc-icon-wrap"><i class="{{ $doc['icon'] }}"></i></div>
                        <div class="doc-info">
                            <div class="doc-title">
                                <span>{{ $doc['name'] }}</span>
                                <div class="doc-check"><i class="bx bx-check"></i></div>
                            </div>
                            <small class="text-info d-block mb-1" style="font-size:0.75rem;">{{ $doc['name_ar'] }}</small>
                            <p class="doc-sub">{{ $doc['desc'] }}</p>
                        </div>
                    </div>
                @endforeach

                @foreach($customExistingDocs as $cDoc)
                    <div class="doc-preset-card active custom-doc-card" data-name="{{ $cDoc }}" data-rec="0">
                        <input type="checkbox" name="required_documents[]" value="{{ $cDoc }}" class="doc-checkbox d-none" checked>
                        <div class="doc-icon-wrap" style="background:rgba(16,185,129,0.15);color:#10B981;"><i class="bx bx-file"></i></div>
                        <div class="doc-info">
                            <div class="doc-title">
                                <span>{{ $cDoc }}</span>
                                <div class="d-flex align-items-center gap-1">
                                    <button type="button" class="btn btn-sm text-danger p-0 btn-remove-custom-doc" title="Remove"><i class="bx bx-trash"></i></button>
                                    <div class="doc-check"><i class="bx bx-check"></i></div>
                                </div>
                            </div>
                            <small class="text-success d-block mb-1" style="font-size:0.75rem;">Custom Required Document</small>
                            <p class="doc-sub">User must upload this document for the trip</p>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Custom Document Adder --}}
            <div class="p-3 rounded-3 mb-4" style="background: rgba(255,255,255,0.03); border: 1px dashed rgba(6,182,212,0.3);">
                <label class="form-label mb-2"><i class="bx bx-plus-circle text-info me-1"></i>Add Custom Document Type (مستند إضافي خاص)</label>
                <div class="input-group">
                    <input type="text" id="customDocInput" class="form-control" placeholder="e.g. Health Certificate, Temperature Log, Import Permit...">
                    <button type="button" class="btn btn-outline-info px-4 fw-bold" id="btnAddCustomDoc">
                        <i class="bx bx-plus me-1"></i>Add Document
                    </button>
                </div>
            </div>

            {{-- Submit Action --}}
            <div class="text-end pt-3">
                <button type="submit" class="btn btn-save-trip d-inline-flex align-items-center gap-2" id="submitBtn">
                    <i class="bx bx-save fs-5"></i>
                    <span>Update Scheduled Trip</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {

    // Toggle doc preset card
    $(document).on('click', '.doc-preset-card', function(e) {
        if ($(e.target).closest('.btn-remove-custom-doc').length) return;
        $(this).toggleClass('active');
        var isChecked = $(this).hasClass('active');
        $(this).find('.doc-checkbox').prop('checked', isChecked);
    });

    // Select Recommended
    $('#btnSelectRecommendedDocs').on('click', function() {
        $('.doc-preset-card').each(function() {
            var isRec = $(this).data('rec') == '1';
            $(this).toggleClass('active', isRec);
            $(this).find('.doc-checkbox').prop('checked', isRec);
        });
    });

    // Select All
    $('#btnSelectAllDocs').on('click', function() {
        $('.doc-preset-card').addClass('active').find('.doc-checkbox').prop('checked', true);
    });

    // Clear All
    $('#btnClearAllDocs').on('click', function() {
        $('.doc-preset-card').removeClass('active').find('.doc-checkbox').prop('checked', false);
    });

    // Add Custom Doc
    $('#btnAddCustomDoc').on('click', function() {
        var docName = $('#customDocInput').val().trim();
        if (!docName) return;

        var exists = false;
        $('.doc-preset-card').each(function() {
            if ($(this).data('name').toLowerCase() === docName.toLowerCase()) {
                exists = true;
                $(this).addClass('active').find('.doc-checkbox').prop('checked', true);
            }
        });

        if (!exists) {
            var cardHtml = 
                '<div class="doc-preset-card active custom-doc-card" data-name="' + docName + '" data-rec="0">' +
                    '<input type="checkbox" name="required_documents[]" value="' + docName + '" class="doc-checkbox d-none" checked>' +
                    '<div class="doc-icon-wrap" style="background:rgba(16,185,129,0.15);color:#10B981;"><i class="bx bx-file"></i></div>' +
                    '<div class="doc-info">' +
                        '<div class="doc-title">' +
                            '<span>' + docName + '</span>' +
                            '<div class="d-flex align-items-center gap-1">' +
                                '<button type="button" class="btn btn-sm text-danger p-0 btn-remove-custom-doc" title="Remove"><i class="bx bx-trash"></i></button>' +
                                '<div class="doc-check"><i class="bx bx-check"></i></div>' +
                            '</div>' +
                        '</div>' +
                        '<small class="text-success d-block mb-1" style="font-size:0.75rem;">Custom Required Document</small>' +
                        '<p class="doc-sub">User must upload this document for the trip</p>' +
                    '</div>' +
                '</div>';
            $('#docPresetGrid').append(cardHtml);
        }

        $('#customDocInput').val('');
    });

    $(document).on('click', '.btn-remove-custom-doc', function(e) {
        e.stopPropagation();
        $(this).closest('.doc-preset-card').remove();
    });

    // AJAX load truck sub types
    $('#truck_type_id').on('change', function() {
        var truckTypeId = $(this).val();
        if (!truckTypeId) {
            $('#truck_sub_type_id').html('<option value="">-- Select Truck Type First --</option>');
            return;
        }

        $('#truck_sub_type_id').html('<option value="">Loading sub-types...</option>');

        $.ajax({
            url: '{{ route("get.shipment.sub.types.ajax", ":id") }}'.replace(':id', truckTypeId),
            type: 'GET',
            success: function(data) {
                var subTypes = Array.isArray(data) ? data : (data.sub_types || []);
                if (subTypes && subTypes.length > 0) {
                    var html = '<option value="">-- Select Sub-Type (Optional) --</option>';
                    subTypes.forEach(function(sub) {
                        html += '<option value="' + sub.id + '">' + sub.name_en + ' (' + (sub.name_ar || sub.name_en) + ')</option>';
                    });
                    $('#truck_sub_type_id').html(html);
                } else {
                    $('#truck_sub_type_id').html('<option value="">No sub-types available for this truck type</option>');
                }
            },
            error: function() {
                $('#truck_sub_type_id').html('<option value="">Error loading sub-types</option>');
            }
        });
    });

    // Customs clearance toggle handler (Matching App)
    $(document).on('click', '.btn-customs-toggle', function() {
        var val = $(this).data('val');
        $('#customs_clearance_input').val(val);
        $('.btn-customs-toggle').removeClass('active-yes active-no');
        if (val == 1) {
            $(this).addClass('active-yes');
        } else {
            $(this).addClass('active-no');
        }
    });


    // ═══════════════════════════════════════════════════════════
    // DYNAMIC PRICING ADDITIONS (الإضافات المرنة للسعر)
    // ═══════════════════════════════════════════════════════════
    var pricingRowCounter = 0;

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function addPricingRow(name, type, val) {
        name = name || '';
        type = type || 'percentage';
        val = (val !== undefined && val !== null) ? val : '';
        var idx = pricingRowCounter++;

        var isPct = (type === 'percentage');
        var suffixText = isPct ? '%' : 'KWD';

        var trHtml = '<tr class="pricing-row" data-index="' + idx + '">' +
            '<td>' +
                '<input type="text" name="pricing_additions[' + idx + '][name]" class="form-control form-control-sm addition-name" placeholder="مثال: ضريبة 15% أو هامش ربح" value="' + escapeHtml(name) + '" required>' +
            '</td>' +
            '<td>' +
                '<select name="pricing_additions[' + idx + '][type]" class="form-select form-select-sm addition-type">' +
                    '<option value="percentage"' + (isPct ? ' selected' : '') + '>نسبة مئوية (%) من الأساسي</option>' +
                    '<option value="fixed"' + (!isPct ? ' selected' : '') + '>مبلغ مقطوع (KWD)</option>' +
                '</select>' +
            '</td>' +
            '<td>' +
                '<div class="input-group input-group-sm">' +
                    '<input type="number" step="0.01" min="0" name="pricing_additions[' + idx + '][value]" class="form-control addition-val" placeholder="0.00" value="' + val + '" required>' +
                    '<span class="input-group-text addition-suffix bg-dark border-secondary border-opacity-25 text-white-50">' + suffixText + '</span>' +
                '</div>' +
            '</td>' +
            '<td>' +
                '<span class="badge bg-dark border border-secondary border-opacity-25 text-warning addition-calc-preview font-monospace">+0.00 KWD</span>' +
            '</td>' +
            '<td class="text-center">' +
                '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-pricing-row p-1 px-2" title="حذف">' +
                    '<i class="bx bx-trash"></i>' +
                '</button>' +
            '</td>' +
        '</tr>';

        $('#pricingAdditionsBody').append(trHtml);
        updatePricingCalculations();
    }

    function updatePricingCalculations() {
        var basePrice = parseFloat($('#base_price_input').val()) || 0;
        var totalAdditions = 0;
        var rowCount = 0;

        $('#pricingAdditionsBody tr.pricing-row').each(function() {
            rowCount++;
            var $row = $(this);
            var type = $row.find('.addition-type').val();
            var val = parseFloat($row.find('.addition-val').val()) || 0;
            var impact = 0;

            if (type === 'percentage') {
                $row.find('.addition-suffix').text('%');
                impact = basePrice * (val / 100);
                $row.find('.addition-calc-preview').text('+' + impact.toFixed(2) + ' KWD (' + val + '%)');
            } else {
                $row.find('.addition-suffix').text('KWD');
                impact = val;
                $row.find('.addition-calc-preview').text('+' + impact.toFixed(2) + ' KWD (ثابت)');
            }
            totalAdditions += impact;
        });

        if (rowCount > 0) {
            $('#noPricingAdditionsMsg').hide();
        } else {
            $('#noPricingAdditionsMsg').show();
        }

        var finalUnitPrice = basePrice + totalAdditions;
        $('#summaryBasePrice').text(basePrice.toFixed(2) + ' KWD');
        $('#summaryAdditionsTotal').text('+' + totalAdditions.toFixed(2) + ' KWD');
        $('#summaryFinalUnitPrice').text(finalUnitPrice.toFixed(2) + ' KWD');
    }

    // Add manual row
    $('#btnAddPricingRow').on('click', function() {
        addPricingRow('', 'percentage', '');
    });

    // Add preset
    $(document).on('click', '.btn-add-preset', function() {
        var pName = $(this).data('name');
        var pType = $(this).data('type');
        var pVal  = $(this).data('val');
        addPricingRow(pName, pType, pVal);
    });

    // Remove row
    $(document).on('click', '.btn-remove-pricing-row', function() {
        $(this).closest('tr').remove();
        updatePricingCalculations();
    });

    // Recalculate on any input change
    $(document).on('input change', '#base_price_input, .addition-val, .addition-type', function() {
        updatePricingCalculations();
    });

    // Pre-populate initial pricing additions from trip or old input
    var initialPricingAdditions = @json(old('pricing_additions', $trip->pricing_additions ?: []));
    if (initialPricingAdditions && Array.isArray(initialPricingAdditions) && initialPricingAdditions.length > 0) {
        initialPricingAdditions.forEach(function(item) {
            addPricingRow(item.name, item.type, item.value);
        });
    } else {
        updatePricingCalculations();
    }

    // Form submit state
    $('#tripForm').on('submit', function() {
        $('#submitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Updating Trip...');
    });

});
</script>

@endsection
