@extends('admin.master_admin')
@section('admin')

<style>
    /* ─── Status Dropdown Dynamic Badges ─── */
    .onboarding-status-select {
        font-weight: 600 !important;
        font-size: 0.85rem !important;
        border-radius: 20px !important;
        padding: 5px 12px 5px 28px !important;
        cursor: pointer !important;
        transition: all 0.25s ease-in-out !important;
        width: auto !important;
        display: inline-block !important;
        box-shadow: none !important;
        white-space: nowrap !important;
    }

    .onboarding-status-select.status-badge-active {
        background-color: rgba(34, 197, 94, 0.18) !important;
        color: #22C55E !important;
        border: 1px solid rgba(34, 197, 94, 0.4) !important;
    }

    .onboarding-status-select.status-badge-inactive {
        background-color: rgba(148, 163, 184, 0.18) !important;
        color: #94A3B8 !important;
        border: 1px solid rgba(148, 163, 184, 0.4) !important;
    }

    .onboarding-status-select option {
        background-color: #1E293B !important;
        color: #F8FAFC !important;
        font-weight: 600;
        padding: 8px;
    }

    html.light-theme .onboarding-status-select option {
        background-color: #FFFFFF !important;
        color: #0F172A !important;
    }
</style>

<!-- Page Header -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold header-title mb-1">
            <i class="bx bx-slideshow text-info me-2"></i>Onboarding Screens
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Manage and customize introductory onboarding slides, images, titles, descriptions, and sequence order.
        </p>
    </div>
    <div>
        <a href="{{ route('add.onboarding') }}" class="btn btn-primary rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-plus-circle fs-5"></i>
            <span>Add New Slide</span>
        </a>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-lg rounded-4 overflow-hidden">
    <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3 px-4 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
            <i class="bx bx-table text-info fs-4"></i>
            <span>Onboarding Slides List</span>
        </h5>
        <span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-25 px-3 py-1 fw-semibold fs-6">
            Total Slides: {{ count($onboardings) }}
        </span>
    </div>

    <div class="card-body p-4">
        <div class="table-responsive">
            <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th style="width: 50px;" class="text-center">Order</th>
                        <th style="width: 80px;" class="text-center">Image</th>
                        <th>Title (En / Ar)</th>
                        <th>Description (En / Ar)</th>
                        <th style="width: 140px;" class="text-center">Status</th>
                        <th style="width: 130px;">Created Date</th>
                        <th class="text-center" style="width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($onboardings as $key => $item)
                    <tr>
                        <!-- Slide Order -->
                        <td class="text-center">
                            <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50 px-2.5 py-1.5 rounded-pill fw-bold fs-7">
                                #{{ $item->sort_order ?? ($key + 1) }}
                            </span>
                        </td>

                        <!-- Slide Image -->
                        <td class="text-center">
                            <img src="{{ (!empty($item->image) && file_exists(public_path('upload/onboarding_images/'.$item->image))) ? asset('upload/onboarding_images/'.$item->image) : asset('upload/no_image.jpg') }}" 
                                 class="rounded-3 border border-info shadow-sm media-zoomable cursor-pointer" 
                                 data-title="{{ $item->title_en }} - Preview"
                                 style="width: 56px; height: 56px; object-fit: cover;" 
                                 alt="{{ $item->title_en }}">
                        </td>

                        <!-- Title -->
                        <td class="fw-bold fs-6">
                            <div class="d-flex flex-column gap-1">
                                <span class="text-white fw-bold fs-6">{{ $item->title_en }}</span>
                                @if(!empty($item->title_ar))
                                    <small class="text-info" dir="rtl" style="font-size: 0.85rem;">{{ $item->title_ar }}</small>
                                @endif
                            </div>
                        </td>

                        <!-- Description -->
                        <td>
                            <div class="d-flex flex-column gap-1" style="max-width: 320px;">
                                @if(!empty($item->description_en))
                                    <span class="text-slate-300 fs-7 text-truncate" title="{{ $item->description_en }}">{{ Str::limit($item->description_en, 60) }}</span>
                                @else
                                    <span class="text-slate-500 fs-7 italic">No English description</span>
                                @endif

                                @if(!empty($item->description_ar))
                                    <small class="text-slate-400 fs-8 text-truncate" dir="rtl" title="{{ $item->description_ar }}">{{ Str::limit($item->description_ar, 60) }}</small>
                                @endif
                            </div>
                        </td>

                        <!-- Status Selector with Instant AJAX -->
                        <td class="text-center" style="min-width: 140px;">
                            <select class="form-select onboarding-status-select status-badge-{{ $item->status }}" 
                                    data-slide-id="{{ $item->id }}" 
                                    title="Change Slide Status">
                                <option value="active" {{ $item->status === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $item->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </td>

                        <!-- Date -->
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

                        <!-- Actions -->
                        <td class="text-center" style="min-width: 100px;">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <!-- Edit Button -->
                                <a href="{{ route('edit.onboarding', $item->id) }}" 
                                   class="btn btn-sm rounded-3 p-2 d-inline-flex align-items-center justify-content-center text-white border-0 shadow-sm" 
                                   style="width: 36px; height: 36px; background-color: #06B6D4;"
                                   title="Edit Slide">
                                    <i class="bx bx-edit fs-5"></i>
                                </a>

                                <!-- Delete Button -->
                                <a href="{{ route('delete.onboarding', $item->id) }}" 
                                   class="btn btn-sm rounded-3 p-2 d-inline-flex align-items-center justify-content-center text-white border-0 shadow-sm delete-onboarding-btn" 
                                   style="width: 36px; height: 36px; background-color: #F43F5E;"
                                   title="Delete Slide">
                                    <i class="bx bx-trash fs-5"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-slate-400">
                            <i class="bx bx-slideshow fs-1 text-info opacity-50 d-block mb-2"></i>
                            <span>No onboarding slides found. Click <strong>"Add New Slide"</strong> to create the first screen.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- AJAX Status Change & SweetAlert Delete Script -->
<script type="text/javascript">
    $(document).ready(function() {
        // Status dropdown change event
        $(document).on('change', '.onboarding-status-select', function() {
            var selectElem = $(this);
            var slideId = selectElem.data('slide-id');
            var newStatus = selectElem.val();

            selectElem.removeClass('status-badge-active status-badge-inactive')
                      .addClass('status-badge-' + newStatus);

            $.ajax({
                url: "{{ route('onboarding.status.ajax') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: slideId,
                    status: newStatus
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message || 'Status updated successfully!');
                    } else {
                        toastr.error('Failed to update status.');
                    }
                },
                error: function(xhr) {
                    toastr.error('An error occurred while updating status.');
                }
            });
        });

        // Delete SweetAlert confirmation
        $(document).on('click', '.delete-onboarding-btn', function(e) {
            e.preventDefault();
            var link = $(this).attr("href");

            Swal.fire({
                title: 'Are you sure?',
                text: "Delete this onboarding slide permanently?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#F43F5E',
                cancelButtonColor: '#64748B',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = link;
                }
            });
        });
    });
</script>

@endsection
