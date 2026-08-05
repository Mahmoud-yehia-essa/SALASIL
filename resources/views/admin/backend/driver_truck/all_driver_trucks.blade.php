@extends('admin.master_admin')
@section('admin')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<style>
    /* ─── Guaranteed Horizontal Scroll for Driver Trucks Table ─── */
    .table-responsive {
        overflow-x: auto !important;
        overflow-y: hidden !important;
        -webkit-overflow-scrolling: touch !important;
        width: 100% !important;
        display: block !important;
        margin-bottom: 0.5rem;
    }
    #example {
        width: 100% !important;
        min-width: 1100px !important;
        margin-bottom: 0 !important;
    }
    #example th, 
    #example td {
        white-space: nowrap !important;
    }

    /* Custom Styling for Status Badges & Selects */
    select.driver-truck-verified-select, select.driver-truck-default-select {
        width: 155px !important;
        min-width: 155px !important;
        max-width: 155px !important;
        height: 38px !important;
        padding-left: 14px !important;
        padding-right: 28px !important;
        background-position: right 10px center !important;
        background-size: 10px 7px !important;
        border-radius: 50rem !important;
        font-weight: 700 !important;
        font-size: 0.83rem !important;
        line-height: 1.3 !important;
        cursor: pointer !important;
        display: inline-block !important;
        box-shadow: none !important;
        white-space: nowrap !important;
    }

    .verified-badge-1 {
        background-color: rgba(34, 197, 94, 0.18) !important;
        color: #22C55E !important;
        border: 1px solid rgba(34, 197, 94, 0.4) !important;
    }

    .verified-badge-0 {
        background-color: rgba(245, 158, 11, 0.18) !important;
        color: #F59E0B !important;
        border: 1px solid rgba(245, 158, 11, 0.4) !important;
    }

    .default-badge-1 {
        background-color: rgba(6, 182, 212, 0.18) !important;
        color: #06B6D4 !important;
        border: 1px solid rgba(6, 182, 212, 0.4) !important;
    }

    .default-badge-0 {
        background-color: rgba(148, 163, 184, 0.18) !important;
        color: #94A3B8 !important;
        border: 1px solid rgba(148, 163, 184, 0.4) !important;
    }

    select option {
        background-color: #1E293B !important;
        color: #F8FAFC !important;
        font-weight: 600;
        padding: 8px;
    }

    /* Filter Card Glassmorphism */
    .filter-card {
        background: rgba(30, 41, 59, 0.6) !important;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
    }
</style>

<!-- Page Header -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold header-title mb-1">
            <i class="bx bx-link-alt text-info me-2"></i>Driver & Truck Fleet Assignments
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Manage fleet vehicles assigned to drivers, verification status, and active default trucks.
        </p>
    </div>
    <div>
        <a href="{{ route('add.driver.truck') }}" class="btn btn-primary rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-plus-circle fs-5"></i>
            <span>Assign Truck to Driver</span>
        </a>
    </div>
</div>

