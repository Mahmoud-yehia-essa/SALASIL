@extends('admin.master_admin')
@section('admin')

<style>
    /* Custom Styling for Truck Status Dropdown Select Badges */
    select.truck-status-select {
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

    .truck-status-select.status-badge-active {
        background-color: rgba(34, 197, 94, 0.18) !important;
        color: #22C55E !important;
        border: 1px solid rgba(34, 197, 94, 0.4) !important;
    }

    .truck-status-select.status-badge-inactive {
        background-color: rgba(148, 163, 184, 0.18) !important;
        color: #94A3B8 !important;
        border: 1px solid rgba(148, 163, 184, 0.4) !important;
    }

    .truck-status-select option {
        background-color: #1E293B !important;
        color: #F8FAFC !important;
        font-weight: 600;
        padding: 8px;
    }

    html.light-theme .truck-status-select option {
        background-color: #FFFFFF !important;
        color: #0F172A !important;
    }
</style>

<!-- Page Header -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold header-title mb-1">
            <i class="bx bx-truck text-info me-2"></i>Truck Types List
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Manage and view all registered truck fleet classifications and load capacities.
        </p>
    </div>
    <div>
        <a href="{{ route('add.truck.type') }}" class="btn btn-primary rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-plus-circle fs-5"></i>
            <span>Add New Truck Type</span>
        </a>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-lg rounded-4 overflow-hidden">
    <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3 px-4 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
            <i class="bx bx-table text-info fs-4"></i>
            <span>Truck Types Records</span>
        </h5>
        <span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-25 px-3 py-1 fw-semibold fs-6">
            Total Types: {{ count($truckTypes) }}
        </span>
    </div>

    <div class="card-body p-4">
        <div class="table-responsive">
            <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th style="width: 70px;">Photo</th>
                        <th>Truck Type Name</th>
                        <th>Max Weight (Tons)</th>
                        <th style="width: 155px;">Status</th>
                        <th>Created Date</th>
                        <th class="text-center" style="width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($truckTypes as $key => $item)
                    <tr>
                        <td class="text-center fw-bold">{{ $key + 1 }}</td>
                        <td class="text-center">
                            <img src="{{ (!empty($item->photo) && file_exists(public_path('upload/truck_images/'.$item->photo))) ? url('upload/truck_images/'.$item->photo) : url('upload/no_image.jpg') }}" 
                                 class="rounded-3 border border-info shadow-sm media-zoomable cursor-pointer" 
                                 data-title="{{ $item->name_en }} - Photo"
                                 style="width: 48px; height: 48px; object-fit: cover;" 
                                 alt="{{ $item->name_en }}">
                        </td>
                        <td class="fw-bold fs-6">
                            <div class="d-flex flex-column">
                                <span class="text-white fw-bold fs-6">{{ $item->name_en }}</span>
                                @if(!empty($item->name_ar) && $item->name_ar !== $item->name_en)
                                    <small class="text-slate-400" style="font-size: 0.8rem;">{{ $item->name_ar }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($item->max_weight !== null)
                                <span class="badge bg-primary bg-opacity-20 text-primary border border-primary border-opacity-50 px-3 py-1.5 rounded-pill fw-semibold fs-7">
                                    <i class="bx bx-tachometer me-1"></i> {{ number_format($item->max_weight, 2) }} Tons
                                </span>
                            @else
                                <span class="text-slate-400">N/A</span>
                            @endif
                        </td>
                        <!-- Dynamic Status Dropdown with Instant AJAX Update -->
                        <td style="min-width: 155px;">
                            <select class="form-select truck-status-select status-badge-{{ $item->status }}" 
                                    data-truck-id="{{ $item->id }}" 
                                    title="Change Status">
                                <option value="active" {{ $item->status === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $item->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                                <a href="{{ route('edit.truck.type', $item->id) }}" 
                                   class="btn btn-sm rounded-3 p-2 d-inline-flex align-items-center justify-content-center text-white border-0 shadow-sm" 
                                   style="width: 36px; height: 36px; background-color: #06B6D4;"
                                   title="Edit Truck Type">
                                    <i class="bx bx-edit fs-5"></i>
                                </a>

                                <!-- Delete Button -->
                                <a href="{{ route('delete.truck.type', $item->id) }}" 
                                   class="btn btn-sm rounded-3 p-2 d-inline-flex align-items-center justify-content-center text-white border-0 shadow-sm delete-truck-type-btn" 
                                   style="width: 36px; height: 36px; background-color: #F43F5E;"
                                   title="Delete Truck Type">
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

@endsection
