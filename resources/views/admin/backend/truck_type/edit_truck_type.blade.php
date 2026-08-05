@extends('admin.master_admin')
@section('admin')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<!-- Page Header -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold mb-1 header-title">
            <i class="bx bx-edit text-info me-2"></i>Edit Truck Type
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Update details and configuration for truck type: <span class="text-info fw-semibold">{{ $truckType->name_en }}</span>
        </p>
    </div>
    <div>
        <a href="{{ route('all.truck.types') }}" class="btn btn-outline-info rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-list-ul fs-5"></i>
            <span>View All Truck Types</span>
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3 px-4">
                <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="bx bx-truck text-info fs-4"></i>
                    <span>Edit Truck Type Information</span>
                </h5>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ route('update.truck.type') }}" enctype="multipart/form-data" id="truckEditForm">
                    @csrf
                    <input type="hidden" name="id" value="{{ $truckType->id }}">

                    <!-- Main Information Section -->
                    <div class="mb-4">
                        <h6 class="text-info fw-bold text-uppercase fs-7 mb-3" style="letter-spacing: 0.5px;">
                            <i class="bx bx-info-circle me-1"></i> General Details
                        </h6>
                        <div class="row g-3">
                            <!-- Truck Type Name (English) -->
                            <div class="col-12 col-md-6">
                                <label for="name_en" class="form-label fw-semibold">Truck Type Name (English) <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="text" 
                                           name="name_en" 
                                           id="name_en" 
                                           class="form-control @error('name_en') is-invalid @enderror" 
                                           style="padding-left: 48px !important;"
                                           placeholder="e.g. Flatbed Trailer / Heavy Duty Truck"
                                           value="{{ old('name_en', $truckType->name_en) }}"
                                           required>
                                    <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-truck fs-5"></i>
                                    </span>
                                </div>
                                @error('name_en')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Max Weight in Tons -->
                            <div class="col-12 col-md-6">
                                <label for="max_weight" class="form-label fw-semibold">Max Weight (Tons)</label>
                                <div class="position-relative">
                                    <input type="number" 
                                           step="0.01"
                                           name="max_weight" 
                                           id="max_weight" 
                                           class="form-control @error('max_weight') is-invalid @enderror" 
                                           style="padding-left: 48px !important;"
                                           placeholder="e.g. 25.50"
                                           value="{{ old('max_weight', $truckType->max_weight) }}">
                                    <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-tachometer fs-5"></i>
                                    </span>
                                </div>
                                @error('max_weight')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-12 col-md-6">
                                <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', $truckType->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $truckType->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr class="border-secondary opacity-25 my-4">

                    <!-- Photo Upload Section -->
                    <div class="mb-4">
                        <h6 class="text-info fw-bold text-uppercase fs-7 mb-3" style="letter-spacing: 0.5px;">
                            <i class="bx bx-image me-1"></i> Truck Type Photo / Icon
                        </h6>
                        <div class="row align-items-center g-3">
                            <div class="col-12 col-md-8">
                                <label for="image" class="form-label fw-semibold">Change Image</label>
                                <input type="file" 
                                       name="photo" 
                                       id="image" 
                                       class="form-control"
                                       accept="image/*">
                                <small class="text-slate-400 d-block mt-1">
                                    Allowed formats: JPG, PNG, WEBP, SVG (Max size: 2MB).
                                </small>
                            </div>

                            <div class="col-12 col-md-4 text-center text-md-start">
                                <label class="form-label fw-semibold d-block">Image Preview (Click to Enlarge)</label>
                                <div class="position-relative d-inline-block media-zoomable cursor-pointer" data-title="Truck Type Photo Preview" style="cursor: pointer;">
                                    <img id="showImage" 
                                         src="{{ (!empty($truckType->photo) && file_exists(public_path('upload/truck_images/'.$truckType->photo))) ? url('upload/truck_images/'.$truckType->photo) : url('upload/no_image.jpg') }}" 
                                         alt="Truck Image Preview" 
                                         class="rounded-3 border border-info shadow-sm p-1"
                                         style="width: 90px; height: 90px; object-fit: cover;">
                                    <span class="position-absolute bottom-0 end-0 bg-info text-dark rounded-circle p-1 d-flex align-items-center justify-content-center shadow-sm" style="width: 24px; height: 24px; font-size: 12px;">
                                        <i class="bx bx-search-alt-2"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top border-secondary border-opacity-25">
                        <a href="{{ route('all.truck.types') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3 fw-semibold">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 fw-semibold d-inline-flex align-items-center gap-2">
                            <i class="bx bx-check-circle fs-5"></i>
                            <span>Update Truck Type</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        // Live image preview handler
        $('#image').change(function(e){
            var reader = new FileReader();
            reader.onload = function(e){
                $('#showImage').attr('src', e.target.result);
            }
            if (e.target.files[0]) {
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    });
</script>

@endsection
