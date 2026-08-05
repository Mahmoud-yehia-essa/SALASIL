@extends('admin.master_admin')
@section('admin')

{{-- ========================
     ADD NEW FIXED ROUTE
======================== --}}

<style>
:root {
    --rt-cyan:       #06B6D4;
    --rt-cyan-light: #38BDF8;
    --rt-navy:       #0F172A;
    --rt-border:     rgba(255,255,255,0.08);
}

.rt-card {
    background: linear-gradient(135deg, rgba(30,41,59,0.95) 0%, rgba(15,23,42,0.98) 100%);
    border: 1px solid var(--rt-border);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0,0,0,0.4);
}
.rt-card-header {
    background: linear-gradient(135deg, rgba(6,182,212,0.15) 0%, rgba(56,189,248,0.08) 100%);
    border-bottom: 1px solid rgba(6,182,212,0.2);
    padding: 24px 32px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.rt-card-header .icon-wrap {
    width: 52px; height: 52px;
    background: linear-gradient(135deg, var(--rt-cyan), var(--rt-cyan-light));
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: #fff;
    box-shadow: 0 8px 20px rgba(6,182,212,0.35);
    flex-shrink: 0;
}
.rt-card-body { padding: 32px; }

.rt-card .form-control,
.rt-card .form-select {
    background: rgba(255,255,255,0.05) !important;
    border: 1px solid rgba(255,255,255,0.12) !important;
    color: #F8FAFC !important;
    border-radius: 12px !important;
    padding: 12px 16px !important;
    transition: all 0.25s !important;
    font-size: 0.92rem !important;
}
.rt-card .form-control:focus,
.rt-card .form-select:focus {
    background: rgba(6,182,212,0.08) !important;
    border-color: var(--rt-cyan) !important;
    box-shadow: 0 0 0 3px rgba(6,182,212,0.15) !important;
    color: #F8FAFC !important;
}
.rt-card .form-select option { background: #1E293B; color: #F8FAFC; }
.rt-card .form-label { color: #CBD5E1 !important; font-weight: 700 !important; font-size: 0.88rem !important; margin-bottom: 8px; }

.rt-section-title {
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--rt-cyan-light);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.rt-section-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, rgba(6,182,212,0.4), transparent);
}

.btn-save-route {
    background: linear-gradient(135deg, var(--rt-cyan), var(--rt-cyan-light));
    border: none;
    color: #0F172A;
    font-weight: 800;
    font-size: 1.05rem;
    padding: 14px 44px;
    border-radius: 14px;
    transition: all 0.25s;
    box-shadow: 0 6px 25px rgba(6,182,212,0.35);
}
.btn-save-route:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(6,182,212,0.5);
    color: #0F172A;
}

/* ═════════════════════════════════════════════════════════════
   LIGHT MODE OVERRIDES FOR ADD FIXED ROUTE PAGE
═════════════════════════════════════════════════════════════ */
html.light-theme .rt-card {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 15px 45px rgba(0,0,0,0.06) !important;
}
html.light-theme .rt-card-header {
    background: linear-gradient(135deg, rgba(2,132,199,0.08) 0%, rgba(3,105,161,0.04) 100%) !important;
    border-bottom-color: #E2E8F0 !important;
}
html.light-theme .rt-card-header h4 {
    color: #0F172A !important;
}
html.light-theme .rt-card-header small,
html.light-theme .rt-card-header p {
    color: #64748B !important;
}
html.light-theme .rt-card .form-control,
html.light-theme .rt-card .form-select {
    background: #FFFFFF !important;
    border-color: #CBD5E1 !important;
    color: #0F172A !important;
}
html.light-theme .rt-card .form-control:focus,
html.light-theme .rt-card .form-select:focus {
    background: #FFFFFF !important;
    border-color: #0284C7 !important;
    box-shadow: 0 0 0 3px rgba(2,132,199,0.15) !important;
    color: #0F172A !important;
}
html.light-theme .rt-card .form-select option {
    background: #FFFFFF !important;
    color: #0F172A !important;
}
html.light-theme .rt-card .form-label {
    color: #334155 !important;
}
html.light-theme .rt-section-title {
    color: #0284C7 !important;
}
html.light-theme .rt-section-title::after {
    background: linear-gradient(90deg, rgba(2,132,199,0.3), transparent) !important;
}
</style>

{{-- Page Header --}}
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold mb-1 header-title">
            <i class="bx bx-git-repo-forked text-info me-2"></i>Add Fixed Route
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Define origin and destination locations, quote type (Local/International), and estimated distance.
        </p>
    </div>
    <div>
        <a href="{{ route('all.routes') }}" class="btn btn-outline-info rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-list-ul fs-5"></i>
            <span>All Routes</span>
        </a>
    </div>
</div>

