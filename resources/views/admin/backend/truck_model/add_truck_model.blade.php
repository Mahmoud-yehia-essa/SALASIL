@extends('admin.master_admin')
@section('admin')

<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold mb-1 header-title">
            <i class="bx bx-plus-circle text-info me-2"></i>Add New Truck Model
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Create a truck model linked to a brand manufacturer (e.g., Actros for Mercedes, FH16 for Volvo).
        </p>
    </div>
    <div>
        <a href="{{ route('all.truck.models') }}" class="btn btn-outline-info rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-list-ul fs-5"></i>
            <span>View All Models</span>
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3 px-4">
                <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="bx bx-car text-info fs-4"></i>
                    <span>Model Details</span>
                </h5>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ route('store.truck.model') }}">
                    @csrf

                    <div class="row g-3">
                        <!-- Select Brand -->
                        <div class="col-12">
                            <label for="truck_brand_id" class="form-label fw-semibold">Select Parent Brand <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <select name="truck_brand_id" id="truck_brand_id" class="form-select @error('truck_brand_id') is-invalid @enderror" style="padding-left: 48px !important;" required>
                                    <option value="">-- Choose Brand --</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ old('truck_brand_id') == $brand->id ? 'selected' : '' }}>
                                            {{ $brand->name_en }} ({{ $brand->name_ar }})
                                        </option>
                                    @endforeach
                                </select>
                                <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                    <i class="bx bx-badge-check fs-5"></i>
                                </span>
                            </div>
                            @error('truck_brand_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Model Name (English) -->
                        <div class="col-12 col-md-6">
                            <label for="name_en" class="form-label fw-semibold">Model Name (English) <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" 
                                       name="name_en" 
                                       id="name_en" 
                                       class="form-control @error('name_en') is-invalid @enderror" 
                                       style="padding-left: 48px !important;"
                                       placeholder="e.g. Actros 1845 / FH 500"
                                       value="{{ old('name_en') }}"
                                       required>
                                <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                    <i class="bx bx-car fs-5"></i>
                                </span>
                            </div>
                            @error('name_en')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Model Name (Arabic) -->
                        <div class="col-12 col-md-6">
                            <label for="name_ar" class="form-label fw-semibold">Model Name (Arabic) <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" 
                                       name="name_ar" 
                                       id="name_ar" 
                                       class="form-control @error('name_ar') is-invalid @enderror" 
                                       style="padding-left: 48px !important;"
                                       placeholder="مثال: أكتروس 1845"
                                       value="{{ old('name_ar') }}"
                                       required>
                                <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                    <i class="bx bx-car fs-5"></i>
                                </span>
                            </div>
                            @error('name_ar')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="col-12 col-md-6">
                            <label for="is_active" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="is_active" id="is_active" class="form-select" required>
                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top border-secondary border-opacity-25">
                        <a href="{{ route('all.truck.models') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3 fw-semibold">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 fw-semibold d-inline-flex align-items-center gap-2">
                            <i class="bx bx-check-circle fs-5"></i>
                            <span>Save Model</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
