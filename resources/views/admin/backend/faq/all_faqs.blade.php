@extends('admin.master_admin')
@section('admin')

<style>
    .faq-status-select {
        font-weight: 600 !important;
        font-size: 0.85rem !important;
        border-radius: 20px !important;
        padding: 5px 12px 5px 28px !important;
        cursor: pointer !important;
        transition: all 0.25s ease-in-out !important;
        width: auto !important;
        display: inline-block !important;
        box-shadow: none !important;
    }
    .faq-status-select.status-badge-active {
        background-color: rgba(34, 197, 94, 0.18) !important;
        color: #22C55E !important;
        border: 1px solid rgba(34, 197, 94, 0.4) !important;
    }
    .faq-status-select.status-badge-inactive {
        background-color: rgba(148, 163, 184, 0.18) !important;
        color: #94A3B8 !important;
        border: 1px solid rgba(148, 163, 184, 0.4) !important;
    }
</style>

<!-- Page Header -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold header-title mb-1">
            <i class="bx bx-help-circle text-info me-2"></i>Frequently Asked Questions (FAQ)
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Manage and organize questions, answers, and categories displayed in the mobile app support center.
        </p>
    </div>
    <div>
        <a href="{{ route('add.faq') }}" class="btn btn-primary rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow">
            <i class="bx bx-plus-circle fs-5"></i>
            <span>Add New FAQ</span>
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background-color: var(--card-bg, #1e293b);">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4" style="width: 60px;">#</th>
                        <th>Question</th>
                        <th>Category</th>
                        <th class="text-center" style="width: 100px;">Order</th>
                        <th class="text-center" style="width: 140px;">Status</th>
                        <th class="text-end pe-4" style="width: 130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($faqs as $key => $item)
                    <tr>
                        <td class="ps-4 text-muted fw-semibold">{{ $key + 1 }}</td>
                        <td style="white-space: normal; min-width: 280px; max-width: 450px;">
                            <div class="fw-bold text-slate-200 mb-1">{{ $item->question }}</div>
                            <div class="text-slate-400 small text-truncate" style="max-width: 420px;">
                                {{ Str::limit($item->answer, 110) }}
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3 py-1">
                                {{ $item->category }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary bg-opacity-25 text-slate-300 rounded-pill px-2 py-1">
                                {{ $item->order_sequence }}
                            </span>
                        </td>
                        <td class="text-center">
                            <select class="form-select form-select-sm faq-status-select {{ $item->status ? 'status-badge-active' : 'status-badge-inactive' }}" 
                                    data-id="{{ $item->id }}">
                                <option value="1" {{ $item->status ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ !$item->status ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('edit.faq', $item->id) }}" class="btn btn-sm btn-outline-info rounded-3" title="Edit FAQ">
                                    <i class="bx bx-edit-alt"></i>
                                </a>
                                <a href="{{ route('delete.faq', $item->id) }}" class="btn btn-sm btn-outline-danger rounded-3" id="delete" title="Delete FAQ">
                                    <i class="bx bx-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bx bx-help-circle fs-1 mb-2 d-block"></i>
                            No FAQs found. Click "Add New FAQ" to create one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const statusSelects = document.querySelectorAll('.faq-status-select');
    statusSelects.forEach(select => {
        select.addEventListener('change', function () {
            const faqId = this.getAttribute('data-id');
            const newStatus = this.value;
            const targetSelect = this;

            fetch("{{ route('faq.status.ajax') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    id: faqId,
                    status: newStatus
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status) {
                    if (newStatus == '1') {
                        targetSelect.classList.remove('status-badge-inactive');
                        targetSelect.classList.add('status-badge-active');
                    } else {
                        targetSelect.classList.remove('status-badge-active');
                        targetSelect.classList.add('status-badge-inactive');
                    }
                    if (typeof toastr !== 'undefined') {
                        toastr.success(data.message);
                    }
                }
            })
            .catch(() => {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Failed to update status');
                }
            });
        });
    });
});
</script>

@endsection
