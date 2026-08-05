@extends('admin.master_admin')
@section('admin')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<!-- Page Header -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold mb-1 header-title">
            <i class="bx bx-plus-circle text-info me-2"></i>Assign Truck to Driver
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Link a fleet driver to a main truck type, optional sub-type, plate number, and set verification status.
        </p>
    </div>
    <div>
        <a href="{{ route('all.driver.trucks') }}" class="btn btn-outline-info rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-list-ul fs-5"></i>
            <span>View All Assigned Trucks</span>
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3 px-4">
                <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="bx bx-link-alt text-info fs-4"></i>
                    <span>Assignment Details</span>
                </h5>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ route('store.driver.truck') }}" id="driverTruckForm">
                    @csrf

                    <!-- Section 1: Driver Selection -->
                    <div class="mb-4">
                        <h6 class="text-info fw-bold text-uppercase fs-7 mb-3" style="letter-spacing: 0.5px;">
                            <i class="bx bx-user-check me-1"></i> Driver Selection
                        </h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="driver_id" class="form-label fw-semibold">Select Driver <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <select name="driver_id" id="driver_id" class="form-select @error('driver_id') is-invalid @enderror" style="padding-left: 48px !important;" required>
                                        <option value="">-- Choose Driver --</option>
                                        @foreach($drivers as $driver)
                                            <option value="{{ $driver->id }}" {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
                                                {{ $driver->fname }} {{ $driver->lname }} ({{ $driver->phone ?? $driver->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-user fs-5"></i>
                                    </span>
                                </div>
                                @error('driver_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="border-secondary opacity-25 my-4">

                    <!-- Section 2: Truck Categories & Sub-Types (Dynamic AJAX) -->
                    <div class="mb-4">
                        <h6 class="text-info fw-bold text-uppercase fs-7 mb-3" style="letter-spacing: 0.5px;">
                            <i class="bx bx-truck me-1"></i> Truck Type Specification
                        </h6>
                        <div class="row g-3">
                            <!-- Main Truck Type (Required) -->
                            <div class="col-12 col-md-6">
                                <label for="truck_type_id" class="form-label fw-semibold">Main Truck Type <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <select name="truck_type_id" id="truck_type_id" class="form-select @error('truck_type_id') is-invalid @enderror" style="padding-left: 48px !important;" required>
                                        <option value="">-- Choose Main Truck Type --</option>
                                        @foreach($truckTypes as $type)
                                            <option value="{{ $type->id }}" {{ old('truck_type_id') == $type->id ? 'selected' : '' }}>
                                                {{ $type->name_en }} {{ $type->max_weight ? '('.$type->max_weight.' Tons)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-truck fs-5"></i>
                                    </span>
                                </div>
                                @error('truck_type_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Truck Sub-Type (Optional - Dynamic AJAX) -->
                            <div class="col-12 col-md-6">
                                <label for="truck_sub_type_id" class="form-label fw-semibold">
                                    Truck Sub-Type <small class="text-muted fw-normal">(Optional / اختياري)</small>
                                </label>
                                <div class="position-relative">
                                    <select name="truck_sub_type_id" id="truck_sub_type_id" class="form-select @error('truck_sub_type_id') is-invalid @enderror" style="padding-left: 48px !important;">
                                        <option value="">-- Select Main Truck Type First --</option>
                                    </select>
                                    <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-subdirectory-right fs-5"></i>
                                    </span>
                                </div>
                                <small class="text-slate-400 d-block mt-1">
                                    Automatically populated based on the selected Truck Type.
                                </small>
                                @error('truck_sub_type_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="border-secondary opacity-25 my-4">

                    <!-- Section 3: Brand, Model & Mechanical Specifications (Cascading Dropdown) -->
                    <div class="mb-4">
                        <h6 class="text-info fw-bold text-uppercase fs-7 mb-3" style="letter-spacing: 0.5px;">
                            <i class="bx bx-purchase-tag-alt me-1"></i> Brand, Model & Technical Specs
                        </h6>
                        <div class="row g-3">
                            <!-- Truck Brand Dropdown -->
                            <div class="col-12 col-md-6">
                                <label for="truck_brand_id" class="form-label fw-semibold">
                                    Truck Brand (الماركة) <span class="text-danger">*</span>
                                </label>
                                <div class="position-relative">
                                    <select name="truck_brand_id" id="truck_brand_id" class="form-select @error('truck_brand_id') is-invalid @enderror" style="padding-left: 48px !important;" required>
                                        <option value="">-- Select Truck Brand --</option>
                                        @if(isset($truckBrands))
                                            @foreach($truckBrands as $brand)
                                                <option value="{{ $brand->id }}" {{ old('truck_brand_id') == $brand->id ? 'selected' : '' }}>
                                                    {{ $brand->name_en }} {{ !empty($brand->name_ar) ? '('.$brand->name_ar.')' : '' }}
                                                </option>
                                            @endforeach
                                        @elseif(isset($brands))
                                            @foreach($brands as $brand)
                                                <option value="{{ $brand->id }}" {{ old('truck_brand_id') == $brand->id ? 'selected' : '' }}>
                                                    {{ $brand->name_en }} {{ !empty($brand->name_ar) ? '('.$brand->name_ar.')' : '' }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-badge-check fs-5"></i>
                                    </span>
                                </div>
                                <small class="text-slate-400 d-block mt-1">Select vehicle manufacturer brand (e.g. Mercedes-Benz, Volvo, MAN, Scania).</small>
                                @error('truck_brand_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Truck Model Dropdown (Cascading Dependency) -->
                            <div class="col-12 col-md-6">
                                <label for="truck_model_id" class="form-label fw-semibold d-flex justify-content-between align-items-center">
                                    <span>Truck Model (الموديل) <span class="text-danger">*</span></span>
                                    <span id="model_cascading_badge" class="badge bg-secondary bg-opacity-25 text-slate-300 font-monospace" style="font-size: 0.7rem;">
                                        <i class="bx bx-link-external me-1"></i>Depends on Brand
                                    </span>
                                </label>
                                <div class="position-relative">
                                    <select name="truck_model_id" id="truck_model_id" class="form-select @error('truck_model_id') is-invalid @enderror" style="padding-left: 48px !important;" disabled required>
                                        <option value="">-- Select Brand First --</option>
                                    </select>
                                    <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-car fs-5"></i>
                                    </span>
                                </div>
                                <small class="text-slate-400 d-block mt-1">
                                    Dynamic cascading dropdown: Models populate automatically after selecting a Brand.
                                </small>
                                @error('truck_model_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Manufacturing Year Input -->
                            <div class="col-12 col-md-6">
                                <label for="manufacturing_year" class="form-label fw-semibold">
                                    Manufacturing Year (سنة الصنع) <span class="text-danger">*</span>
                                </label>
                                <div class="position-relative">
                                    <input type="number" 
                                           name="manufacturing_year" 
                                           id="manufacturing_year" 
                                           class="form-control @error('manufacturing_year') is-invalid @enderror" 
                                           style="padding-left: 48px !important;"
                                           min="1980" 
                                           max="{{ date('Y') + 1 }}" 
                                           step="1"
                                           placeholder="e.g. 2023"
                                           value="{{ old('manufacturing_year') }}" 
                                           required>
                                    <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-calendar fs-5"></i>
                                    </span>
                                </div>
                                <small class="text-slate-400 d-block mt-1">Vehicle manufacture year (1980 - {{ date('Y') + 1 }}).</small>
                                @error('manufacturing_year')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Axles Count Input -->
                            <div class="col-12 col-md-6">
                                <label for="axles_count" class="form-label fw-semibold">
                                    Axle Count / Axle # (عدد المحاور) <span class="text-danger">*</span>
                                </label>
                                <div class="position-relative">
                                    <input type="number" 
                                           name="axles_count" 
                                           id="axles_count" 
                                           class="form-control @error('axles_count') is-invalid @enderror" 
                                           style="padding-left: 48px !important;"
                                           min="2" 
                                           max="12" 
                                           step="1"
                                           placeholder="e.g. 2, 3, 4, 6"
                                           value="{{ old('axles_count') }}" 
                                           required>
                                    <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-layer fs-5"></i>
                                    </span>
                                </div>
                                <small class="text-slate-400 d-block mt-1">Number of wheel axles (e.g. 2, 3, 4, 6+ for heavy haulage).</small>
                                @error('axles_count')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="border-secondary opacity-25 my-4">

                    <!-- Section 4: Fleet Management & Verification -->
                    <div class="mb-4">
                        <h6 class="text-info fw-bold text-uppercase fs-7 mb-3" style="letter-spacing: 0.5px;">
                            <i class="bx bx-cog me-1"></i> Vehicle Attributes & Status
                        </h6>
                        <div class="row g-3">
                            <!-- Plate Number -->
                            <div class="col-12 col-md-6">
                                <label for="plate_number" class="form-label fw-semibold">Plate Number / License Plate</label>
                                <div class="position-relative">
                                    <input type="text" 
                                           name="plate_number" 
                                           id="plate_number" 
                                           class="form-control @error('plate_number') is-invalid @enderror" 
                                           style="padding-left: 48px !important; font-family: monospace;"
                                           placeholder="e.g. ABC-1234 / 1234 أ ب ج"
                                           value="{{ old('plate_number') }}">
                                    <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-id-card fs-5"></i>
                                    </span>
                                </div>
                                <small class="text-slate-400 d-block mt-1">Helps distinguish if the driver owns multiple trucks.</small>
                                @error('plate_number')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Verification Status -->
                            <div class="col-12 col-md-6">
                                <label for="is_verified" class="form-label fw-semibold">Document Verification Status</label>
                                <select name="is_verified" id="is_verified" class="form-select">
                                    <option value="1" {{ old('is_verified', '0') == '1' ? 'selected' : '' }}>Verified (Approved by Admin)</option>
                                    <option value="0" {{ old('is_verified', '0') == '0' ? 'selected' : '' }}>Unverified (Pending Documentation)</option>
                                </select>
                            </div>

                            <!-- Default Active Truck Checkbox -->
                            <div class="col-12 mt-3">
                                <div class="form-check form-switch card p-3 border-info border-opacity-25 bg-info bg-opacity-10 rounded-3">
                                    <input class="form-check-input ms-0 me-3" type="checkbox" role="switch" id="is_default" name="is_default" value="1" {{ old('is_default', '1') == '1' ? 'checked' : '' }} style="width: 42px; height: 22px; cursor: pointer;">
                                    <label class="form-check-label fw-semibold text-white cursor-pointer" for="is_default">
                                        Set as Active Default Truck for Order Reception
                                    </label>
                                    <small class="text-slate-300 d-block mt-1">
                                        If checked, this truck will be set as the driver's current active truck for receiving order dispatches.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top border-secondary border-opacity-25">
                        <a href="{{ route('all.driver.trucks') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3 fw-semibold">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 fw-semibold d-inline-flex align-items-center gap-2">
                            <i class="bx bx-check-circle fs-5"></i>
                            <span>Save Truck Assignment</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        // Dynamic AJAX loading of Truck Sub-Types based on Main Truck Type Selection
        $('#truck_type_id').on('change', function(){
            var truckTypeId = $(this).val();
            var $subTypeSelect = $('#truck_sub_type_id');

            if (truckTypeId) {
                $subTypeSelect.html('<option value="">Loading sub-types...</option>');

                $.ajax({
                    url: "{{ url('/admin/driver-truck/get-sub-types') }}/" + truckTypeId,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $subTypeSelect.empty();
                        $subTypeSelect.append('<option value="">-- None / Optional Sub-Type --</option>');

                        if (data && data.length > 0) {
                            $.each(data, function(key, value){
                                var payload = value.max_payload ? ' (' + value.max_payload + ' Tons)' : '';
                                $subTypeSelect.append('<option value="' + value.id + '">' + value.name_en + payload + '</option>');
                            });
                        } else {
                            $subTypeSelect.html('<option value="">-- No Sub-Types Available (Optional) --</option>');
                        }
                    },
                    error: function() {
                        $subTypeSelect.html('<option value="">-- None / Optional Sub-Type --</option>');
                    }
                });
            } else {
                $subTypeSelect.html('<option value="">-- Select Main Truck Type First --</option>');
            }
        });

        // Trigger change if truck_type_id has an initial old value on validation error
        if ($('#truck_type_id').val()) {
            $('#truck_type_id').trigger('change');
        }

        // Dynamic AJAX loading of Truck Models based on Truck Brand Selection (Cascading Dropdown)
        $('#truck_brand_id').on('change', function(){
            var brandId = $(this).val();
            var $modelSelect = $('#truck_model_id');
            var $modelBadge = $('#model_cascading_badge');

            if (brandId) {
                $modelSelect.prop('disabled', false);
                $modelSelect.html('<option value="">Loading models...</option>');
                $modelBadge.removeClass('bg-secondary bg-opacity-25 text-slate-300')
                           .addClass('bg-info bg-opacity-25 text-info')
                           .html('<i class="bx bx-check-circle me-1"></i>Brand Selected');

                $.ajax({
                    url: "{{ url('/admin/driver-truck/get-models-by-brand') }}/" + brandId,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $modelSelect.empty();
                        $modelSelect.append('<option value="">-- Choose Truck Model --</option>');

                        if (data && data.length > 0) {
                            $.each(data, function(key, value){
                                var nameAr = value.name_ar ? ' (' + value.name_ar + ')' : '';
                                $modelSelect.append('<option value="' + value.id + '">' + value.name_en + nameAr + '</option>');
                            });
                        } else {
                            $modelSelect.html('<option value="">-- No Models Found for Selected Brand --</option>');
                        }
                    },
                    error: function() {
                        $modelSelect.html('<option value="">-- Choose Truck Model --</option>');
                    }
                });
            } else {
                $modelSelect.empty();
                $modelSelect.append('<option value="">-- Select Brand First --</option>');
                $modelSelect.prop('disabled', true);
                $modelBadge.removeClass('bg-info bg-opacity-25 text-info')
                           .addClass('bg-secondary bg-opacity-25 text-slate-300')
                           .html('<i class="bx bx-link-external me-1"></i>Depends on Brand');
            }
        });

        // Trigger change if truck_brand_id has an initial old value on validation error
        if ($('#truck_brand_id').val()) {
            $('#truck_brand_id').trigger('change');
        }
    });
</script>

@endsection
