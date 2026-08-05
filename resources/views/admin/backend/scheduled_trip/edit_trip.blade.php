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
                    <label class="form-label">Trip Date <span class="text-danger">*</span></label>
                    <input type="date" name="trip_date" class="form-control" value="{{ old('trip_date', $trip->trip_date) }}" onclick="this.showPicker()">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Trip Departure Time</label>
                    <input type="time" name="trip_time" class="form-control" value="{{ old('trip_time', $trip->trip_time) }}" onclick="this.showPicker()">
                </div>
            </div>

            <div class="my-4" style="height:1px;background:rgba(255,255,255,0.06);"></div>

            {{-- ════ 4. PRICING & CAPACITY ════ --}}
            <div class="tp-section-title"><i class="bx bx-purchase-tag-alt me-1"></i>Step 4: Pricing & Capacity</div>
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-4">
                    <label class="form-label">Price per Unit / Order (KWD) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00" value="{{ old('price', $trip->price) }}">
                        <span class="input-group-text bg-dark border-secondary border-opacity-25 text-warning fw-bold">KWD</span>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Total Booking Capacity <span class="text-danger">*</span></label>
                    <input type="number" name="total_capacity" id="total_capacity" class="form-control" placeholder="1" value="{{ old('total_capacity', $trip->total_capacity) }}" min="1">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Available Capacity <span class="text-danger">*</span></label>
                    <input type="number" name="available_capacity" id="available_capacity" class="form-control" placeholder="1" value="{{ old('available_capacity', $trip->available_capacity) }}" min="0">
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

    // Form submit state
    $('#tripForm').on('submit', function() {
        $('#submitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Updating Trip...');
    });

});
</script>

@endsection
