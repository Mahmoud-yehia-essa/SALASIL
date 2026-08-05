@forelse($users as $key => $user)
<tr>
    <td class="text-center fw-bold">{{ $key + 1 }}</td>
    <td class="text-center">
        <img src="{{ (!empty($user->photo) && file_exists(public_path('upload/user_images/'.$user->photo))) ? url('upload/user_images/'.$user->photo) : url('upload/no_image.jpg') }}" 
             class="rounded-circle border border-info shadow-sm media-zoomable cursor-pointer" 
             data-title="{{ $user->fname }} {{ $user->lname }} - Photo"
             style="width: 42px; height: 42px; object-fit: cover;" 
             alt="{{ $user->fname }} {{ $user->lname }}">
    </td>
    <td class="fw-bold fs-6 client-name-text">
        <div class="d-flex flex-column gap-1">
            <span class="fw-bold text-white">{{ $user->fname }} {{ $user->lname }}</span>
            @if($user->client_code)
                <div class="d-flex align-items-center gap-1">
                    <span class="badge bg-dark text-info border border-info border-opacity-40 px-2 py-1 rounded-2 font-monospace" style="width: max-content; font-size: 0.76rem; letter-spacing: 0.5px;" title="Client Unique Code">
                        <i class="bx bx-barcode me-1"></i>{{ $user->client_code }}
                    </span>
                    <!-- Interactive QR Code Trigger -->
                    <button type="button" 
                            class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-40 px-2 py-1 rounded-2 cursor-pointer btn-open-qr-modal border-0" 
                            data-client-code="{{ $user->client_code }}"
                            data-client-name="{{ $user->fname }} {{ $user->lname }}"
                            data-profile-url="{{ route('public.user.profile', $user->client_code) }}"
                            title="Scan & View Digital QR Profile">
                        <i class="bx bx-qr-scan fs-6"></i>
                    </button>
                </div>
            @endif
        </div>
    </td>
    <td>{{ $user->email ?? 'N/A' }}</td>
    <td>
        @if($user->phone)
            <div class="d-flex flex-column gap-1">
                <span class="text-info fw-semibold"><i class="bx bx-phone me-1"></i>{{ $user->country_code ?? '+966' }} {{ $user->phone }}</span>
                @if($user->secondary_phone)
                    <span class="text-slate-400 fs-7" title="Secondary Phone"><i class="bx bx-phone-call me-1"></i>{{ $user->secondary_phone }}</span>
                @endif
            </div>
        @else
            <span class="text-slate-400">N/A</span>
        @endif
    </td>
    <td>
        @if($user->role == 'company_customer')
            <span class="badge px-3 py-1 rounded-pill fw-semibold" style="background-color: rgba(6, 182, 212, 0.18); color: #38BDF8; border: 1px solid rgba(6, 182, 212, 0.4);">
                <i class="bx bx-building-house me-1"></i> Corporate Client
            </span>
        @elseif($user->role == 'individual_customer')
            <span class="badge px-3 py-1 rounded-pill fw-semibold" style="background-color: rgba(59, 130, 246, 0.18); color: #60A5FA; border: 1px solid rgba(59, 130, 246, 0.4);">
                <i class="bx bx-user me-1"></i> Individual Client
            </span>
        @elseif($user->role == 'driver')
            <span class="badge px-3 py-1 rounded-pill fw-semibold" style="background-color: rgba(245, 158, 11, 0.18); color: #F59E0B; border: 1px solid rgba(245, 158, 11, 0.4);">
                <i class="bx bx-car me-1"></i> Driver
            </span>
        @elseif($user->role == 'admin')
            <span class="badge px-3 py-1 rounded-pill fw-semibold" style="background-color: rgba(244, 63, 94, 0.18); color: #F43F5E; border: 1px solid rgba(244, 63, 94, 0.4);">
                <i class="bx bx-shield me-1"></i> System Admin
            </span>
        @else
            <span class="badge px-3 py-1 rounded-pill fw-semibold" style="background-color: rgba(148, 163, 184, 0.18); color: #CBD5E1; border: 1px solid rgba(148, 163, 184, 0.4);">
                <i class="bx bx-user me-1"></i> {{ ucfirst(str_replace('_', ' ', $user->role ?? 'Client')) }}
            </span>
        @endif
    </td>
    <!-- Dynamic Status Dropdown with Instant AJAX Update -->
    <td style="min-width: 165px;">
        <select class="form-select user-status-select status-badge-{{ $user->status }}" 
                data-user-id="{{ $user->id }}" 
                title="Change Client Status">
            <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Active</option>
            <option value="pending" {{ $user->status === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="inactive" {{ $user->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
            <option value="banned" {{ $user->status === 'banned' ? 'selected' : '' }}>Banned</option>
        </select>
    </td>
    <td>
        @if($user->created_at)
            <div class="d-flex flex-column gap-1">
                <span class="fw-semibold joined-date-text fs-7">
                    <i class="bx bx-calendar text-info me-1"></i> {{ $user->created_at->format('M d, Y') }}
                </span>
                <span class="text-slate-400" style="font-size: 0.78rem;">
                    <i class="bx bx-time-five me-1 text-info"></i> {{ $user->created_at->diffForHumans() }}
                </span>
            </div>
        @else
            <span class="text-slate-400">N/A</span>
        @endif
    </td>
    <td class="text-center" style="min-width: 100px;">
        <div class="d-flex align-items-center justify-content-center gap-2">
            <!-- Edit Client Profile Button -->
            <a href="{{ route('edit.user', $user->id) }}" 
               class="btn btn-sm rounded-3 p-2 d-inline-flex align-items-center justify-content-center text-white border-0 shadow-sm" 
               style="width: 36px; height: 36px; background-color: #06B6D4;"
               title="Edit Client Details">
                <i class="bx bx-edit fs-5"></i>
            </a>

            <!-- Delete Client Account Button -->
            <a href="{{ route('delete.user', $user->id) }}" 
               class="btn btn-sm rounded-3 p-2 d-inline-flex align-items-center justify-content-center text-white border-0 shadow-sm delete-client-btn" 
               style="width: 36px; height: 36px; background-color: #F43F5E;"
               title="Delete Client Account">
                <i class="bx bx-trash fs-5"></i>
            </a>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="9" class="text-center py-4 text-slate-400">
        <i class="bx bx-search-alt fs-1 text-slate-500 mb-2 d-block"></i>
        <span>No users or clients match the selected filter criteria.</span>
    </td>
</tr>
@endforelse
