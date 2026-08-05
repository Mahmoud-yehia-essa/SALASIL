@extends('admin.master_admin')
@section('admin')

<!-- Page Header -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold mb-1 header-title">
            <i class="bx bx-edit text-info me-2"></i>Edit Truck Sub-Type
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Update sub-type details for: <span class="text-info fw-semibold">{{ $subType->name_en }}</span>
        </p>
    </div>
    <div>
        <a href="{{ route('all.truck.sub.types') }}" class="btn btn-outline-info rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-list-ul fs-5"></i>
            <span>View All Sub-Types</span>
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3 px-4">
                <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="bx bx-category-alt text-info fs-4"></i>
                    <span>Edit Truck Sub-Type Details</span>
                </h5>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ route('update.truck.sub.type') }}" id="subTypeEditForm">
                    @csrf
                    <input type="hidden" name="id" value="{{ $subType->id }}">

                    <!-- Main Information Section -->
                    <div class="mb-4">
                        <h6 class="text-info fw-bold text-uppercase fs-7 mb-3" style="letter-spacing: 0.5px;">
                            <i class="bx bx-info-circle me-1"></i> General Details
                        </h6>
                        <div class="row g-3">
                            <!-- Main Truck Type Dropdown -->
                            <div class="col-12 col-md-6">
                                <label for="truck_type_id" class="form-label fw-semibold">Main Truck Category <span class="text-danger">*</span></label>
                                <select name="truck_type_id" id="truck_type_id" class="form-select @error('truck_type_id') is-invalid @enderror" required>
                                    <option value="">-- Select Main Category --</option>
                                    @foreach($truckTypes as $type)
                                        <option value="{{ $type->id }}" {{ old('truck_type_id', $subType->truck_type_id) == $type->id ? 'selected' : '' }}>
                                            {{ $type->name_en }} {{ !empty($type->name_ar) && $type->name_ar !== $type->name_en ? '('.$type->name_ar.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('truck_type_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Sub-Type Name (English) -->
                            <div class="col-12 col-md-6">
                                <label for="name_en" class="form-label fw-semibold">Sub-Type Name (English) <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="text" 
                                           name="name_en" 
                                           id="name_en" 
                                           class="form-control @error('name_en') is-invalid @enderror" 
                                           style="padding-left: 48px !important;"
                                           placeholder="e.g. Flatbed, Reefer, Box Truck"
                                           value="{{ old('name_en', $subType->name_en) }}"
                                           required>
                                    <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-tag fs-5"></i>
                                    </span>
                                </div>
                                @error('name_en')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Max Payload in Tons -->
                            <div class="col-12 col-md-6">
                                <label for="max_payload" class="form-label fw-semibold">Max Payload (Tons)</label>
                                <div class="position-relative">
                                    <input type="number" 
                                           step="0.01"
                                           name="max_payload" 
                                           id="max_payload" 
                                           class="form-control @error('max_payload') is-invalid @enderror" 
                                           style="padding-left: 48px !important;"
                                           placeholder="e.g. 18.50"
                                           value="{{ old('max_payload', $subType->max_payload) }}">
                                    <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-tachometer fs-5"></i>
                                    </span>
                                </div>
                                @error('max_payload')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-12 col-md-6">
                                <label for="is_active" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="is_active" id="is_active" class="form-select @error('is_active') is-invalid @enderror" required>
                                    <option value="1" {{ old('is_active', $subType->is_active) == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('is_active', $subType->is_active) == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top border-secondary border-opacity-25">
                        <a href="{{ route('all.truck.sub.types') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3 fw-semibold">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 fw-semibold d-inline-flex align-items-center gap-2">
                            <i class="bx bx-check-circle fs-5"></i>
                            <span>Update Sub-Type</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

@endsection
