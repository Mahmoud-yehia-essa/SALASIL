@extends('admin.master_admin')
@section('admin')

<div class="row">
    <div class="col-12 col-lg-8 mx-auto">
        <div class="card border-0 shadow-lg rounded-4 p-4">
            <div class="d-flex align-items-center justify-content-between mb-4 border-bottom border-secondary border-opacity-25 pb-3">
                <h4 class="mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="bx bx-edit text-primary fs-3"></i>
                    <span>تعديل متطلب مستند الشركة</span>
                </h4>
                <a href="{{ route('all.company.documents') }}" class="btn btn-outline-secondary rounded-3 px-3">
                    <i class="bx bx-arrow-back me-1"></i> رجوع
                </a>
            </div>

            <form action="{{ route('update.company.document') }}" method="POST">
                @csrf
                <input type="hidden" name="id" value="{{ $document->id }}">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">اسم المستند (بالعربية) <span class="text-danger">*</span></label>
                        <input type="text" name="name_ar" class="form-control rounded-3 @error('name_ar') is-invalid @enderror" value="{{ old('name_ar', $document->name_ar) }}" required>
                        @error('name_ar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">اسم المستند (English) <span class="text-danger">*</span></label>
                        <input type="text" name="name_en" class="form-control rounded-3 @error('name_en') is-invalid @enderror" value="{{ old('name_en', $document->name_en) }}" required>
                        @error('name_en')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">الوصف (بالعربية)</label>
                        <textarea name="description_ar" class="form-control rounded-3" rows="3">{{ old('description_ar', $document->description_ar) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">الوصف (English)</label>
                        <textarea name="description_en" class="form-control rounded-3" rows="3">{{ old('description_en', $document->description_en) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">نوع الطلب في التطبيق <span class="text-danger">*</span></label>
                        <select name="is_required" class="form-select rounded-3">
                            <option value="1" {{ $document->is_required ? 'selected' : '' }}>مطلوب إلزامي (Required / Mandatory)</option>
                            <option value="0" {{ !$document->is_required ? 'selected' : '' }}>اختياري (Optional)</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">ترتيب الظهور</label>
                        <input type="number" name="sort_order" class="form-control rounded-3" value="{{ old('sort_order', $document->sort_order) }}">
                    </div>

                    <div class="col-12 mt-3">
                        <div class="form-check form-switch fs-6">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActiveSwitch" {{ $document->is_active ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="isActiveSwitch">تفعيل المستند وظهوره في التطبيق</label>
                        </div>
                    </div>

                    <div class="col-12 mt-4 pt-2 border-top border-secondary border-opacity-25 d-flex justify-content-end gap-2">
                        <a href="{{ route('all.company.documents') }}" class="btn btn-secondary rounded-3 px-4">إلغاء</a>
                        <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold">حفظ التعديلات</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
