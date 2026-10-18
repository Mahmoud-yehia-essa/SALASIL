@extends('admin.master_admin')
@section('admin')

<style>
    .doc-required-badge {
        cursor: pointer;
        padding: 6px 12px;
        border-radius: 50rem;
        font-weight: 700;
        font-size: 0.82rem;
        transition: all 0.2s ease;
    }
    .badge-is-required {
        background-color: rgba(220, 38, 38, 0.15);
        color: #DC2626;
        border: 1px solid rgba(220, 38, 38, 0.35);
    }
    .badge-is-optional {
        background-color: rgba(100, 116, 139, 0.15);
        color: #64748B;
        border: 1px solid rgba(100, 116, 139, 0.35);
    }
    .doc-status-badge {
        cursor: pointer;
        padding: 6px 12px;
        border-radius: 50rem;
        font-weight: 700;
        font-size: 0.82rem;
        transition: all 0.2s ease;
    }
    .badge-status-active {
        background-color: rgba(34, 197, 94, 0.15);
        color: #22C55E;
        border: 1px solid rgba(34, 197, 94, 0.35);
    }
    .badge-status-inactive {
        background-color: rgba(148, 163, 184, 0.15);
        color: #94A3B8;
        border: 1px solid rgba(148, 163, 184, 0.35);
    }
</style>

<!-- Page Header -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold header-title mb-1">
            <i class="bx bx-file-blank text-primary me-2"></i>مستندات الشركات المطلوبة (Company Documents)
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            تحديد وإدارة المستندات المطلوبة لحسابات الشركات في التطبيق وتحديد كون المستند مطلوباً (Required) أو اختيارياً (Optional).
        </p>
    </div>
    <div>
        <a href="{{ route('add.company.document') }}" class="btn btn-primary rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-plus-circle fs-5"></i>
            <span>إضافة مستند جديد</span>
        </a>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-lg rounded-4 overflow-hidden">
    <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3 px-4 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
            <i class="bx bx-list-check text-primary fs-4"></i>
            <span>قائمة المستندات المطلوبة</span>
        </h5>
        <span class="badge bg-primary bg-opacity-20 text-primary border border-primary border-opacity-25 px-3 py-1 fw-semibold fs-6">
            الإجمالي: {{ count($documents) }}
        </span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-dark bg-opacity-50 text-slate-300">
                    <tr>
                        <th class="ps-4" style="width: 60px;">#</th>
                        <th>اسم المستند (العربية)</th>
                        <th>اسم المستند (English)</th>
                        <th>الوصف</th>
                        <th class="text-center">نوع الطلب (Required / Optional)</th>
                        <th class="text-center">الحالة</th>
                        <th class="text-center">الملفات المرفوعة</th>
                        <th class="text-center pe-4" style="width: 140px;">العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $key => $item)
                    <tr>
                        <td class="ps-4 fw-bold text-muted">{{ $key + 1 }}</td>
                        <td>
                            <div class="fw-bold fs-6">{{ $item->name_ar }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold text-info">{{ $item->name_en }}</div>
                        </td>
                        <td>
                            <span class="text-muted small">{{ Str::limit($item->description_ar ?: $item->description_en, 50) }}</span>
                        </td>
                        <td class="text-center">
                            <span class="doc-required-badge {{ $item->is_required ? 'badge-is-required' : 'badge-is-optional' }}"
                                  onclick="toggleRequired({{ $item->id }}, this)">
                                <i class="bx {{ $item->is_required ? 'bx-error-circle' : 'bx-check-circle' }} me-1"></i>
                                <span>{{ $item->is_required ? 'مطلوب (Required)' : 'اختياري (Optional)' }}</span>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="doc-status-badge {{ $item->is_active ? 'badge-status-active' : 'badge-status-inactive' }}"
                                  onclick="changeStatus({{ $item->id }}, this)">
                                <span>{{ $item->is_active ? 'مفعل (Active)' : 'معطل (Inactive)' }}</span>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary bg-opacity-25 text-light px-2 py-1">
                                {{ $item->user_documents_count }} ملف
                            </span>
                        </td>
                        <td class="text-center pe-4">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('edit.company.document', $item->id) }}" class="btn btn-sm btn-outline-info rounded-2" title="تعديل">
                                    <i class="bx bx-edit fs-6"></i>
                                </a>
                                <a href="{{ route('delete.company.document', $item->id) }}" class="btn btn-sm btn-outline-danger rounded-2" id="delete" title="حذف">
                                    <i class="bx bx-trash fs-6"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bx bx-folder-open fs-1 d-block mb-2 text-secondary"></i>
                            لا توجد متطلبات مستندات مضافة بعد.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function toggleRequired(id, element) {
        $.ajax({
            url: "{{ route('toggle.company.document.required.ajax') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: id
            },
            success: function(response) {
                if(response.status) {
                    if(response.is_required) {
                        $(element).removeClass('badge-is-optional').addClass('badge-is-required');
                        $(element).find('i').removeClass('bx-check-circle').addClass('bx-error-circle');
                        $(element).find('span').text('مطلوب (Required)');
                    } else {
                        $(element).removeClass('badge-is-required').addClass('badge-is-optional');
                        $(element).find('i').removeClass('bx-error-circle').addClass('bx-check-circle');
                        $(element).find('span').text('اختياري (Optional)');
                    }
                    toastr.success(response.message);
                }
            },
            error: function() {
                toastr.error('حدث خطأ أثناء تعديل الحالة');
            }
        });
    }

    function changeStatus(id, element) {
        $.ajax({
            url: "{{ route('change.company.document.status.ajax') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: id
            },
            success: function(response) {
                if(response.status) {
                    if(response.is_active) {
                        $(element).removeClass('badge-status-inactive').addClass('badge-status-active');
                        $(element).find('span').text('مفعل (Active)');
                    } else {
                        $(element).removeClass('badge-status-active').addClass('badge-status-inactive');
                        $(element).find('span').text('معطل (Inactive)');
                    }
                    toastr.success(response.message);
                }
            },
            error: function() {
                toastr.error('حدث خطأ أثناء تعديل الحالة');
            }
        });
    }
</script>

@endsection
