@extends('admin.master_admin')
@section('admin')

<!-- Page Header -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold mb-1 header-title">
            <i class="bx bx-plus-circle text-info me-2"></i>Add New Shipment Nature
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Create a new cargo nature entry (e.g. Hazardous, Fragile, Refrigerated) with English and Arabic names.
        </p>
    </div>
    <div>
        <a href="{{ route('all.shipment.natures') }}" class="btn btn-outline-info rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-list-ul fs-5"></i>
            <span>View All Shipment Natures</span>
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-xl-9">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3 px-4">
                <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="bx bx-shield-quarter text-info fs-4"></i>
                    <span>Shipment Nature Details</span>
                </h5>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ route('store.shipment.nature') }}" id="shipmentNatureForm">
                    @csrf

                    <div class="row g-3 mb-4">
                        <!-- Nature Name English (Required) -->
                        <div class="col-12 col-md-6">
                            <label for="name_en" class="form-label fw-semibold">Shipment Nature Name (English) <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" 
                                       name="name_en" 
                                       id="name_en" 
                                       class="form-control @error('name_en') is-invalid @enderror" 
                                       placeholder="e.g. Hazardous Materials / Fragile"
                                       value="{{ old('name_en') }}"
                                       required>
                                <span class="position-absolute text-info" style="right: 14px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                                    <i class="bx bx-error-circle fs-5"></i>
                                </span>
                            </div>
                            @error('name_en')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Nature Name Arabic (Required) -->
                        <div class="col-12 col-md-6">
                            <label for="name_ar" class="form-label fw-semibold">Shipment Nature Name (Arabic) <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" 
                                       name="name_ar" 
                                       id="name_ar" 
                                       class="form-control @error('name_ar') is-invalid @enderror" 
                                       style="direction: rtl;"
                                       placeholder="مثال: مواد خطرة / قابل للكسر"
                                       value="{{ old('name_ar') }}"
                                       required>
                                <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                                    <i class="bx bx-font fs-5"></i>
                                </span>
                            </div>
                            @error('name_ar')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Active Status Switch -->
                        <div class="col-12 col-md-6">
                            <label for="is_active" class="form-label fw-semibold">Status</label>
                            <select name="is_active" id="is_active" class="form-select">
                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active (مفعل)</option>
                                <option value="0" {{ old('is_active', '1') == '0' ? 'selected' : '' }}>Inactive (غير مفعل)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top border-secondary border-opacity-25">
                        <a href="{{ route('all.shipment.natures') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3 fw-semibold">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 fw-semibold d-inline-flex align-items-center gap-2">
                            <i class="bx bx-check-circle fs-5"></i>
                            <span>Save Shipment Nature</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

@endsection
