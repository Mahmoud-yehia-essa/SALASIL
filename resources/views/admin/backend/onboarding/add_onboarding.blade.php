@extends('admin.master_admin')
@section('admin')

<!-- Page Header -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold header-title mb-1">
            <i class="bx bx-plus-circle text-info me-2"></i>Add New Onboarding Slide
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Create a new introductory onboarding screen with bilingual content and media.
        </p>
    </div>
    <div>
        <a href="{{ route('all.onboarding') }}" class="btn btn-outline-secondary rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-arrow-back fs-5"></i>
            <span>Back to All Slides</span>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12 col-xl-10 mx-auto">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3 px-4">
                <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="bx bx-slideshow text-info fs-4"></i>
                    <span>Slide Information & Content</span>
                </h5>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ route('store.onboarding') }}" enctype="multipart/form-data" id="onboardingForm">
                    @csrf

                    <!-- Titles Section (English & Arabic) -->
                    <div class="mb-4">
                        <h6 class="text-info fw-bold text-uppercase fs-7 mb-3" style="letter-spacing: 0.5px;">
                            <i class="bx bx-heading me-1"></i> Slide Titles (العنوان)
                        </h6>
                        <div class="row g-3">
                            <!-- English Title -->
                            <div class="col-12 col-md-6">
                                <label for="title_en" class="form-label fw-semibold">Title (English) <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="text" 
                                           name="title_en" 
                                           id="title_en" 
                                           class="form-control @error('title_en') is-invalid @enderror" 
                                           style="padding-left: 48px !important;"
                                           placeholder="e.g. Fast & Reliable Freight Logistics"
                                           value="{{ old('title_en') }}"
                                           required>
                                    <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-font fs-5"></i>
                                    </span>
                                </div>
                                @error('title_en')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Arabic Title -->
                            <div class="col-12 col-md-6">
                                <label for="title_ar" class="form-label fw-semibold">Title (Arabic - العنوان بالعربي)</label>
                                <div class="position-relative">
                                    <input type="text" 
                                           name="title_ar" 
                                           id="title_ar" 
                                           dir="rtl"
                                           class="form-control @error('title_ar') is-invalid @enderror" 
                                           style="padding-right: 48px !important;"
                                           placeholder="مثال: نقل موثوق وسريع لجميع شحناتك"
                                           value="{{ old('title_ar') }}">
                                    <span class="position-absolute text-info" style="right: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-text fs-5"></i>
                                    </span>
                                </div>
                                @error('title_ar')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="border-secondary opacity-25 my-4">

                    <!-- Descriptions Section (English & Arabic) -->
                    <div class="mb-4">
                        <h6 class="text-info fw-bold text-uppercase fs-7 mb-3" style="letter-spacing: 0.5px;">
                            <i class="bx bx-detail me-1"></i> Slide Description & Text (الوصف / تفاصيل الشريحة)
                        </h6>
                        <div class="row g-3">
                            <!-- English Description -->
                            <div class="col-12 col-md-6">
                                <label for="description_en" class="form-label fw-semibold">Description (English)</label>
                                <textarea name="description_en" 
                                          id="description_en" 
                                          rows="4"
                                          class="form-control @error('description_en') is-invalid @enderror"
                                          placeholder="Write a clear and engaging description for this slide...">{{ old('description_en') }}</textarea>
                                @error('description_en')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Arabic Description -->
                            <div class="col-12 col-md-6">
                                <label for="description_ar" class="form-label fw-semibold">Description (Arabic - الوصف بالعربي)</label>
                                <textarea name="description_ar" 
                                          id="description_ar" 
                                          dir="rtl"
                                          rows="4"
                                          class="form-control @error('description_ar') is-invalid @enderror"
                                          placeholder="اكتب شرحاً واضحاً وموجزاً لهذه الشريحة...">{{ old('description_ar') }}</textarea>
                                @error('description_ar')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="border-secondary opacity-25 my-4">

                    <!-- Order & Status Section -->
                    <div class="mb-4">
                        <h6 class="text-info fw-bold text-uppercase fs-7 mb-3" style="letter-spacing: 0.5px;">
                            <i class="bx bx-sort-alt-2 me-1"></i> Ordering & Status
                        </h6>
                        <div class="row g-3">
                            <!-- Sort Order -->
                            <div class="col-12 col-md-6">
                                <label for="sort_order" class="form-label fw-semibold">Slide Sequence Order (ترتيب الظهور) <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="number" 
                                           name="sort_order" 
                                           id="sort_order" 
                                           min="1"
                                           class="form-control @error('sort_order') is-invalid @enderror" 
                                           style="padding-left: 48px !important;"
                                           value="{{ old('sort_order', $nextOrder ?? 1) }}"
                                           required>
                                    <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-sort-numbered fs-5"></i>
                                    </span>
                                </div>
                                <small class="text-slate-400 d-block mt-1">Slides will be displayed sequentially based on this number (1, 2, 3...).</small>
                                @error('sort_order')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-12 col-md-6">
                                <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active (مفعل للظهور)</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive (معطل)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr class="border-secondary opacity-25 my-4">

                    <!-- Image Upload Section -->
                    <div class="mb-4">
                        <h6 class="text-info fw-bold text-uppercase fs-7 mb-3" style="letter-spacing: 0.5px;">
                            <i class="bx bx-image me-1"></i> Slide Illustration / Graphic (صورة الشريحة)
                        </h6>
                        <div class="row align-items-center g-3">
                            <div class="col-12 col-md-8">
                                <label for="image" class="form-label fw-semibold">Choose Illustration Image</label>
                                <input type="file" 
                                       name="image" 
                                       id="image" 
                                       class="form-control @error('image') is-invalid @enderror"
                                       accept="image/*">
                                <small class="text-slate-400 d-block mt-1">
                                    Allowed formats: JPG, PNG, WEBP, SVG (Max size: 5MB). High quality vector illustrations or transparent PNGs recommended.
                                </small>
                                @error('image')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4 text-center text-md-start">
                                <label class="form-label fw-semibold d-block">Live Preview (Click to Enlarge)</label>
                                <div class="position-relative d-inline-block media-zoomable cursor-pointer" data-title="Slide Image Preview" style="cursor: pointer;">
                                    <img id="showImage" 
                                         src="{{ asset('upload/no_image.jpg') }}" 
                                         alt="Slide Image Preview" 
                                         class="rounded-3 border border-info shadow-sm p-1"
                                         style="width: 100px; height: 100px; object-fit: cover;">
                                    <span class="position-absolute bottom-0 end-0 bg-info text-dark rounded-circle p-1 d-flex align-items-center justify-content-center shadow-sm" style="width: 26px; height: 26px; font-size: 13px;">
                                        <i class="bx bx-search-alt-2"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top border-secondary border-opacity-25">
                        <a href="{{ route('all.onboarding') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3 fw-semibold">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 fw-semibold d-inline-flex align-items-center gap-2">
                            <i class="bx bx-check-circle fs-5"></i>
                            <span>Save Onboarding Slide</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        // Live image preview
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
