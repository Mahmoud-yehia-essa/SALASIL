@extends('admin.master_admin')
@section('admin')

<style>
    /* Custom Styling for Status Badges & Selects */
    select.country-status-select {
        width: 130px !important;
        min-width: 130px !important;
        height: 36px !important;
        padding-left: 12px !important;
        padding-right: 24px !important;
        background-position: right 8px center !important;
        background-size: 10px 6px !important;
        border-radius: 50rem !important;
        font-weight: 700 !important;
        font-size: 0.82rem !important;
        line-height: 1.3 !important;
        cursor: pointer !important;
        display: inline-block !important;
        box-shadow: none !important;
        white-space: nowrap !important;
    }

    .country-status-select.status-badge-active {
        background-color: rgba(34, 197, 94, 0.18) !important;
        color: #22C55E !important;
        border: 1px solid rgba(34, 197, 94, 0.4) !important;
    }

    .country-status-select.status-badge-inactive {
        background-color: rgba(148, 163, 184, 0.18) !important;
        color: #94A3B8 !important;
        border: 1px solid rgba(148, 163, 184, 0.4) !important;
    }

    select option {
        background-color: #1E293B !important;
        color: #F8FAFC !important;
        font-weight: 600;
    }
</style>

<!-- Page Header -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold header-title mb-1">
            <i class="bx bx-globe text-info me-2"></i>Countries Management
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Manage countries, ISO country codes, active statuses, and associated cities.
        </p>
    </div>
    <div>
        <a href="{{ route('add.country') }}" class="btn btn-primary rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-plus-circle fs-5"></i>
            <span>Add New Country</span>
        </a>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-lg rounded-4 overflow-hidden">
    <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3 px-4 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
            <i class="bx bx-table text-info fs-4"></i>
            <span>Countries Database Records</span>
        </h5>
        <span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-25 px-3 py-1 fw-semibold fs-6">
            Total Records: {{ count($countries) }}
        </span>
    </div>

    <div class="card-body p-4">
        <div class="table-responsive">
            <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Country Name (EN)</th>
                        <th>Country Name (AR)</th>
                        <th>ISO Code</th>
                        <th>Associated Cities</th>
                        <th style="width: 140px;">Status</th>
                        <th>Created Date</th>
                        <th class="text-center" style="width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($countries as $key => $item)
                    <tr>
                        <td class="text-center fw-bold">{{ $key + 1 }}</td>
                        <td class="fw-bold text-white fs-6">
                            <i class="bx bx-flag me-1 text-info"></i> {{ $item->name_en }}
                        </td>
                        <td class="fw-semibold text-slate-300 fs-6" style="direction: rtl; text-align: right;">
                            {{ $item->name_ar }}
                        </td>
                        <td>
                            @if($item->code)
                                <span class="badge bg-dark text-warning border border-warning border-opacity-50 px-3 py-1.5 rounded-3 fw-mono fs-7" style="font-family: monospace; letter-spacing: 1px;">
                                    {{ $item->code }}
                                </span>
                            @else
                                <span class="text-slate-400">N/A</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-50 px-3 py-1.5 rounded-pill fw-semibold fs-7">
                                <i class="bx bx-building-house me-1"></i> {{ $item->cities_count ?? 0 }} Cities
                            </span>
                        </td>
                        <!-- Dynamic Status Dropdown -->
                        <td style="min-width: 140px;">
                            <select class="form-select country-status-select status-badge-{{ $item->is_active == 1 ? 'active' : 'inactive' }}" 
                                    data-country-id="{{ $item->id }}" 
                                    title="Change Active Status">
                                <option value="1" {{ $item->is_active == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ $item->is_active == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </td>
                        <td class="text-slate-300 fs-7">
                            {{ $item->created_at ? $item->created_at->format('Y-m-d') : 'N/A' }}
                        </td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <a href="{{ route('edit.country', $item->id) }}" 
                                   class="btn btn-sm btn-outline-info rounded-3 p-2 d-inline-flex align-items-center justify-content-center"
                                   title="Edit Country">
                                    <i class="bx bx-edit-alt fs-5"></i>
                                </a>
                                <a href="{{ route('delete.country', $item->id) }}" 
                                   class="btn btn-sm btn-outline-danger rounded-3 p-2 d-inline-flex align-items-center justify-content-center delete-country-btn"
                                   title="Delete Country">
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
