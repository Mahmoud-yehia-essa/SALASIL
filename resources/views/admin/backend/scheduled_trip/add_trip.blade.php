@extends('admin.master_admin')
@section('admin')

{{-- ========================
     ADD SCHEDULED TRIP
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
            <i class="bx bx-calendar-plus text-info me-2"></i>Add Scheduled Trip
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Schedule a new fixed trip with route, truck type, capacity, price, date and time.
        </p>
    </div>
    <div>
        <a href="{{ route('all.scheduled.trips') }}" class="btn btn-outline-info rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-list-ul fs-5"></i>
            <span>All Scheduled Trips</span>
        </a>
    </div>
</div>

{{-- Main Form Card --}}
<div class="tp-card">
    <div class="tp-card-header">
        <div class="icon-wrap"><i class="bx bx-calendar-event"></i></div>
        <div>
            <h4 class="fw-bold text-white mb-0">Scheduled Trip Specifications</h4>
            <small class="text-muted">Fill in route, capacity, truck type, date and price</small>
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
        <form method="POST" action="{{ route('store.scheduled.trip') }}" id="tripForm" autocomplete="off">
            @csrf

            {{-- ════ 1. ROUTE SELECTION ════ --}}
            <div class="tp-section-title"><i class="bx bx-git-repo-forked me-1"></i>Step 1: Select Fixed Route</div>
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-8">
                    <label class="form-label">Route <span class="text-danger">*</span></label>
                    <select name="route_id" id="route_id" class="form-select @error('route_id') is-invalid @enderror">
                        <option value="">-- Select a Fixed Route --</option>
                        @foreach($routes as $rt)
                            <option value="{{ $rt->id }}" {{ old('route_id') == $rt->id ? 'selected' : '' }}>
                                Route #{{ $rt->id }}: {{ $rt->originCity->name_en ?? 'Origin' }} ({{ $rt->originCountry->name_en ?? '' }}) ➔ {{ $rt->destinationCity->name_en ?? 'Destination' }} ({{ $rt->destinationCountry->name_en ?? '' }}) [{{ ucfirst($rt->quote_type) }}]
                            </option>
                        @endforeach
                    </select>
                    @error('route_id')
                        <div class="invalid-feedback text-danger d-block mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Trip Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published (Open)</option>
                        <option value="boarding" {{ old('status') === 'boarding' ? 'selected' : '' }}>Boarding</option>
                        <option value="in_transit" {{ old('status') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                        <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="canceled" {{ old('status') === 'canceled' ? 'selected' : '' }}>Canceled</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback text-danger d-block mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="my-4" style="height:1px;background:rgba(255,255,255,0.06);"></div>

            {{-- ════ 2. TRUCK & CARGO SPECS ════ --}}
            <div class="tp-section-title"><i class="bx bx-truck me-1"></i>Step 2: Truck Specifications & Quantities</div>
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6">
                    <label class="form-label">Truck Type <span class="text-danger">*</span></label>
                    <select name="truck_type_id" id="truck_type_id" class="form-select @error('truck_type_id') is-invalid @enderror">
                        <option value="">-- Select Truck Type --</option>
                        @foreach($truckTypes as $type)
                            <option value="{{ $type->id }}" {{ old('truck_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name_en }} ({{ $type->name_ar }})</option>
                        @endforeach
                    </select>
                    @error('truck_type_id')
                        <div class="invalid-feedback text-danger d-block mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Truck Sub-Type (Optional)</label>
                    <select name="truck_sub_type_id" id="truck_sub_type_id" class="form-select">
                        <option value="">-- Select Truck Type First --</option>
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Number of Trucks <span class="text-danger">*</span></label>
                    <input type="number" name="number_of_trucks" id="number_of_trucks" class="form-control @error('number_of_trucks') is-invalid @enderror" placeholder="1" value="{{ old('number_of_trucks', 1) }}" min="1">
                    @error('number_of_trucks')
                        <div class="invalid-feedback text-danger d-block mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Total Approx. Weight (Tons)</label>
                    <div class="input-group">
                        <input type="number" step="0.1" name="total_weight_ton" class="form-control @error('total_weight_ton') is-invalid @enderror" placeholder="e.g. 24.5" value="{{ old('total_weight_ton') }}">
                        <span class="input-group-text bg-dark border-secondary border-opacity-25 text-info fw-bold">Tons</span>
                    </div>
                </div>

                {{-- Customs Clearance Toggle (As in App Search) --}}
                <div class="col-12 col-md-6">
                    <label class="form-label d-flex justify-content-between align-items-center">
                        <span><i class="bx bx-shield-quarter text-info me-1"></i>Customs Clearance <span class="text-info">(التخليص الجمركي)</span></span>
                        <small class="text-muted">Matches App Search</small>
                    </label>
                    <input type="hidden" name="customs_clearance" id="customs_clearance_input" value="{{ old('customs_clearance', 1) }}">
                    <div class="customs-toggle-group">
                        <button type="button" class="btn-customs-toggle {{ old('customs_clearance', 1) == 1 ? 'active-yes' : '' }}" data-val="1">
                            <i class="bx bx-check-circle"></i>
                            <div>
                                <div class="fw-bold">Yes</div>
                                <small style="font-size:0.74rem;opacity:0.85;">Customs Included (يشمل التخليص)</small>
                            </div>
                        </button>
                        <button type="button" class="btn-customs-toggle {{ old('customs_clearance', 1) == 0 ? 'active-no' : '' }}" data-val="0">
                            <i class="bx bx-x-circle"></i>
                            <div>
                                <div class="fw-bold">No</div>
                                <small style="font-size:0.74rem;opacity:0.85;">No Clearance (بدون تخليص)</small>
                            </div>
                        </button>
                    </div>
                </div>

                {{-- Shipment Type & Shipment Nature --}}
                <div class="col-12 col-md-6">
                    <label class="form-label d-flex justify-content-between align-items-center">
                        <span><i class="bx bx-package text-info me-1"></i>Shipment Type <span class="text-info">(نوع الشحنة)</span></span>
                        <small class="text-muted">Optional</small>
                    </label>
                    <select name="shipment_type_id" id="shipment_type_id" class="form-select @error('shipment_type_id') is-invalid @enderror">
                        <option value="">-- All / Any Shipment Type (أي نوع) --</option>
                        @foreach($shipmentTypes as $type)
                            <option value="{{ $type->id }}" {{ old('shipment_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name_en }} ({{ $type->name_ar }})
                            </option>
                        @endforeach
                    </select>
                    @error('shipment_type_id')
                        <div class="invalid-feedback text-danger d-block mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label d-flex justify-content-between align-items-center">
                        <span><i class="bx bx-check-shield text-info me-1"></i>Shipment Nature <span class="text-info">(طبيعة الشحنة)</span></span>
                        <small class="text-muted">Optional</small>
                    </label>
                    <select name="shipment_nature_id" id="shipment_nature_id" class="form-select @error('shipment_nature_id') is-invalid @enderror">
                        <option value="">-- All / Any Shipment Nature (أي طبيعة) --</option>
                        @foreach($shipmentNatures as $nature)
                            <option value="{{ $nature->id }}" {{ old('shipment_nature_id') == $nature->id ? 'selected' : '' }}>
                                {{ $nature->name_en }} ({{ $nature->name_ar }})
                            </option>
                        @endforeach
                    </select>
                    @error('shipment_nature_id')
                        <div class="invalid-feedback text-danger d-block mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Cargo & Carrier Notes --}}
                <div class="col-12 col-md-12">
                    <label class="form-label">Cargo & Carrier Notes (ملاحظات الشحنة والرحلة)</label>
                    <textarea name="notes" class="form-control" rows="2" style="height:58px;" placeholder="Any extra details for the carrier (optional)...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="my-4" style="height:1px;background:rgba(255,255,255,0.06);"></div>

            {{-- ════ 3. SCHEDULE TIMING & DRIVER ════ --}}
            <div class="tp-section-title"><i class="bx bx-time me-1"></i>Step 3: Driver & Schedule Timing</div>
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-4">
                    <label class="form-label">Trip Date <span class="badge bg-secondary bg-opacity-25 text-warning ms-1" style="font-size:0.72rem;">غير فعال / يحدد لاحقاً في الطلب</span></label>
                    <input type="date" name="trip_date" class="form-control" value="" disabled style="background-color: rgba(255,255,255,0.05); cursor: not-allowed; opacity: 0.6;">
                    <div class="form-text text-muted" style="font-size:0.75rem;"><i class="bx bx-lock-alt me-1 text-warning"></i>تم تعطيل الحقل هنا، ويتم تحديد التاريخ لاحقاً عند إنشاء الطلب (Add Scheduled Trip Order).</div>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Trip Departure Time <span class="badge bg-secondary bg-opacity-25 text-warning ms-1" style="font-size:0.72rem;">غير فعال / يحدد لاحقاً</span></label>
                    <input type="time" name="trip_time" class="form-control" value="" disabled style="background-color: rgba(255,255,255,0.05); cursor: not-allowed; opacity: 0.6;">
                    <div class="form-text text-muted" style="font-size:0.75rem;"><i class="bx bx-lock-alt me-1 text-warning"></i>تم تعطيل الحقل هنا، ويتم تحديد الوقت لاحقاً عند إنشاء الطلب.</div>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Assigned Driver (Optional)</label>
                    <select name="driver_id" class="form-select @error('driver_id') is-invalid @enderror">
                        <option value="">-- Unassigned (Assign Later) --</option>
                        @foreach($drivers as $d)
                            <option value="{{ $d->id }}" {{ old('driver_id') == $d->id ? 'selected' : '' }}>
                                {{ $d->fname }} {{ $d->lname }} ({{ $d->phone }})
                            </option>
                        @endforeach
                    </select>
                    @error('driver_id')
                        <div class="invalid-feedback text-danger d-block mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="my-4" style="height:1px;background:rgba(255,255,255,0.06);"></div>

            {{-- ════ 4. PRICING ════ --}}
            <div class="tp-section-title"><i class="bx bx-purchase-tag-alt me-1"></i>Step 4: Pricing (التسعير)</div>
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6">
                    <label class="form-label">Price per Unit / Order (KWD) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror" placeholder="0.00" value="{{ old('price') }}">
                        <span class="input-group-text bg-dark border-secondary border-opacity-25 text-warning fw-bold">KWD</span>
                    </div>
                    @error('price')
                        <div class="invalid-feedback text-danger d-block mt-1">{{ $message }}</div>
                    @enderror
                </div>
                {{-- Open Booking Capacity without limit --}}
                <input type="hidden" name="total_capacity" value="9999">
                <input type="hidden" name="available_capacity" value="9999">
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
                    $oldDocs = old('required_documents', ['Commercial Invoice', 'Packing List', 'Customs Clearance Certificate']);
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
                    <i class="bx bx-calendar-check fs-5"></i>
                    <span>Publish Scheduled Trip</span>
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

    // Form submit state
    $('#tripForm').on('submit', function() {
        $('#submitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Publishing Trip...');
    });

});
</script>

@endsection
