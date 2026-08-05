@extends('admin.master_admin')
@section('admin')

<style>
    /* Custom Styling for Model Status Dropdown Select Badges */
    select.model-status-select {
        width: 145px !important;
        min-width: 145px !important;
        max-width: 145px !important;
        height: 38px !important;
        padding-left: 14px !important;
        padding-right: 28px !important;
        background-position: right 10px center !important;
        background-size: 12px 8px !important;
        border-radius: 50rem !important;
        font-weight: 700 !important;
        font-size: 0.84rem !important;
        line-height: 1.3 !important;
        cursor: pointer !important;
        text-align: left !important;
        display: inline-block !important;
        box-shadow: none !important;
        white-space: nowrap !important;
    }

    .model-status-select.status-badge-active {
        background-color: rgba(34, 197, 94, 0.18) !important;
        color: #22C55E !important;
        border: 1px solid rgba(34, 197, 94, 0.4) !important;
    }

    .model-status-select.status-badge-inactive {
        background-color: rgba(148, 163, 184, 0.18) !important;
        color: #94A3B8 !important;
        border: 1px solid rgba(148, 163, 184, 0.4) !important;
    }

    .model-status-select option {
        background-color: #1E293B !important;
        color: #F8FAFC !important;
        font-weight: 600;
        padding: 8px;
    }

    html.light-theme .model-status-select option {
        background-color: #FFFFFF !important;
        color: #0F172A !important;
    }
</style>

<!-- Page Header -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold header-title mb-1">
            <i class="bx bx-car text-info me-2"></i>Truck Models List
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Manage and view all registered truck models linked to manufacturer brands.
        </p>
    </div>
    <div>
        <a href="{{ route('add.truck.model') }}" class="btn btn-primary rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-plus-circle fs-5"></i>
            <span>Add New Model</span>
        </a>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-lg rounded-4 overflow-hidden">
    <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
            <i class="bx bx-table text-info fs-4"></i>
            <span>Truck Models Records</span>
        </h5>
        <span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-25 px-3 py-1 fw-semibold fs-6">
            Total Models: {{ count($models) }}
        </span>
    </div>

    <div class="card-body p-4">
        <div class="table-responsive">
            <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th style="width: 40px;" class="text-center">#</th>
                        <th>Parent Brand</th>
                        <th>Model Name (English)</th>
                        <th>Model Name (Arabic)</th>
                        <th style="width: 155px;">Status</th>
                        <th>Created Date</th>
                        <th class="text-center" style="width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($models as $key => $item)
                        <tr>
                            <td class="text-center fw-bold">{{ $key + 1 }}</td>
                            <td>
                                <span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-50 px-3 py-1.5 rounded-pill fw-semibold">
                                    <i class="bx bx-badge-check me-1"></i> {{ $item->brand->name_en ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="fw-bold fs-6">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bx bx-car text-info fs-5"></i>
                                    <span class="fw-bold fs-6">{{ $item->name_en }}</span>
                                </div>
                            </td>
                            <td class="text-slate-300">{{ $item->name_ar ?? 'N/A' }}</td>
                            <!-- Dynamic Status Dropdown with Instant AJAX Update -->
                            <td style="min-width: 155px;">
                                <select class="form-select model-status-select status-badge-{{ $item->is_active ? 'active' : 'inactive' }}" 
                                        data-model-id="{{ $item->id }}" 
                                        title="Change Status">
                                    <option value="1" {{ $item->is_active == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ $item->is_active == 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </td>
                            <td>
                                @if($item->created_at)
                                    <div class="d-flex flex-column gap-1">
                                        <span class="fw-semibold joined-date-text fs-7">
                                            <i class="bx bx-calendar text-info me-1"></i> {{ $item->created_at->format('M d, Y') }}
                                        </span>
                                        <span class="text-slate-400" style="font-size: 0.78rem;">
                                            <i class="bx bx-time-five me-1 text-info"></i> {{ $item->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-slate-400">N/A</span>
                                @endif
                            </td>
                            <td class="text-center" style="min-width: 100px;">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <!-- Edit Button -->
                                    <a href="{{ route('edit.truck.model', $item->id) }}" 
                                       class="btn btn-sm rounded-3 p-2 d-inline-flex align-items-center justify-content-center text-white border-0 shadow-sm" 
                                       style="width: 36px; height: 36px; background-color: #06B6D4;"
                                       title="Edit Model">
                                        <i class="bx bx-edit fs-5"></i>
                                    </a>

                                    <!-- Delete Button -->
                                    <a href="{{ route('delete.truck.model', $item->id) }}" 
                                       class="btn btn-sm rounded-3 p-2 d-inline-flex align-items-center justify-content-center text-white border-0 shadow-sm delete-truck-model-btn" 
                                       style="width: 36px; height: 36px; background-color: #F43F5E;"
                                       title="Delete Model">
                                        <i class="bx bx-trash fs-5"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        // Dynamic AJAX Status Change Handler
        $(document).on('change', '.model-status-select', function(e) {
            e.preventDefault();
            var select = $(this);
            var modelId = select.attr('data-model-id') || select.data('model-id');
            var isActive = select.val();
            var csrfToken = $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}";

            select.prop('disabled', true);

            $.ajax({
                url: "{{ route('truck.model.status.ajax') }}",
                type: "POST",
                data: {
                    _token: csrfToken,
                    id: modelId,
                    is_active: isActive
                },
                dataType: "json",
                success: function(response) {
                    select.prop('disabled', false);
                    select.removeClass('status-badge-active status-badge-inactive');
                    var badgeClass = (isActive == '1' || isActive == 1) ? 'status-badge-active' : 'status-badge-inactive';
                    select.addClass(badgeClass);

                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message || 'Model status updated successfully!');
                    }
                },
                error: function(xhr) {
                    select.prop('disabled', false);
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to update model status.';
                    if (typeof toastr !== 'undefined') toastr.error(msg);
                }
            });
        });
    });
</script>

@endsection