{{-- Main Form Card --}}
<div class="rt-card">
    <div class="rt-card-header">
        <div class="icon-wrap"><i class="bx bx-map-alt"></i></div>
        <div>
            <h4 class="fw-bold text-white mb-0">Fixed Route Specifications</h4>
            <small class="text-muted">Fill in origin, destination, distance, and status</small>
        </div>
    </div>
    <div class="rt-card-body">
        <form method="POST" action="{{ route('store.route') }}" id="routeForm" autocomplete="off">
            @csrf

            {{-- ════ 1. ROUTE CLASSIFICATION ════ --}}
            <div class="rt-section-title"><i class="bx bx-grid-alt me-1"></i>Route Type</div>
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6">
                    <label class="form-label">Quote Type <span class="text-danger">*</span></label>
                    <select name="quote_type" id="quote_type" class="form-select">
                        <option value="local" {{ old('quote_type') === 'local' ? 'selected' : '' }}>Local (Domestic)</option>
                        <option value="international" {{ old('quote_type') === 'international' ? 'selected' : '' }}>International (Cross-Border)</option>
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select">
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="my-4" style="height:1px;background:rgba(255,255,255,0.06);"></div>

            {{-- ════ 2. ORIGIN LOCATION ════ --}}
            <div class="rt-section-title"><i class="bx bx-map-pin me-1"></i>Origin Location (Start Point)</div>
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6">
                    <label class="form-label">Origin Country <span class="text-danger">*</span></label>
                    <select name="origin_country_id" id="origin_country_id" class="form-select">
                        <option value="">-- Select Origin Country --</option>
                        @foreach($countries as $c)
                            <option value="{{ $c->id }}">{{ $c->name_en }} ({{ $c->name_ar }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Origin City <span class="text-danger">*</span></label>
                    <select name="origin_city_id" id="origin_city_id" class="form-select">
                        <option value="">-- Select Country First --</option>
                    </select>
                </div>
            </div>

            <div class="my-4" style="height:1px;background:rgba(255,255,255,0.06);"></div>

            {{-- ════ 3. DESTINATION LOCATION ════ --}}
            <div class="rt-section-title"><i class="bx bx-navigation me-1"></i>Destination Location (End Point)</div>
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6">
                    <label class="form-label">Destination Country <span class="text-danger">*</span></label>
                    <select name="destination_country_id" id="destination_country_id" class="form-select">
                        <option value="">-- Select Destination Country --</option>
                        @foreach($countries as $c)
                            <option value="{{ $c->id }}">{{ $c->name_en }} ({{ $c->name_ar }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Destination City <span class="text-danger">*</span></label>
                    <select name="destination_city_id" id="destination_city_id" class="form-select">
                        <option value="">-- Select Country First --</option>
                    </select>
                </div>
            </div>

            <div class="my-4" style="height:1px;background:rgba(255,255,255,0.06);"></div>

            {{-- ════ 4. DISTANCE ════ --}}
            <div class="rt-section-title"><i class="bx bx-tachometer me-1"></i>Distance Estimation</div>
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6">
                    <label class="form-label">Estimated Distance (in Kilometers)</label>
                    <div class="input-group">
                        <input type="number" step="0.1" name="estimated_distance" class="form-control" placeholder="e.g. 450.5" value="{{ old('estimated_distance') }}">
                        <span class="input-group-text bg-dark border-secondary border-opacity-25 text-info fw-bold">KM</span>
                    </div>
                </div>
            </div>

            {{-- Submit Action --}}
            <div class="text-end pt-3">
                <button type="submit" class="btn btn-save-route d-inline-flex align-items-center gap-2" id="submitBtn">
                    <i class="bx bx-save fs-5"></i>
                    <span>Save Fixed Route</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {

    // Load Origin Cities when Origin Country changes
    $('#origin_country_id').on('change', function() {
        var countryId = $(this).val();
        loadCities(countryId, '#origin_city_id');
    });

    // Load Destination Cities when Destination Country changes
    $('#destination_country_id').on('change', function() {
        var countryId = $(this).val();
        loadCities(countryId, '#destination_city_id');
    });

    function loadCities(countryId, targetSelect) {
        if (!countryId) {
            $(targetSelect).html('<option value="">-- Select Country First --</option>');
            return;
        }

        $(targetSelect).html('<option value="">Loading cities...</option>');

        $.ajax({
            url: '{{ route("get.shipment.cities.ajax", ":id") }}'.replace(':id', countryId),
            type: 'GET',
            success: function(data) {
                var cities = Array.isArray(data) ? data : (data.cities || []);
                if (cities && cities.length > 0) {
                    var html = '<option value="">-- Select City --</option>';
                    cities.forEach(function(city) {
                        html += '<option value="' + city.id + '">' + city.name_en + ' (' + (city.name_ar || city.name_en) + ')</option>';
                    });
                    $(targetSelect).html(html);
                } else {
                    $(targetSelect).html('<option value="">No cities available for this country</option>');
                }
            },
            error: function() {
                $(targetSelect).html('<option value="">Error loading cities</option>');
            }
        });
    }

    // Form submit state
    $('#routeForm').on('submit', function() {
        $('#submitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Saving Route...');
    });

});
</script>

@endsection