<!-- Interactive AJAX Filter Card -->
<div class="card filter-card border-0 shadow-lg rounded-4 mb-4">
    <div class="card-body p-3 p-md-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="card-title mb-0 fw-bold text-info d-flex align-items-center gap-2">
                <i class="bx bx-filter-alt fs-5"></i>
                <span>Interactive Fleet Filters</span>
            </h6>
            <button type="button" id="btn_reset_filters" class="btn btn-sm btn-outline-secondary rounded-3 px-3 fw-semibold d-flex align-items-center gap-1">
                <i class="bx bx-refresh fs-6"></i> Reset Filters
            </button>
        </div>

        <div class="row g-3">
            <!-- 1. Filter by Driver -->
            <div class="col-12 col-sm-6 col-lg-3">
                <label for="filter_driver_id" class="form-label fw-semibold text-slate-300 fs-7 mb-1">Filter by Driver</label>
                <div class="position-relative">
                    <select id="filter_driver_id" class="form-select driver-truck-filter rounded-3">
                        <option value="">All Drivers (الكل)</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}">
                                {{ $driver->fname }} {{ $driver->lname }} ({{ $driver->phone ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- 2. Filter by Main Truck Type -->
            <div class="col-12 col-sm-6 col-lg-3">
                <label for="filter_truck_type_id" class="form-label fw-semibold text-slate-300 fs-7 mb-1">Filter Main Truck Type</label>
                <div class="position-relative">
                    <select id="filter_truck_type_id" class="form-select driver-truck-filter rounded-3">
                        <option value="">All Truck Types (الكل)</option>
                        @foreach($truckTypes as $type)
                            <option value="{{ $type->id }}">
                                {{ $type->name_en }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- 3. Filter by Truck Sub-Type -->
            <div class="col-12 col-sm-6 col-lg-3">
                <label for="filter_truck_sub_type_id" class="form-label fw-semibold text-slate-300 fs-7 mb-1">Filter Truck Sub-Type</label>
                <div class="position-relative">
                    <select id="filter_truck_sub_type_id" class="form-select rounded-3">
                        <option value="">All Sub-Types (الكل)</option>
                        <option value="none">General (No Sub-Type / بدون تصنيف فرعي)</option>
                        @foreach($truckSubTypes as $sub)
                            <option value="{{ $sub->id }}" data-parent-type="{{ $sub->truck_type_id }}">
                                {{ $sub->name_en }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- 4. Filter by Verification Status -->
            <div class="col-12 col-sm-6 col-lg-3">
                <label for="filter_is_verified" class="form-label fw-semibold text-slate-300 fs-7 mb-1">Verification Status</label>
                <div class="position-relative">
                    <select id="filter_is_verified" class="form-select rounded-3">
                        <option value="all">All Verification Statuses</option>
                        <option value="1">Verified Only (موثقة)</option>
                        <option value="0">Unverified Only (غير موثقة)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-lg rounded-4 overflow-hidden">
    <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3 px-4 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
            <i class="bx bx-table text-info fs-4"></i>
            <span>Assigned Driver Trucks Records</span>
        </h5>
        <span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-25 px-3 py-1 fw-semibold fs-6">
            Total Records: <span id="total_records_count">{{ count($driverTrucks) }}</span>
        </span>
    </div>

    <div class="card-body p-4">
        <div class="table-responsive">
            <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Driver Details</th>
                        <th>Truck Type (Main)</th>
                        <th>Truck Sub-Type (Optional)</th>
                        <th>Plate Number</th>
                        <th style="width: 165px; min-width: 165px;">Default Truck</th>
                        <th style="width: 165px; min-width: 165px;">Verification</th>
                        <th class="text-center" style="width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody id="driver_trucks_tbody">
                    @include('admin.backend.driver_truck.partials.table_rows')
                </tbody>
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        var csrfToken = $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}";
        var filterUrl = "{{ route('filter.driver.trucks.ajax') }}";

        // Store original sub-type options HTML for resetting
        var $subTypeSelect = $('#filter_truck_sub_type_id');
        var originalSubTypeOptions = $subTypeSelect.html();

        // Main Truck Type Filter change -> Dynamically update Sub-Type options via AJAX
        $('#filter_truck_type_id').on('change', function() {
            var selectedTypeId = $(this).val();
            $subTypeSelect.val(''); // Reset sub-type value first to prevent race condition

            if (selectedTypeId) {
                $.ajax({
                    url: "{{ url('/admin/driver-truck/get-sub-types') }}/" + selectedTypeId,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $subTypeSelect.empty();
                        $subTypeSelect.append('<option value="">All Sub-Types (الكل)</option>');
                        $subTypeSelect.append('<option value="none">General (No Sub-Type / بدون تصنيف فرعي)</option>');
                        if (data && data.length > 0) {
                            $.each(data, function(key, value) {
                                $subTypeSelect.append('<option value="' + value.id + '">' + value.name_en + '</option>');
                            });
                        }
                        fetchFilteredDriverTrucks();
                    }
                });
            } else {
                $subTypeSelect.html(originalSubTypeOptions).val('');
                fetchFilteredDriverTrucks();
            }
        });

        // Trigger AJAX filtering when other filter dropdowns change
        $(document).on('change', '#filter_driver_id, #filter_truck_sub_type_id, #filter_is_verified', function() {
            fetchFilteredDriverTrucks();
        });

        // Reset Filters Button
        $('#btn_reset_filters').on('click', function(e) {
            e.preventDefault();
            $('#filter_driver_id').val('');
            $('#filter_truck_type_id').val('');
            $subTypeSelect.html(originalSubTypeOptions).val('');
            $('#filter_is_verified').val('all');

            fetchFilteredDriverTrucks();
        });

        // AJAX Fetcher Function
        function fetchFilteredDriverTrucks() {
            var driverId = $('#filter_driver_id').val();
            var truckTypeId = $('#filter_truck_type_id').val();
            var truckSubTypeId = $('#filter_truck_sub_type_id').val();
            var isVerified = $('#filter_is_verified').val();

            // Add loading opacity effect to tbody
            $('#driver_trucks_tbody').css('opacity', '0.4');

            $.ajax({
                url: filterUrl,
                type: "POST",
                data: {
                    _token: csrfToken,
                    driver_id: driverId,
                    truck_type_id: truckTypeId,
                    truck_sub_type_id: truckSubTypeId,
                    is_verified: isVerified
                },
                dataType: "json",
                success: function(response) {
                    $('#driver_trucks_tbody').css('opacity', '1');

                    if (response.status === 'success') {
                        // Destroy DataTable before updating DOM
                        if ($.fn.DataTable.isDataTable('#example')) {
                            $('#example').DataTable().clear().destroy();
                        }

                        // Replace tbody content and update count
                        $('#driver_trucks_tbody').html(response.html);
                        $('#total_records_count').text(response.count);

                        // Re-initialize DataTable
                        if (response.count > 0 && $.fn.DataTable) {
                            $('#example').DataTable({
                                autoWidth: false,
                                language: {
                                    search: "Search in loaded results:",
                                    lengthMenu: "Display _MENU_ entries",
                                    info: "Showing _START_ to _END_ of _TOTAL_ entries"
                                }
                            });
                        }
                    }
                },
                error: function() {
                    $('#driver_trucks_tbody').css('opacity', '1');
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Failed to filter records.');
                    }
                }
            });
        }
    });
</script>

@endsection
