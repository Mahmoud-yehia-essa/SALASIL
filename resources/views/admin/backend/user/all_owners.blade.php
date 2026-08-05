@extends('admin.master_admin')
@section('admin')

<style>
    /* Custom Styling for Status Dropdown Select Badges */
    select.user-status-select {
        width: 155px !important;
        min-width: 155px !important;
        max-width: 155px !important;
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

    .user-status-select.status-badge-active {
        background-color: rgba(34, 197, 94, 0.18) !important;
        color: #22C55E !important;
        border: 1px solid rgba(34, 197, 94, 0.4) !important;
    }

    .user-status-select.status-badge-pending {
        background-color: rgba(245, 158, 11, 0.18) !important;
        color: #F59E0B !important;
        border: 1px solid rgba(245, 158, 11, 0.4) !important;
    }

    .user-status-select.status-badge-inactive {
        background-color: rgba(148, 163, 184, 0.18) !important;
        color: #94A3B8 !important;
        border: 1px solid rgba(148, 163, 184, 0.4) !important;
    }

    .user-status-select.status-badge-banned {
        background-color: rgba(244, 63, 94, 0.18) !important;
        color: #F43F5E !important;
        border: 1px solid rgba(244, 63, 94, 0.4) !important;
    }

    .user-status-select option {
        background-color: #1E293B !important;
        color: #F8FAFC !important;
        font-weight: 600;
        padding: 8px;
    }

    html.light-theme .user-status-select option {
        background-color: #FFFFFF !important;
        color: #0F172A !important;
    }

    /* ─── Guaranteed Horizontal Scroll for Clients Table ─── */
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
</style>

<!-- Page Header -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold header-title mb-1">
            <i class="bx bx-user-check text-info me-2"></i>Registered Clients List
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Manage and view all registered individual and corporate clients on the SALASIL platform.
        </p>
    </div>
    <div>
        <a href="{{ route('add.user') }}" class="btn btn-primary rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-user-plus fs-5"></i>
            <span>Add New Client</span>
        </a>
    </div>
</div>

<!-- Interactive AJAX Filter Card -->
<div class="card border-0 shadow-lg rounded-4 mb-4" style="background: rgba(30, 41, 59, 0.6) !important; backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.08) !important;">
    <div class="card-body p-3 p-md-4">
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <h6 class="card-title mb-0 fw-bold text-info d-flex align-items-center gap-2">
                <i class="bx bx-filter-alt fs-5"></i>
                <span>Interactive Filters</span>
            </h6>
            <button type="button" id="btn_reset_filters" class="btn btn-sm btn-outline-secondary rounded-3 px-3 fw-semibold d-flex align-items-center gap-1">
                <i class="bx bx-refresh fs-6"></i> Reset Filters
            </button>
        </div>

        <div class="row g-3 align-items-end">
            <!-- 1. Filter by Role -->
            <div class="col-12 col-sm-6 col-lg-4">
                <label for="filter_role" class="form-label fw-semibold text-slate-300 fs-7 mb-1">User Role</label>
                <div class="position-relative">
                    <select id="filter_role" class="form-select user-filter-control rounded-3">
                        <option value="">All Roles</option>
                        <option value="company_customer" {{ request('role') == 'company_customer' ? 'selected' : '' }}>Corporate Client</option>
                        <option value="individual_customer" {{ request('role') == 'individual_customer' ? 'selected' : '' }}>Individual Client</option>
                        <option value="driver" {{ request('role') == 'driver' ? 'selected' : '' }}>Driver</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>System Admin</option>
                    </select>
                </div>
            </div>

            <!-- 2. Filter by Status -->
            <div class="col-12 col-sm-6 col-lg-4">
                <label for="filter_status" class="form-label fw-semibold text-slate-300 fs-7 mb-1">Account Status</label>
                <div class="position-relative">
                    <select id="filter_status" class="form-select user-filter-control rounded-3">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="banned" {{ request('status') == 'banned' ? 'selected' : '' }}>Banned</option>
                    </select>
                </div>
            </div>

            <!-- 3. Search Keyword Input -->
            <div class="col-12 col-sm-6 col-lg-4">
                <label for="filter_search" class="form-label fw-semibold text-slate-300 fs-7 mb-1">Search Keywords</label>
                <div class="input-group">
                    <input type="text" id="filter_search" class="form-control rounded-start-3" placeholder="Code (DR-30404), name, email, phone..." value="{{ request('search') }}">
                    <button type="button" id="btn_search_submit" class="btn btn-info px-3 fw-semibold rounded-end-3 d-inline-flex align-items-center gap-1">
                        <i class="bx bx-search fs-5"></i>
                        <span>Search</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-lg rounded-4 overflow-hidden">
    <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
            <i class="bx bx-table text-info fs-4"></i>
            <span>Clients Database Records</span>
        </h5>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-25 px-3 py-1 fw-semibold fs-6">
                Total Records: <span id="total_records_count">{{ count($users) }}</span>
            </span>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="table-responsive">
            <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th style="width: 60px;">Photo</th>
                        <th>Client Name</th>
                        <th>Email Address</th>
                        <th>Phone</th>
                        <th>Account Role</th>
                        <th style="width: 165px;">Status</th>
                        <th>Joined Date</th>
                        <th class="text-center" style="width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody id="users_tbody">
                    @include('admin.backend.user.user_table_rows', ['users' => $users])
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Interactive QR Code Modal -->
<div class="modal fade" id="qrCodeModal" tabindex="-1" aria-labelledby="qrCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background: #141E47; border: 1px solid rgba(78, 205, 196, 0.35) !important;">
            <div class="modal-header border-bottom border-secondary border-opacity-25 py-3 px-4">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="qrCodeModalLabel">
                    <i class="bx bx-qr-scan text-info fs-4"></i>
                    <span>Digital Verified Profile QR Code</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <h5 class="fw-bold text-white mb-1" id="qrModalClientName">Client Profile</h5>
                <div class="mb-3">
                    <span class="badge bg-dark text-info border border-info border-opacity-40 px-3 py-1.5 rounded-pill font-monospace fs-7" id="qrModalClientCode">
                        CODE-000
                    </span>
                </div>

                <div class="p-3 bg-white rounded-4 d-inline-block shadow-lg mb-3">
                    <img src="" id="qrModalImg" alt="QR Code" style="width: 220px; height: 220px; display: block;" />
                </div>

                <p class="text-slate-300 fs-7 mb-3">
                    Scan with any smartphone camera to view the live public verification profile.
                </p>

                <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
                    <a href="#" id="qrModalProfileLink" target="_blank" class="btn btn-info rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1 fs-7">
                        <i class="bx bx-external-link fs-5"></i> Open Profile Page
                    </a>
                    <button type="button" id="qrModalCopyLinkBtn" class="btn btn-outline-light rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1 fs-7">
                        <i class="bx bx-copy fs-5"></i> Copy Profile Link
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document.body).ready(function() {
        var filterUrl = "{{ route('filter.users.ajax') }}";
        var csrfToken = "{{ csrf_token() }}";

        // QR Code Modal Trigger
        $(document).on('click', '.btn-open-qr-modal', function() {
            var code = $(this).data('client-code');
            var name = $(this).data('client-name');
            var url  = $(this).data('profile-url');

            $('#qrModalClientName').text(name);
            $('#qrModalClientCode').text(code);
            $('#qrModalProfileLink').attr('href', url);
            $('#qrModalImg').attr('src', 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' + encodeURIComponent(url));
            
            $('#qrModalCopyLinkBtn').off('click').on('click', function() {
                navigator.clipboard.writeText(url).then(function() {
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Public profile URL copied to clipboard!');
                    } else {
                        alert('Public profile link copied to clipboard!');
                    }
                });
            });

            var modal = new bootstrap.Modal(document.getElementById('qrCodeModal'));
            modal.show();
        });

        // Listen for changes on filter selects
        $('#filter_role, #filter_status').on('change', function() {
            fetchFilteredUsers();
        });

        // Listen for search button click & Enter key press
        $('#btn_search_submit').on('click', function() {
            fetchFilteredUsers();
        });
        $('#filter_search').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                fetchFilteredUsers();
            }
        });

        // Reset filters button
        $('#btn_reset_filters').on('click', function() {
            $('#filter_role').val('');
            $('#filter_status').val('');
            $('#filter_search').val('');
            fetchFilteredUsers();
        });

        function fetchFilteredUsers() {
            var role = $('#filter_role').val();
            var status = $('#filter_status').val();
            var search = $('#filter_search').val();

            // Opacity effect while loading AJAX
            $('#users_tbody').css('opacity', '0.4');

            $.ajax({
                url: filterUrl,
                type: "POST",
                data: {
                    _token: csrfToken,
                    role: role,
                    status: status,
                    search: search
                },
                dataType: "json",
                success: function(response) {
                    $('#users_tbody').css('opacity', '1');

                    if (response.status === 'success') {
                        // Destroy DataTable before DOM update if initialized
                        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#example')) {
                            $('#example').DataTable().clear().destroy();
                        }

                        // Replace tbody HTML & update total count
                        $('#users_tbody').html(response.html);
                        $('#total_records_count').text(response.count);

                        // Re-initialize DataTable ONLY when there are active records (count > 0)
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
                    $('#users_tbody').css('opacity', '1');
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Failed to filter users data.');
                    }
                }
            });
        }
    });
</script>

@endsection
