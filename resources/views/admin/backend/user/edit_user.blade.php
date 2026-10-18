@extends('admin.master_admin')
@section('admin')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<style>
/* ════════════════════════════════════════════════════════════════════
   COMPANY DOCUMENTS REQUIREMENTS & UPLOADED SECTION (LIGHT & DARK THEME)
   ════════════════════════════════════════════════════════════════════ */

/* Light Theme Variables */
html.light-theme, html:not(.dark-theme) {
    --doc-box-bg: #F8FAFC;
    --doc-box-border: #E2E8F0;
    --doc-box-title: #0F172A;
    --doc-box-desc: #475569;
    --doc-toolbar-bg: #FFFFFF;
    --doc-toolbar-border: #E2E8F0;
    --doc-card-bg: #FFFFFF;
    --doc-card-border: #E2E8F0;
    --doc-card-title: #0F172A;
    --doc-card-sub: #64748B;
    --doc-card-sel-bg: #F0F9FF;
    --doc-card-sel-border: #0284C7;
    --doc-card-sel-title: #0369A1;
    --doc-badge-default-req-bg: #FEF2F2;
    --doc-badge-default-req-text: #DC2626;
    --doc-badge-default-req-border: #FECACA;
    --doc-badge-default-opt-bg: #F1F5F9;
    --doc-badge-default-opt-text: #475569;
    --doc-badge-default-opt-border: #CBD5E1;
    --doc-badge-approved-bg: #F0FDF4;
    --doc-badge-approved-text: #16A34A;
    --doc-badge-approved-border: #BBF7D0;
    --doc-badge-pending-bg: #FFFBEB;
    --doc-badge-pending-text: #D97706;
    --doc-badge-pending-border: #FDE68A;
    --doc-badge-rejected-bg: #FEF2F2;
    --doc-badge-rejected-text: #DC2626;
    --doc-badge-rejected-border: #FECACA;
    --doc-badge-notuploaded-bg: #FEF2F2;
    --doc-badge-notuploaded-text: #B91C1C;
    --doc-badge-notuploaded-border: #FCA5A5;
    --doc-badge-custom-bg: #FEF3C7;
    --doc-badge-custom-text: #B45309;
    --doc-badge-custom-border: #FCD34D;
    --doc-thumb-box-bg: #F8FAFC;
    --doc-thumb-box-border: #E2E8F0;
    --doc-btn-request-bg: #F0F9FF;
    --doc-btn-request-text: #0284C7;
    --doc-btn-request-border: #BAE6FD;
    --doc-modal-content-bg: #FFFFFF;
    --doc-modal-text: #0F172A;
    --doc-modal-sub: #475569;
    --doc-modal-border: #E2E8F0;
    --doc-modal-box-bg: #F8FAFC;
}

/* Dark Theme Variables */
html.dark-theme {
    --doc-box-bg: rgba(15, 23, 42, 0.65);
    --doc-box-border: rgba(56, 189, 248, 0.25);
    --doc-box-title: #38BDF8;
    --doc-box-desc: #94A3B8;
    --doc-toolbar-bg: rgba(15, 23, 42, 0.85);
    --doc-toolbar-border: rgba(255, 255, 255, 0.1);
    --doc-card-bg: rgba(30, 41, 59, 0.65);
    --doc-card-border: rgba(255, 255, 255, 0.1);
    --doc-card-title: #F8FAFC;
    --doc-card-sub: #94A3B8;
    --doc-card-sel-bg: rgba(14, 165, 233, 0.15);
    --doc-card-sel-border: rgba(56, 189, 248, 0.6);
    --doc-card-sel-title: #38BDF8;
    --doc-badge-default-req-bg: rgba(239, 68, 68, 0.2);
    --doc-badge-default-req-text: #FCA5A5;
    --doc-badge-default-req-border: rgba(239, 68, 68, 0.3);
    --doc-badge-default-opt-bg: rgba(148, 163, 184, 0.15);
    --doc-badge-default-opt-text: #CBD5E1;
    --doc-badge-default-opt-border: rgba(148, 163, 184, 0.25);
    --doc-badge-approved-bg: rgba(34, 197, 94, 0.2);
    --doc-badge-approved-text: #86EFAC;
    --doc-badge-approved-border: rgba(34, 197, 94, 0.3);
    --doc-badge-pending-bg: rgba(245, 158, 11, 0.2);
    --doc-badge-pending-text: #FDE047;
    --doc-badge-pending-border: rgba(245, 158, 11, 0.3);
    --doc-badge-rejected-bg: rgba(239, 68, 68, 0.2);
    --doc-badge-rejected-text: #FCA5A5;
    --doc-badge-rejected-border: rgba(239, 68, 68, 0.3);
    --doc-badge-notuploaded-bg: rgba(239, 68, 68, 0.15);
    --doc-badge-notuploaded-text: #F87171;
    --doc-badge-notuploaded-border: rgba(239, 68, 68, 0.25);
    --doc-badge-custom-bg: rgba(245, 158, 11, 0.2);
    --doc-badge-custom-text: #FCD34D;
    --doc-badge-custom-border: rgba(245, 158, 11, 0.3);
    --doc-thumb-box-bg: rgba(15, 23, 42, 0.8);
    --doc-thumb-box-border: rgba(255, 255, 255, 0.1);
    --doc-btn-request-bg: rgba(56, 189, 248, 0.15);
    --doc-btn-request-text: #38BDF8;
    --doc-btn-request-border: rgba(56, 189, 248, 0.4);
    --doc-modal-content-bg: #1e293b;
    --doc-modal-text: #f8fafc;
    --doc-modal-sub: #94a3b8;
    --doc-modal-border: rgba(255, 255, 255, 0.1);
    --doc-modal-box-bg: rgba(15, 23, 42, 0.7);
}

/* Container Panels */
.doc-requirements-box, .uploaded-docs-box {
    background-color: var(--doc-box-bg) !important;
    border: 1px solid var(--doc-box-border) !important;
    border-radius: 1rem !important;
}

.doc-requirements-box .section-header-title,
.uploaded-docs-box .section-header-title {
    color: var(--doc-box-title) !important;
    font-weight: 700 !important;
}

.doc-requirements-box .section-header-desc,
.uploaded-docs-box .section-header-desc {
    color: var(--doc-box-desc) !important;
}

/* Toolbar */
.doc-req-toolbar {
    background-color: var(--doc-toolbar-bg) !important;
    border: 1px solid var(--doc-toolbar-border) !important;
}

html.light-theme .doc-req-toolbar .btn-outline-secondary,
html:not(.dark-theme) .doc-req-toolbar .btn-outline-secondary {
    background-color: #FFFFFF !important;
    border-color: #CBD5E1 !important;
    color: #334155 !important;
}
html.light-theme .doc-req-toolbar .btn-outline-secondary:hover,
html:not(.dark-theme) .doc-req-toolbar .btn-outline-secondary:hover {
    background-color: #F1F5F9 !important;
    color: #0F172A !important;
}

html.light-theme .doc-req-toolbar .btn-outline-info,
html:not(.dark-theme) .doc-req-toolbar .btn-outline-info {
    background-color: #F0F9FF !important;
    border-color: #0284C7 !important;
    color: #0369A1 !important;
}

html.light-theme .doc-req-toolbar .btn-outline-warning,
html:not(.dark-theme) .doc-req-toolbar .btn-outline-warning {
    background-color: #FFFBEB !important;
    border-color: #F59E0B !important;
    color: #B45309 !important;
}

/* Document Cards */
.req-doc-card {
    background-color: var(--doc-card-bg) !important;
    border: 1px solid var(--doc-card-border) !important;
    transition: all 0.2s ease-in-out;
}

.req-doc-card .doc-card-title {
    color: var(--doc-card-title) !important;
}

.req-doc-card .doc-card-sub {
    color: var(--doc-card-sub) !important;
}

/* Selected State */
.req-doc-card.is-selected {
    background-color: var(--doc-card-sel-bg) !important;
    border: 1.5px solid var(--doc-card-sel-border) !important;
    box-shadow: 0 2px 8px rgba(2, 132, 199, 0.08) !important;
}

.req-doc-card.is-selected .doc-card-title {
    color: var(--doc-card-sel-title) !important;
}

/* Semantic Badges */
.badge-def-req {
    background-color: var(--doc-badge-default-req-bg) !important;
    color: var(--doc-badge-default-req-text) !important;
    border: 1px solid var(--doc-badge-default-req-border) !important;
}

.badge-def-opt {
    background-color: var(--doc-badge-default-opt-bg) !important;
    color: var(--doc-badge-default-opt-text) !important;
    border: 1px solid var(--doc-badge-default-opt-border) !important;
}

.badge-st-approved {
    background-color: var(--doc-badge-approved-bg) !important;
    color: var(--doc-badge-approved-text) !important;
    border: 1px solid var(--doc-badge-approved-border) !important;
}

.badge-st-pending {
    background-color: var(--doc-badge-pending-bg) !important;
    color: var(--doc-badge-pending-text) !important;
    border: 1px solid var(--doc-badge-pending-border) !important;
}

.badge-st-rejected {
    background-color: var(--doc-badge-rejected-bg) !important;
    color: var(--doc-badge-rejected-text) !important;
    border: 1px solid var(--doc-badge-rejected-border) !important;
}

.badge-st-notuploaded {
    background-color: var(--doc-badge-notuploaded-bg) !important;
    color: var(--doc-badge-notuploaded-text) !important;
    border: 1px solid var(--doc-badge-notuploaded-border) !important;
}

.badge-st-custom {
    background-color: var(--doc-badge-custom-bg) !important;
    color: var(--doc-badge-custom-text) !important;
    border: 1px solid var(--doc-badge-custom-border) !important;
}

/* Buttons inside cards */
.single-doc-request-btn {
    background-color: var(--doc-btn-request-bg) !important;
    color: var(--doc-btn-request-text) !important;
    border: 1px solid var(--doc-btn-request-border) !important;
}

.single-doc-request-btn:hover {
    filter: brightness(0.95);
}

/* Uploaded Document Cards */
.uploaded-doc-item-card {
    background-color: var(--doc-card-bg) !important;
    border: 1px solid var(--doc-card-border) !important;
}

.uploaded-doc-item-card .doc-title {
    color: var(--doc-card-title) !important;
}

.uploaded-doc-item-card .doc-sub {
    color: var(--doc-card-sub) !important;
}

.uploaded-doc-item-card .thumb-details-box {
    background-color: var(--doc-thumb-box-bg) !important;
    border: 1px solid var(--doc-thumb-box-border) !important;
}

.doc-empty-box {
    background-color: var(--doc-toolbar-bg) !important;
    border: 1px dashed var(--doc-box-border) !important;
}
.doc-empty-box h6 {
    color: var(--doc-box-title) !important;
}
.doc-empty-box p {
    color: var(--doc-box-desc) !important;
}

/* Modal theme styling */
.doc-themed-modal .modal-content {
    background-color: var(--doc-modal-content-bg) !important;
    color: var(--doc-modal-text) !important;
    border: 1px solid var(--doc-modal-border) !important;
}

.doc-themed-modal .modal-header,
.doc-themed-modal .modal-footer {
    border-color: var(--doc-modal-border) !important;
}

.doc-themed-modal .modal-body p {
    color: var(--doc-modal-sub) !important;
}

.doc-themed-modal .modal-switches-box {
    background-color: var(--doc-modal-box-bg) !important;
    border: 1px solid var(--doc-modal-border) !important;
}

html.light-theme .doc-themed-modal .btn-close,
html:not(.dark-theme) .doc-themed-modal .btn-close {
    filter: none !important;
}

html.light-theme .doc-themed-modal .form-control,
html:not(.dark-theme) .doc-themed-modal .form-control {
    background-color: #FFFFFF !important;
    border-color: #CBD5E1 !important;
    color: #0F172A !important;
}
</style>

<!-- Page Header -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold mb-1 header-title">
            <i class="bx bx-edit text-info me-2"></i>Edit Client Profile
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Update information and account credentials for client: <span class="text-info fw-bold">{{ $user->fname }} {{ $user->lname }}</span>
        </p>
    </div>
    <div>
        <a href="{{ route('all.owners') }}" class="btn btn-outline-info rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-list-ul fs-5"></i>
            <span>Back to All Clients</span>
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3 px-4">
                <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="bx bx-id-card text-info fs-4"></i>
                    <span>Edit Client Details</span>
                </h5>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ route('update.user') }}" enctype="multipart/form-data" id="editForm">
                    @csrf
                    <input type="hidden" name="id" value="{{ $user->id }}">

                    <!-- Personal Information Section -->
                    <div class="mb-4">
                        <h6 class="text-info fw-bold text-uppercase fs-7 mb-3" style="letter-spacing: 0.5px;">
                            <i class="bx bx-user me-1"></i> Personal Information
                        </h6>
                        <div class="row g-3">
                            <!-- First Name -->
                            <div class="col-12 col-md-6">
                                <label for="fname" class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="text" 
                                           name="fname" 
                                           id="fname" 
                                           class="form-control @error('fname') is-invalid @enderror" 
                                           style="padding-left: 48px !important;"
                                           placeholder="e.g. Mahmoud"
                                           value="{{ old('fname', $user->fname) }}"
                                           required>
                                    <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-user fs-5"></i>
                                    </span>
                                </div>
                                @error('fname')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Last Name -->
                            <div class="col-12 col-md-6">
                                <label for="lname" class="form-label fw-semibold">Last Name</label>
                                <div class="position-relative">
                                    <input type="text" 
                                           name="lname" 
                                           id="lname" 
                                           class="form-control" 
                                           style="padding-left: 48px !important;"
                                           placeholder="e.g. Essa"
                                           value="{{ old('lname', $user->lname) }}">
                                    <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-user fs-5"></i>
                                    </span>
                                </div>
                            </div>

                            <!-- Date of Birth -->
                            <div class="col-12 col-md-6">
                                <label for="dateofbirth" class="form-label fw-semibold">Date of Birth</label>
                                <div class="position-relative">
                                    <input type="date" 
                                           name="dateofbirth" 
                                           id="dateofbirth" 
                                           class="form-control cursor-pointer" 
                                           style="padding-left: 48px !important;"
                                           value="{{ old('dateofbirth', $user->dateofbirth ? $user->dateofbirth->format('Y-m-d') : '') }}"
                                           onclick="try{this.showPicker()}catch(e){}">
                                    <span class="position-absolute text-info cursor-pointer" style="left: 14px; top: 50%; transform: translateY(-50%); z-index: 5;" onclick="try{document.getElementById('dateofbirth').showPicker()}catch(e){}">
                                        <i class="bx bx-calendar fs-5"></i>
                                    </span>
                                </div>
                            </div>

                            <!-- Locale Preference -->
                            <div class="col-12 col-md-6">
                                <label for="locale" class="form-label fw-semibold">Preferred Language</label>
                                <select name="locale" id="locale" class="form-select">
                                    <option value="en" {{ old('locale', $user->locale) == 'en' ? 'selected' : '' }}>English (en)</option>
                                    <option value="ar" {{ old('locale', $user->locale) == 'ar' ? 'selected' : '' }}>Arabic (ar)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr class="border-secondary opacity-25 my-4">

                    <!-- Contact & Location Section -->
                    <div class="mb-4">
                        <h6 class="text-info fw-bold text-uppercase fs-7 mb-3" style="letter-spacing: 0.5px;">
                            <i class="bx bx-envelope me-1"></i> Contact & Location
                        </h6>
                        <div class="row g-3">
                            <!-- Email Address -->
                            <div class="col-12 col-md-6">
                                <label for="email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="email" 
                                           name="email" 
                                           id="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           style="padding-left: 48px !important;"
                                           placeholder="mahmoud@example.com"
                                           value="{{ old('email', $user->email) }}"
                                           required>
                                    <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-envelope fs-5"></i>
                                    </span>
                                </div>
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Phone Number & Country Code -->
                            <div class="col-12 col-md-6">
                                <label for="phone" class="form-label fw-semibold">Primary Phone Number</label>
                                <div class="input-group">
                                    <select name="country_code" class="form-select" style="max-width: 130px; padding-right: 1.8rem !important; padding-left: 0.6rem !important;">
                                        <option value="+966" {{ old('country_code', $user->country_code ?? '+966') == '+966' ? 'selected' : '' }}>+966 (KSA)</option>
                                        <option value="+971" {{ old('country_code', $user->country_code) == '+971' ? 'selected' : '' }}>+971 (UAE)</option>
                                        <option value="+965" {{ old('country_code', $user->country_code) == '+965' ? 'selected' : '' }}>+965 (KWT)</option>
                                        <option value="+968" {{ old('country_code', $user->country_code) == '+968' ? 'selected' : '' }}>+968 (OMN)</option>
                                        <option value="+973" {{ old('country_code', $user->country_code) == '+973' ? 'selected' : '' }}>+973 (BHR)</option>
                                        <option value="+962" {{ old('country_code', $user->country_code) == '+962' ? 'selected' : '' }}>+962 (JOR)</option>
                                        <option value="+20" {{ old('country_code', $user->country_code) == '+20' ? 'selected' : '' }}>+20 (EGY)</option>
                                    </select>
                                    <input type="text" 
                                           name="phone" 
                                           id="phone" 
                                           class="form-control" 
                                           placeholder="500000000"
                                           value="{{ old('phone', $user->phone) }}">
                                </div>
                            </div>

                            <!-- Secondary Phone Number -->
                            <div class="col-12 col-md-6">
                                <label for="secondary_phone" class="form-label fw-semibold">Secondary Phone (رقم هاتف إضافي)</label>
                                <div class="position-relative">
                                    <input type="text" 
                                           name="secondary_phone" 
                                           id="secondary_phone" 
                                           class="form-control" 
                                           style="padding-left: 48px !important;"
                                           placeholder="e.g. +966550000000"
                                           value="{{ old('secondary_phone', $user->secondary_phone) }}">
                                    <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-phone-call fs-5"></i>
                                    </span>
                                </div>
                            </div>

                            <!-- Country Selection (Optional) -->
                            <div class="col-12 col-md-6">
                                <label for="country_id" class="form-label fw-semibold">Country (الدولة) <span class="text-slate-400 font-normal fs-8">(Optional / اختياري)</span></label>
                                <div class="position-relative">
                                    <select name="country_id" id="country_id" class="form-select @error('country_id') is-invalid @enderror">
                                        <option value="">Select Country (اختر الدولة)</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->id }}" {{ old('country_id', $user->country_id) == $country->id ? 'selected' : '' }}>
                                                {{ $country->name_en }} ({{ $country->name_ar }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('country_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- City Selection (Optional) -->
                            <div class="col-12 col-md-6">
                                <label for="city_id" class="form-label fw-semibold">City (المدينة) <span class="text-slate-400 font-normal fs-8">(Optional / اختياري)</span></label>
                                <div class="position-relative">
                                    <select name="city_id" id="city_id" class="form-select @error('city_id') is-invalid @enderror">
                                        <option value="">Select City (اختر المدينة)</option>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->id }}" {{ old('city_id', $user->city_id) == $city->id ? 'selected' : '' }}>
                                                {{ $city->name_en }} ({{ $city->name_ar }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('city_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Full Address -->
                            <div class="col-12">
                                <label for="address" class="form-label fw-semibold">Full Address / Location</label>
                                <textarea name="address" 
                                          id="address" 
                                          rows="2" 
                                          class="form-control" 
                                          placeholder="Enter city, district, street address...">{{ old('address', $user->address) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <hr class="border-secondary opacity-25 my-4">

                    <!-- Credentials & Role Section -->
                    <div class="mb-4">
                        <h6 class="text-info fw-bold text-uppercase fs-7 mb-3" style="letter-spacing: 0.5px;">
                            <i class="bx bx-shield-quarter me-1"></i> Account Role & Access Password
                        </h6>
                        <div class="row g-3">
                            <!-- Account Role -->
                            <div class="col-12 col-md-4">
                                <label for="role" class="form-label fw-semibold">Account Role <span class="text-danger">*</span></label>
                                <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                                    <option value="individual_customer" {{ old('role', $user->role) == 'individual_customer' ? 'selected' : '' }}>Individual Client</option>
                                    <option value="company_customer" {{ old('role', $user->role) == 'company_customer' ? 'selected' : '' }}>Company / Corporate</option>
                                    <option value="driver" {{ old('role', $user->role) == 'driver' ? 'selected' : '' }}>Driver</option>
                                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>System Administrator</option>
                                </select>
                            </div>

                            <!-- Account Status -->
                            <div class="col-12 col-md-4">
                                <label for="status" class="form-label fw-semibold">Account Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="pending" {{ old('status', $user->status) == 'pending' ? 'selected' : '' }}>Pending Review</option>
                                    <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="banned" {{ old('status', $user->status) == 'banned' ? 'selected' : '' }}>Banned</option>
                                </select>
                            </div>

                            <!-- Password -->
                            <div class="col-12 col-md-4">
                                <label for="password" class="form-label fw-semibold">New Password <small class="text-muted fw-normal">(Leave blank to keep current)</small></label>
                                <div class="position-relative">
                                    <input type="password" 
                                           name="password" 
                                           id="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           style="padding-left: 48px !important; padding-right: 48px !important;"
                                           placeholder="••••••••">
                                    <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-lock-alt fs-5"></i>
                                    </span>
                                    <button type="button" 
                                            class="btn btn-link text-info p-0 text-decoration-none shadow-none border-0"
                                            style="position: absolute; right: 14px; left: auto; top: 50%; transform: translateY(-50%); z-index: 5;"
                                            onclick="togglePasswordVisibility()"
                                            title="Toggle password visibility">
                                        <i class="bx bx-show fs-5" id="password-toggle-icon"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Account Verification & Credit Balance Section -->
                    <div class="mb-4 p-3 rounded-4 border border-primary border-opacity-25" style="background: rgba(58, 119, 187, 0.08);">
                        <h6 class="text-primary fw-bold text-uppercase fs-7 mb-3 d-flex align-items-center gap-2" style="letter-spacing: 0.5px;">
                            <i class="bx bxs-badge-check fs-5 text-info"></i> Account Verification & Credit Limit (توثيق الحساب والرصيد الائتماني)
                        </h6>
                        <div class="row g-3 align-items-center">
                            <!-- Verification Toggle Switch -->
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold d-block">Official Verification Status (حالة التوثيق)</label>
                                <div class="form-check form-switch p-0 d-flex align-items-center gap-3">
                                    <input class="form-check-input ms-0 mt-0" 
                                           type="checkbox" 
                                           name="is_verified" 
                                           id="is_verified" 
                                           value="1" 
                                           style="width: 3.2rem; height: 1.8rem; cursor: pointer;"
                                           {{ old('is_verified', $user->is_verified) ? 'checked' : '' }}
                                           onchange="updateVerificationBadge(this.checked)">
                                    <label class="form-check-label fw-bold d-flex align-items-center gap-2 cursor-pointer" for="is_verified">
                                        <span id="verificationStatusBadge" class="badge {{ old('is_verified', $user->is_verified) ? 'bg-success bg-opacity-20 text-success border border-success' : 'bg-secondary bg-opacity-20 text-slate-400 border border-secondary' }} px-3 py-2 rounded-pill fs-7">
                                            <i id="verificationStatusIcon" class="bx {{ old('is_verified', $user->is_verified) ? 'bxs-badge-check text-info' : 'bx-shield' }} me-1"></i>
                                            <span id="verificationStatusText">{{ old('is_verified', $user->is_verified) ? 'Verified Account (حساب موثق)' : 'Unverified Account (غير موثق)' }}</span>
                                        </span>
                                    </label>
                                </div>
                                <small class="text-slate-400 mt-1 d-block">
                                    <i class="bx bx-info-circle me-1 text-info"></i> When enabled, a verified badge is prominently displayed on the client's app profile.
                                </small>
                            </div>

                            <!-- Credit Balance / Limit -->
                            <div class="col-12 col-md-6">
                                <label for="credit" class="form-label fw-semibold">
                                    Credit Balance / Limit (الرصيد الائتماني المتاح للسحب)
                                </label>
                                <div class="position-relative input-group">
                                    <input type="number" 
                                           step="0.001" 
                                           min="0" 
                                           name="credit" 
                                           id="credit" 
                                           class="form-control" 
                                           style="padding-left: 48px !important;" 
                                           placeholder="0.000"
                                           value="{{ old('credit', number_format($user->credit ?? 0, 3, '.', '')) }}">
                                    <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-wallet fs-5"></i>
                                    </span>
                                    <span class="input-group-text border-secondary border-opacity-25 text-primary fw-semibold" style="background: var(--doc-card-bg, rgba(255,255,255,0.05));">
                                        KWD / SAR
                                    </span>
                                </div>
                                <small class="text-slate-400 mt-1 d-block">
                                    <i class="bx bx-credit-card me-1 text-info"></i> Available credit limit granted to this user for automatic shipping deductions.
                                </small>
                            </div>
                        </div>
                    </div>

                    <hr class="border-secondary opacity-25 my-4">

                    <!-- Dynamic Corporate Company Profile Section -->
                    @php
                        $cp = $user->companyProfile;
                    @endphp
                    <div id="companyProfileSection" style="display: none; background: var(--doc-box-bg, rgba(58, 119, 187, 0.05));" class="p-3 mb-4 rounded-4 border border-info border-opacity-25">
                        <h6 class="text-info fw-bold text-uppercase fs-7 mb-3 d-flex align-items-center gap-2" style="letter-spacing: 0.5px;">
                            <i class="bx bx-building-house fs-5"></i> Corporate Company Details & Documentation
                        </h6>
                        <div class="row g-3">
                            <!-- Company Name -->
                            <div class="col-12 col-md-6">
                                <label for="company_name" class="form-label fw-semibold">Company Legal Name <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="text" 
                                           name="company_name" 
                                           id="company_name" 
                                           class="form-control @error('company_name') is-invalid @enderror" 
                                           style="padding-left: 48px !important;"
                                           placeholder="e.g. Salasil Logistics Co. LLC"
                                           value="{{ old('company_name', $cp->company_name ?? '') }}">
                                    <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-building fs-5"></i>
                                    </span>
                                </div>
                                @error('company_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Commercial Register (CR) -->
                            <div class="col-12 col-md-6">
                                <label for="commercial_register" class="form-label fw-semibold">Commercial Register (CR Number) <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="text" 
                                           name="commercial_register" 
                                           id="commercial_register" 
                                           class="form-control @error('commercial_register') is-invalid @enderror" 
                                           style="padding-left: 48px !important;"
                                           placeholder="e.g. 1010123456"
                                           value="{{ old('commercial_register', $cp->commercial_register ?? '') }}">
                                    <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-file-blank fs-5"></i>
                                    </span>
                                </div>
                                @error('commercial_register')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Civil ID / National ID -->
                            <div class="col-12 col-md-6">
                                <label for="civil_id" class="form-label fw-semibold">Civil ID / Owner National ID <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="text" 
                                           name="civil_id" 
                                           id="civil_id" 
                                           class="form-control @error('civil_id') is-invalid @enderror" 
                                           style="padding-left: 48px !important;"
                                           placeholder="e.g. 7001234567"
                                           value="{{ old('civil_id', $cp->civil_id ?? '') }}">
                                    <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-id-card fs-5"></i>
                                    </span>
                                </div>
                                @error('civil_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tax / VAT Number -->
                            <div class="col-12 col-md-6">
                                <label for="tax_number" class="form-label fw-semibold">Tax / VAT Number</label>
                                <div class="position-relative">
                                    <input type="text" 
                                           name="tax_number" 
                                           id="tax_number" 
                                           class="form-control @error('tax_number') is-invalid @enderror" 
                                           style="padding-left: 48px !important;"
                                           placeholder="e.g. 300012345600003"
                                           value="{{ old('tax_number', $cp->tax_number ?? '') }}">
                                    <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-receipt fs-5"></i>
                                    </span>
                                </div>
                                @error('tax_number')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- CR Document Upload & Current Document View -->
                            <div class="col-12 col-md-6">
                                <label for="commercial_register_doc" class="form-label fw-semibold d-flex align-items-center justify-content-between">
                                    <span>Commercial Register Document (وثيقة السجل التجاري)</span>
                                    @if(!empty($cp->commercial_register_doc))
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-0.5 fs-8">
                                            <i class="bx bx-check-circle me-1"></i>Document Attached
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-0.5 fs-8">
                                            No Document Uploaded
                                        </span>
                                    @endif
                                </label>
                                <input type="file" 
                                       name="commercial_register_doc" 
                                       id="commercial_register_doc" 
                                       class="form-control live-doc-picker"
                                       data-preview-box="#cr_doc_preview_edit"
                                       data-title="Commercial Register Document"
                                       accept=".pdf,.jpg,.jpeg,.png,.webp">
                                <small class="text-slate-400 d-block mt-1">Allowed formats: PDF, JPG, PNG, WEBP (Max 5MB)</small>
                                <div id="cr_doc_preview_edit" class="mt-2"></div>

                                @if(!empty($cp->commercial_register_doc))
                                    @php
                                        $docPath = 'upload/company_docs/' . $cp->commercial_register_doc;
                                        $docUrl = url($docPath);
                                        $ext = strtolower(pathinfo($cp->commercial_register_doc, PATHINFO_EXTENSION));
                                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
                                    @endphp
                                    <div class="card border border-info border-opacity-25 rounded-3 mt-3 p-3 shadow-sm" style="background: var(--doc-card-bg, #ffffff);">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                            <div class="d-flex align-items-center gap-3">
                                                @if($isImage)
                                                    <div class="position-relative media-zoomable cursor-pointer" data-src="{{ $docUrl }}" data-title="Commercial Register Document" style="cursor: pointer;">
                                                        <img src="{{ $docUrl }}" 
                                                             alt="CR Document" 
                                                             class="rounded-3 border border-info border-opacity-50 shadow-sm"
                                                             style="width: 70px; height: 70px; object-fit: cover;">
                                                        <span class="position-absolute bottom-0 end-0 bg-info text-dark rounded-circle p-1 d-flex align-items-center justify-content-center shadow-sm" style="width: 20px; height: 20px; font-size: 11px;">
                                                            <i class="bx bx-search-alt-2"></i>
                                                        </span>
                                                    </div>
                                                @else
                                                    <div class="rounded-3 bg-danger bg-opacity-20 border border-danger border-opacity-25 p-2 text-danger d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                        <i class="bx bxs-file-pdf fs-1"></i>
                                                    </div>
                                                @endif

                                                <div>
                                                    <span class="d-block fw-bold req-doc-title fs-7 mb-1 text-truncate" style="max-width: 200px;">
                                                        {{ $cp->commercial_register_doc }}
                                                    </span>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="badge badge-st-approved text-uppercase fs-8">
                                                            {{ $ext }} File
                                                        </span>
                                                        <span class="req-doc-sub fs-8">
                                                            Current Attached Document
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-center gap-2">
                                                <a href="{{ $docUrl }}" 
                                                   data-src="{{ $docUrl }}"
                                                   data-title="Commercial Register Document - {{ $cp->company_name ?? '' }}"
                                                   class="btn btn-sm btn-info rounded-3 px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1 media-zoomable shadow-sm">
                                                    <i class="bx bx-zoom-in fs-6"></i>
                                                    <span>View / Enlarge</span>
                                                </a>
                                                <a href="{{ $docUrl }}" 
                                                   target="_blank" 
                                                   download
                                                   class="btn btn-sm btn-outline-secondary rounded-3 px-2 py-1.5"
                                                   title="Download File">
                                                    <i class="bx bx-download fs-6"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @error('commercial_register_doc')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Verification Status -->
                            <div class="col-12 col-md-6">
                                <label for="verification_status" class="form-label fw-semibold">Verification Status</label>
                                <select name="verification_status" id="verification_status" class="form-select">
                                    <option value="pending" {{ old('verification_status', $cp->verification_status ?? 'pending') == 'pending' ? 'selected' : '' }}>Pending Verification</option>
                                    <option value="verified" {{ old('verification_status', $cp->verification_status ?? '') == 'verified' ? 'selected' : '' }}>Verified Company</option>
                                    <option value="rejected" {{ old('verification_status', $cp->verification_status ?? '') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>

                            <!-- Representative Name -->
                            <div class="col-12 col-md-4">
                                <label for="representative_name" class="form-label fw-semibold">Authorized Representative</label>
                                <input type="text" 
                                       name="representative_name" 
                                       id="representative_name" 
                                       class="form-control" 
                                       placeholder="e.g. Ahmed Al-Otaibi"
                                       value="{{ old('representative_name', $cp->representative_name ?? '') }}">
                            </div>

                            <!-- Representative Position -->
                            <div class="col-12 col-md-4">
                                <label for="representative_position" class="form-label fw-semibold">Representative Title</label>
                                <input type="text" 
                                       name="representative_position" 
                                       id="representative_position" 
                                       class="form-control" 
                                       placeholder="e.g. Operations Manager"
                                       value="{{ old('representative_position', $cp->representative_position ?? '') }}">
                            </div>

                            <!-- Representative Phone -->
                            <div class="col-12 col-md-4">
                                <label for="representative_phone" class="form-label fw-semibold">Representative Phone</label>
                                <input type="text" 
                                       name="representative_phone" 
                                       id="representative_phone" 
                                       class="form-control" 
                                       placeholder="e.g. +966501234567"
                                       value="{{ old('representative_phone', $cp->representative_phone ?? '') }}">
                            </div>

                            <!-- Rejection Reason -->
                            <div class="col-12" id="rejectionReasonDiv" style="display: none;">
                                <label for="rejection_reason" class="form-label fw-semibold text-danger">Rejection Reason</label>
                                <textarea name="rejection_reason" 
                                          id="rejection_reason" 
                                          rows="2" 
                                          class="form-control border-danger border-opacity-50" 
                                          placeholder="Specify reason for rejecting this company registration...">{{ old('rejection_reason', $cp->rejection_reason ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Fleet Driver Profile Section -->
                    @php
                        $dp = $user->driverProfile;
                    @endphp
                    <div id="driverProfileSection" style="display: none;" class="p-3 mb-4 rounded-4 border border-warning border-opacity-25 bg-warning bg-opacity-10">
                        <h6 class="text-warning fw-bold text-uppercase fs-7 mb-3 d-flex align-items-center gap-2" style="letter-spacing: 0.5px;">
                            <i class="bx bx-id-card fs-5"></i> Fleet Driver Details & Documentation
                        </h6>
                        <div class="row g-3">
                            <!-- Driving License Number -->
                            <div class="col-12 col-md-6">
                                <label for="license_number" class="form-label fw-semibold">Driving License Number</label>
                                <div class="position-relative">
                                    <input type="text" 
                                           name="license_number" 
                                           id="license_number" 
                                           class="form-control @error('license_number') is-invalid @enderror" 
                                           style="padding-left: 48px !important;"
                                           placeholder="e.g. DL-987654321"
                                           value="{{ old('license_number', $dp->license_number ?? '') }}">
                                    <span class="position-absolute text-warning" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-credit-card-front fs-5"></i>
                                    </span>
                                </div>
                                @error('license_number')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Initial Wallet Balance -->
                            <div class="col-12 col-md-3">
                                <label for="wallet_balance" class="form-label fw-semibold">Wallet Balance</label>
                                <div class="position-relative">
                                    <input type="number" 
                                           step="0.01"
                                           name="wallet_balance" 
                                           id="wallet_balance" 
                                           class="form-control" 
                                           style="padding-left: 48px !important;"
                                           placeholder="0.00"
                                           value="{{ old('wallet_balance', $dp->wallet_balance ?? '0.00') }}">
                                    <span class="position-absolute text-warning" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-wallet fs-5"></i>
                                    </span>
                                </div>
                            </div>

                            <!-- Driver Rating -->
                            <div class="col-12 col-md-3">
                                <label for="rating" class="form-label fw-semibold">Driver Rating</label>
                                <div class="position-relative">
                                    <input type="number" 
                                           step="0.1"
                                           min="1"
                                           max="5"
                                           name="rating" 
                                           id="rating" 
                                           class="form-control" 
                                           style="padding-left: 48px !important;"
                                           placeholder="5.0"
                                           value="{{ old('rating', $dp->rating ?? '5.00') }}">
                                    <span class="position-absolute text-warning" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-star fs-5"></i>
                                    </span>
                                </div>
                            </div>

                            <!-- Driving License Photo -->
                            <div class="col-12 col-md-4">
                                <label for="license_photo" class="form-label fw-semibold">Driving License Document</label>
                                <input type="file" 
                                       name="license_photo" 
                                       id="license_photo" 
                                       class="form-control live-doc-picker"
                                       data-preview-box="#license_photo_preview_edit"
                                       data-title="Driving License Document"
                                       accept=".pdf,.jpg,.jpeg,.png,.webp">
                                <small class="text-slate-400 d-block mt-1">Allowed: PDF, JPG, PNG, WEBP</small>
                                <div id="license_photo_preview_edit" class="mt-2"></div>
                                @if(!empty($dp->license_photo) && file_exists(public_path('upload/driver_docs/' . $dp->license_photo)))
                                    <div class="mt-2">
                                        <a href="{{ url('upload/driver_docs/' . $dp->license_photo) }}" 
                                           data-src="{{ url('upload/driver_docs/' . $dp->license_photo) }}"
                                           data-title="Driving License Document"
                                           class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1 media-zoomable">
                                            <i class="bx bx-show fs-6"></i>
                                            <span>View License</span>
                                        </a>
                                    </div>
                                @endif
                            </div>

                            <!-- Truck Registration Photo -->
                            <div class="col-12 col-md-4">
                                <label for="truck_registration_photo" class="form-label fw-semibold">Truck Registration Document</label>
                                <input type="file" 
                                       name="truck_registration_photo" 
                                       id="truck_registration_photo" 
                                       class="form-control live-doc-picker"
                                       data-preview-box="#truck_reg_preview_edit"
                                       data-title="Truck Registration Document"
                                       accept=".pdf,.jpg,.jpeg,.png,.webp">
                                <small class="text-slate-400 d-block mt-1">Allowed: PDF, JPG, PNG, WEBP</small>
                                <div id="truck_reg_preview_edit" class="mt-2"></div>
                                @if(!empty($dp->truck_registration_photo) && file_exists(public_path('upload/driver_docs/' . $dp->truck_registration_photo)))
                                    <div class="mt-2">
                                        <a href="{{ url('upload/driver_docs/' . $dp->truck_registration_photo) }}" 
                                           data-src="{{ url('upload/driver_docs/' . $dp->truck_registration_photo) }}"
                                           data-title="Truck Registration Document"
                                           class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1 media-zoomable">
                                            <i class="bx bx-show fs-6"></i>
                                            <span>View Registration</span>
                                        </a>
                                    </div>
                                @endif
                            </div>

                            <!-- Civil ID Photo -->
                            <div class="col-12 col-md-4">
                                <label for="civil_id_photo" class="form-label fw-semibold">Civil / National ID Document</label>
                                <input type="file" 
                                       name="civil_id_photo" 
                                       id="civil_id_photo" 
                                       class="form-control live-doc-picker"
                                       data-preview-box="#civil_id_preview_edit"
                                       data-title="Civil ID Document"
                                       accept=".pdf,.jpg,.jpeg,.png,.webp">
                                <small class="text-slate-400 d-block mt-1">Allowed: PDF, JPG, PNG, WEBP</small>
                                <div id="civil_id_preview_edit" class="mt-2"></div>
                                @if(!empty($dp->civil_id_photo) && file_exists(public_path('upload/driver_docs/' . $dp->civil_id_photo)))
                                    <div class="mt-2">
                                        <a href="{{ url('upload/driver_docs/' . $dp->civil_id_photo) }}" 
                                           data-src="{{ url('upload/driver_docs/' . $dp->civil_id_photo) }}"
                                           data-title="Civil ID Document"
                                           class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1 media-zoomable">
                                            <i class="bx bx-show fs-6"></i>
                                            <span>View Civil ID</span>
                                        </a>
                                    </div>
                                @endif
                            </div>

                            <!-- Availability Status -->
                            <div class="col-12 col-md-6">
                                <label for="availability_status" class="form-label fw-semibold">Availability Status</label>
                                <select name="availability_status" id="availability_status" class="form-select">
                                    <option value="offline" {{ old('availability_status', $dp->availability_status ?? 'offline') == 'offline' ? 'selected' : '' }}>Offline</option>
                                    <option value="available" {{ old('availability_status', $dp->availability_status ?? '') == 'available' ? 'selected' : '' }}>Available for Orders</option>
                                    <option value="busy" {{ old('availability_status', $dp->availability_status ?? '') == 'busy' ? 'selected' : '' }}>Busy on Trip</option>
                                </select>
                            </div>

                            <!-- Driver Verification Status -->
                            <div class="col-12 col-md-6">
                                <label for="driver_verification_status" class="form-label fw-semibold">Verification Status</label>
                                <select name="driver_verification_status" id="driver_verification_status" class="form-select">
                                    <option value="pending" {{ old('driver_verification_status', $dp->verification_status ?? 'pending') == 'pending' ? 'selected' : '' }}>Pending Verification</option>
                                    <option value="verified" {{ old('driver_verification_status', $dp->verification_status ?? '') == 'verified' ? 'selected' : '' }}>Verified Driver</option>
                                    <option value="rejected" {{ old('driver_verification_status', $dp->verification_status ?? '') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>

                            <!-- Driver Rejection Reason -->
                            <div class="col-12" id="driverRejectionReasonDiv" style="display: none;">
                                <label for="driver_rejection_reason" class="form-label fw-semibold text-danger">Driver Rejection Reason</label>
                                <textarea name="driver_rejection_reason" 
                                          id="driver_rejection_reason" 
                                          rows="2" 
                                          class="form-control border-danger border-opacity-50" 
                                          placeholder="Specify reason for rejecting this driver application...">{{ old('driver_rejection_reason', $dp->rejection_reason ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Company Required Documents Specification & Request Block -->
                    <div id="companyDocRequirementsBlock" class="p-3 mb-4 doc-requirements-box shadow-sm" style="{{ old('role', $user->role) === 'company_customer' ? '' : 'display: none;' }}">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-2 border-bottom" style="border-color: var(--doc-box-border) !important;">
                            <div>
                                <h6 class="section-header-title text-uppercase fs-7 mb-1 d-flex align-items-center gap-2" style="letter-spacing: 0.5px;">
                                    <i class="bx bx-list-check fs-5"></i> Specify Required Documents for this Client (تحديد المستندات المطلوبة للرفع)
                                </h6>
                                <p class="section-header-desc fs-8 mb-0">
                                    Check which official documents are mandatory for this company to upload. The client's mobile app will sync these requirements in real-time.
                                </p>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span id="selectedReqDocsBadge" class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-30 px-3 py-1.5 rounded-pill fs-7 fw-semibold">
                                    <i class="bx bx-task me-1"></i>0 Required Selected
                                </span>
                            </div>
                        </div>

                        <!-- Quick Action Toolbar -->
                        <div class="doc-req-toolbar d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 p-2 rounded-3">
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-2 px-2.5 py-1 fs-8" id="btnSelectAllReqDocs">
                                    <i class="bx bx-check-double me-1"></i>Select All
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-2 px-2.5 py-1 fs-8" id="btnDeselectAllReqDocs">
                                    <i class="bx bx-x me-1"></i>Deselect All
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-info rounded-2 px-2.5 py-1 fs-8" id="btnResetDefaultReqDocs">
                                    <i class="bx bx-reset me-1"></i>Reset to Defaults
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-warning rounded-2 px-2.5 py-1 fs-8 fw-semibold" id="btnOpenAddCustomDocModal" data-bs-toggle="modal" data-bs-target="#addCustomDocModal">
                                    <i class="bx bx-plus-circle me-1"></i>+ Add Custom Document (إضافة مستند إضافي)
                                </button>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-info rounded-3 px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm text-dark" id="btnSendDocRequestNotification">
                                    <i class="bx bx-bell fs-6"></i>
                                    <span>Send Upload Request to Client (إرسال إشعار للعميل)</span>
                                </button>
                            </div>
                        </div>

                        <!-- Documents Grid -->
                        <div class="row g-3" id="reqDocsGrid">
                            @foreach($availableCompanyDocuments ?? [] as $reqDoc)
                                @php
                                    $isConfigured = ($user->companyProfile && is_array($user->companyProfile->required_documents));
                                    $isRequiredForUser = $isConfigured
                                        ? in_array((int)$reqDoc->id, $user->companyProfile->required_documents, true)
                                        : (bool)$reqDoc->is_required;
                                    
                                    $uploadedDoc = $user->companyDocuments ? $user->companyDocuments->firstWhere('required_document_id', $reqDoc->id) : null;
                                    $isUploaded = ($uploadedDoc !== null);
                                @endphp
                                <div class="col-12 col-md-6" id="req_doc_col_{{ $reqDoc->id }}">
                                    <div class="p-3 rounded-3 h-100 position-relative transition-all req-doc-card {{ $isRequiredForUser ? 'is-selected' : '' }}" id="req_doc_card_{{ $reqDoc->id }}">
                                        <div class="d-flex align-items-start justify-content-between gap-2">
                                            <div class="form-check form-switch m-0 pt-0.5">
                                                <input class="form-check-input req-doc-checkbox" 
                                                       type="checkbox" 
                                                       name="required_documents[]" 
                                                       value="{{ $reqDoc->id }}" 
                                                       id="req_doc_check_{{ $reqDoc->id }}"
                                                       data-id="{{ $reqDoc->id }}"
                                                       data-default="{{ $reqDoc->is_required ? '1' : '0' }}"
                                                       data-is-custom="{{ $reqDoc->user_id ? '1' : '0' }}"
                                                       data-title-en="{{ $reqDoc->name_en }}"
                                                       data-title-ar="{{ $reqDoc->name_ar }}"
                                                       {{ $isRequiredForUser ? 'checked' : '' }}>
                                            </div>
                                            <div class="flex-grow-1 min-w-0">
                                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                                    <label class="form-check-label fw-bold doc-card-title fs-7 mb-0 cursor-pointer d-block text-truncate" for="req_doc_check_{{ $reqDoc->id }}">
                                                        {{ $reqDoc->name_en }}
                                                    </label>
                                                    @if($reqDoc->user_id)
                                                        <span class="badge badge-st-custom fs-9"><i class="bx bx-bookmark-plus me-0.5"></i>Custom</span>
                                                    @endif
                                                </div>
                                                <small class="d-block fs-8 mb-1 doc-card-sub">
                                                    {{ $reqDoc->name_ar }}
                                                </small>
                                                <div class="d-flex align-items-center flex-wrap gap-1.5 mt-1">
                                                    @if($reqDoc->user_id)
                                                        <span class="badge badge-st-custom fs-9">Additional Document</span>
                                                    @elseif($reqDoc->is_required)
                                                        <span class="badge badge-def-req fs-9">Default: Required</span>
                                                    @else
                                                        <span class="badge badge-def-opt fs-9">Default: Optional</span>
                                                    @endif

                                                    @if($isUploaded)
                                                        @if($uploadedDoc->status === 'approved' || $uploadedDoc->status === 'verified')
                                                            <span class="badge badge-st-approved fs-9"><i class="bx bx-check-circle me-0.5"></i>Uploaded & Approved</span>
                                                        @elseif($uploadedDoc->status === 'rejected')
                                                            <span class="badge badge-st-rejected fs-9"><i class="bx bx-x-circle me-0.5"></i>Uploaded (Rejected)</span>
                                                        @else
                                                            <span class="badge badge-st-pending fs-9"><i class="bx bx-time me-0.5"></i>Uploaded (Pending)</span>
                                                        @endif
                                                    @else
                                                        <span class="badge badge-st-notuploaded fs-9"><i class="bx bx-upload me-0.5"></i>Not Uploaded Yet</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="flex-shrink-0 d-flex align-items-center gap-1">
                                                <button type="button" 
                                                        class="btn btn-xs rounded-pill px-2.5 py-1 fs-9 single-doc-request-btn"
                                                        data-id="{{ $reqDoc->id }}"
                                                        data-name="{{ $reqDoc->name_en }}"
                                                        title="Request client to upload this document specifically">
                                                    <i class="bx bx-bell me-0.5"></i>Request
                                                </button>
                                                @if($reqDoc->user_id)
                                                    <button type="button" 
                                                            class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1 fs-9 delete-custom-doc-btn"
                                                            data-id="{{ $reqDoc->id }}"
                                                            data-name="{{ $reqDoc->name_en }}"
                                                            title="Delete this custom document requirement">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Form submission notification toggle -->
                        <div class="mt-3 pt-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2" style="border-color: var(--doc-box-border) !important;">
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" name="send_doc_request_notification" value="1" id="sendDocRequestNotif" checked>
                                <label class="form-check-label section-header-desc fs-8" for="sendDocRequestNotif">
                                    <i class="bx bx-bell me-1 text-info"></i> Send push/in-app alert to client when saving changes (إرسال إشعار في التطبيق للعميل عند حفظ التعديلات)
                                </label>
                            </div>
                            <div class="section-header-desc fs-8">
                                <i class="bx bx-mobile-alt me-1 text-info"></i> Notification will open <strong>Company Documents</strong> directly in the mobile app.
                            </div>
                        </div>
                    </div>

                    <!-- Uploaded Documents from Mobile App Section -->
                    <div class="p-3 mb-4 uploaded-docs-box shadow-sm" id="uploadedDocumentsSection">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-2 border-bottom" style="border-color: var(--doc-box-border) !important;">
                            <div>
                                <h6 class="section-header-title text-uppercase fs-7 mb-1 d-flex align-items-center gap-2" style="letter-spacing: 0.5px;">
                                    <i class="bx bx-file-blank fs-5 text-info"></i> Uploaded Documents from Mobile App (المستندات المرفوعة من التطبيق)
                                </h6>
                                <p class="section-header-desc fs-8 mb-0">
                                    Official company and verification files uploaded by this client via the mobile application.
                                </p>
                            </div>
                            <div>
                                @php
                                    $docCount = $user->companyDocuments ? $user->companyDocuments->count() : 0;
                                @endphp
                                <span class="badge {{ $docCount > 0 ? 'bg-info bg-opacity-20 text-info border border-info border-opacity-25' : 'badge-def-opt' }} px-3 py-1.5 rounded-pill fs-7 fw-semibold">
                                    <i class="bx bx-paperclip me-1"></i>{{ $docCount }} {{ $docCount === 1 ? 'Document' : 'Documents' }} Attached
                                </span>
                            </div>
                        </div>

                        @if(!empty($user->companyDocuments) && $user->companyDocuments->count() > 0)
                            <div class="row g-3">
                                @foreach($user->companyDocuments as $doc)
                                    @php
                                        $ext = strtolower($doc->file_type ?? pathinfo($doc->file_name ?? $doc->file_path, PATHINFO_EXTENSION));
                                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
                                        $docUrl = $doc->file_url;
                                    @endphp
                                    <div class="col-12 col-md-6" id="doc_card_{{ $doc->id }}">
                                        <div class="card h-100 rounded-3 p-3 shadow-sm position-relative uploaded-doc-item-card">
                                            <!-- Card Header: Title & Badges -->
                                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                                <div class="flex-grow-1">
                                                    <h6 class="fw-bold doc-title mb-1 fs-6 d-flex align-items-center gap-1.5">
                                                        <i class="bx bx-file text-info"></i>
                                                        <span>{{ $doc->title }}</span>
                                                    </h6>
                                                    @if($doc->requiredDocument)
                                                        <span class="doc-sub fs-8">{{ $doc->requiredDocument->name_ar ?? '' }}</span>
                                                    @endif
                                                </div>
                                                <div class="d-flex flex-column align-items-end gap-1">
                                                    @if($doc->requiredDocument && $doc->requiredDocument->is_required)
                                                        <span class="badge badge-def-req fs-8">Required</span>
                                                    @else
                                                        <span class="badge badge-def-opt fs-8">Optional</span>
                                                    @endif

                                                    <span id="doc_status_badge_{{ $doc->id }}">
                                                        @if($doc->status === 'approved' || $doc->status === 'verified')
                                                            <span class="badge badge-st-approved fs-8"><i class="bx bx-check-circle me-1"></i>Approved</span>
                                                        @elseif($doc->status === 'rejected')
                                                            <span class="badge badge-st-rejected fs-8"><i class="bx bx-x-circle me-1"></i>Rejected</span>
                                                        @else
                                                            <span class="badge badge-st-pending fs-8"><i class="bx bx-time me-1"></i>Pending Review</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Thumbnail & Details -->
                                            <div class="d-flex align-items-center gap-3 my-2 p-2 rounded-2 thumb-details-box">
                                                @if($isImage)
                                                    <div class="position-relative media-zoomable cursor-pointer flex-shrink-0" data-src="{{ $docUrl }}" data-title="{{ $doc->title }}" style="cursor: pointer;">
                                                        <img src="{{ $docUrl }}" 
                                                             alt="{{ $doc->title }}" 
                                                             class="rounded-2 border border-info border-opacity-50 shadow-sm"
                                                             style="width: 65px; height: 65px; object-fit: cover;">
                                                        <span class="position-absolute bottom-0 end-0 bg-info text-dark rounded-circle p-1 d-flex align-items-center justify-content-center shadow-sm" style="width: 18px; height: 18px; font-size: 10px;">
                                                            <i class="bx bx-search-alt-2"></i>
                                                        </span>
                                                    </div>
                                                @else
                                                    <div class="rounded-2 bg-danger bg-opacity-20 border border-danger border-opacity-25 p-2 text-danger d-flex align-items-center justify-content-center flex-shrink-0" style="width: 65px; height: 65px;">
                                                        <i class="bx bxs-file-pdf fs-1"></i>
                                                    </div>
                                                @endif

                                                <div class="overflow-hidden flex-grow-1">
                                                    <span class="d-block fw-semibold doc-title fs-7 text-truncate mb-0.5" title="{{ $doc->file_name }}">
                                                        {{ $doc->file_name ?? basename($doc->file_path) }}
                                                    </span>
                                                    <div class="d-flex align-items-center gap-2 mb-1">
                                                        <span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-20 fs-8 text-uppercase">
                                                            {{ $ext }} File
                                                        </span>
                                                        @if($doc->file_size)
                                                            <span class="doc-sub fs-8">{{ $doc->file_size }}</span>
                                                        @endif
                                                    </div>
                                                    <span class="doc-sub fs-8 d-block text-truncate">
                                                        <i class="bx bx-calendar me-1"></i>Uploaded: {{ $doc->created_at ? $doc->created_at->format('d M Y, h:i A') : 'Recently' }}
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Rejection reason if any -->
                                            <div id="doc_rejection_box_{{ $doc->id }}" class="mt-1" style="{{ $doc->status === 'rejected' && $doc->rejection_reason ? '' : 'display: none;' }}">
                                                <div class="alert alert-danger py-1 px-2.5 mb-2 fs-8 border-0 rounded-2 d-flex align-items-start gap-1.5">
                                                    <i class="bx bx-error-circle fs-6 flex-shrink-0 mt-0.5"></i>
                                                    <span id="doc_rejection_text_{{ $doc->id }}">Reason: {{ $doc->rejection_reason }}</span>
                                                </div>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-2 pt-2 border-top" style="border-color: var(--doc-box-border) !important;">
                                                <div class="d-flex align-items-center gap-1.5">
                                                    <a href="{{ $docUrl }}" 
                                                       data-src="{{ $docUrl }}"
                                                       data-title="{{ $doc->title }} - {{ $user->name }}"
                                                       class="btn btn-sm btn-info rounded-3 px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1 media-zoomable shadow-sm"
                                                       title="View & Zoom">
                                                        <i class="bx bx-zoom-in fs-6"></i>
                                                        <span class="fs-8">View</span>
                                                    </a>
                                                    <a href="{{ $docUrl }}" 
                                                       target="_blank" 
                                                       download
                                                       class="btn btn-sm btn-outline-secondary rounded-3 px-2 py-1"
                                                       title="Download Document">
                                                        <i class="bx bx-download fs-6"></i>
                                                    </a>
                                                </div>

                                                <div class="d-flex align-items-center gap-1">
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-success rounded-3 px-2 py-1 fs-8 fw-semibold doc-status-btn d-inline-flex align-items-center gap-1"
                                                            data-id="{{ $doc->id }}" 
                                                            data-status="approved"
                                                            title="Approve Document">
                                                        <i class="bx bx-check"></i>
                                                        <span>Approve</span>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-warning rounded-3 px-2 py-1 fs-8 fw-semibold doc-reject-modal-btn d-inline-flex align-items-center gap-1"
                                                            data-id="{{ $doc->id }}" 
                                                            data-title="{{ $doc->title }}"
                                                            title="Reject Document">
                                                        <i class="bx bx-x"></i>
                                                        <span>Reject</span>
                                                    </button>
                                                    <a href="{{ route('user.document.delete', $doc->id) }}" 
                                                       onclick="return confirm('Are you sure you want to permanently delete this document?')"
                                                       class="btn btn-sm btn-outline-danger rounded-3 px-2 py-1"
                                                       title="Delete Document">
                                                        <i class="bx bx-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4 px-3 rounded-3 doc-empty-box">
                                <div class="rounded-circle bg-secondary bg-opacity-10 d-inline-flex align-items-center justify-content-center p-3 mb-2">
                                    <i class="bx bx-folder-open text-slate-400 fs-1"></i>
                                </div>
                                <h6 class="fw-bold mb-1">No Documents Uploaded Yet</h6>
                                <p class="fs-8 mb-0">
                                    This client has not uploaded any official documents from the mobile app.
                                </p>
                            </div>
                        @endif
                    </div>

<hr class="border-secondary opacity-25 my-4">

                    <!-- Profile Photo Section -->
                    <div class="mb-4">
                        <h6 class="text-info fw-bold text-uppercase fs-7 mb-3" style="letter-spacing: 0.5px;">
                            <i class="bx bx-image me-1"></i> Profile Photo / Avatar
                        </h6>
                        <div class="row align-items-center g-3">
                            <div class="col-12 col-md-8">
                                <label for="image" class="form-label fw-semibold">Change Profile Picture</label>
                                <input type="file" 
                                       name="photo" 
                                       id="image" 
                                       class="form-control"
                                       accept="image/*">
                                <small class="text-slate-400 d-block mt-1">
                                    Allowed formats: JPG, PNG, WEBP, GIF (Max size: 2MB).
                                </small>
                            </div>

                            <div class="col-12 col-md-4 text-center text-md-start">
                                <label class="form-label fw-semibold d-block">Image Preview (Click to Enlarge)</label>
                                <div class="position-relative d-inline-block media-zoomable cursor-pointer" data-title="Profile Photo Preview" style="cursor: pointer;">
                                    <img id="showImage" 
                                         src="{{ (!empty($user->photo) && file_exists(public_path('upload/user_images/'.$user->photo))) ? url('upload/user_images/'.$user->photo) : url('upload/no_image.jpg') }}" 
                                         alt="Profile Avatar Preview" 
                                         class="rounded-circle border border-info shadow-sm p-1"
                                         style="width: 80px; height: 80px; object-fit: cover;">
                                    <span class="position-absolute bottom-0 end-0 bg-info text-dark rounded-circle p-1 d-flex align-items-center justify-content-center shadow-sm" style="width: 24px; height: 24px; font-size: 12px;">
                                        <i class="bx bx-search-alt-2"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top border-secondary border-opacity-25">
                        <a href="{{ route('all.owners') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3 fw-semibold">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 fw-semibold d-inline-flex align-items-center gap-2">
                            <i class="bx bx-check-circle fs-5"></i>
                            <span>Update Client Profile</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        // Live image preview handler
        $('#image').change(function(e){
            var reader = new FileReader();
            reader.onload = function(e){
                $('#showImage').attr('src', e.target.result);
            }
            if (e.target.files[0]) {
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        // Dynamic Role Sections Toggle Function
        function toggleRoleSections() {
            var selectedRole = $('#role').val();
            if (selectedRole === 'company_customer') {
                $('#companyProfileSection').slideDown(300);
                $('#companyDocRequirementsBlock').slideDown(300);
                $('#driverProfileSection').slideUp(300);
                $('#company_name, #commercial_register, #civil_id').prop('required', true);
            } else if (selectedRole === 'driver') {
                $('#driverProfileSection').slideDown(300);
                $('#companyProfileSection').slideUp(300);
                $('#companyDocRequirementsBlock').slideUp(300);
                $('#company_name, #commercial_register, #civil_id').prop('required', false);
            } else {
                $('#companyProfileSection').slideUp(300);
                $('#companyDocRequirementsBlock').slideUp(300);
                $('#driverProfileSection').slideUp(300);
                $('#company_name, #commercial_register, #civil_id').prop('required', false);
            }
        }

        function toggleRejectionReasons() {
            if ($('#verification_status').val() === 'rejected') {
                $('#rejectionReasonDiv').slideDown(200);
            } else {
                $('#rejectionReasonDiv').slideUp(200);
            }

            if ($('#driver_verification_status').val() === 'rejected') {
                $('#driverRejectionReasonDiv').slideDown(200);
            } else {
                $('#driverRejectionReasonDiv').slideUp(200);
            }
        }

        $('#role').change(function() {
            toggleRoleSections();
        });

        $('#verification_status, #driver_verification_status').change(function() {
            toggleRejectionReasons();
        });

        // Run checks on page load
        toggleRoleSections();
        toggleRejectionReasons();

        // Dynamic Cities Loading on Country Selection
        $('#country_id').on('change', function() {
            var countryId = $(this).val();
            var $citySelect = $('#city_id');
            $citySelect.empty().append('<option value="">Select City (اختر المدينة)</option>');

            if (countryId) {
                $.ajax({
                    url: "{{ url('/admin/city/get-cities-ajax') }}/" + countryId,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        if (data && data.length > 0) {
                            $.each(data, function(key, value) {
                                $citySelect.append('<option value="' + value.id + '">' + value.name_en + ' (' + value.name_ar + ')</option>');
                            });
                        }
                    }
                });
            }
        });
    });

    // Toggle password visibility
    function togglePasswordVisibility() {
        var passwordInput = document.getElementById('password');
        var toggleIcon = document.getElementById('password-toggle-icon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('bx-show');
            toggleIcon.classList.add('bx-hide');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('bx-hide');
            toggleIcon.classList.add('bx-show');
        }
    }
</script>


<script>
function updateVerificationBadge(checked) {
    var badge = document.getElementById('verificationStatusBadge');
    var icon = document.getElementById('verificationStatusIcon');
    var text = document.getElementById('verificationStatusText');
    if (checked) {
        badge.className = 'badge bg-success bg-opacity-20 text-success border border-success px-3 py-2 rounded-pill fs-7';
        icon.className = 'bx bxs-badge-check text-info me-1';
        text.innerText = 'Verified Account (حساب موثق)';
    } else {
        badge.className = 'badge bg-secondary bg-opacity-20 text-slate-400 border border-secondary px-3 py-2 rounded-pill fs-7';
        icon.className = 'bx bx-shield me-1';
        text.innerText = 'Unverified Account (غير موثق)';
    }
}
</script>


<!-- Document Rejection Reason Modal -->
<div class="modal fade doc-themed-modal" id="docRejectModal" tabindex="-1" aria-labelledby="docRejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-bold text-danger d-flex align-items-center gap-2" id="docRejectModalLabel">
                    <i class="bx bx-x-circle fs-5"></i> Reject Document (رفض المستند)
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="modal_reject_doc_id" value="">
                <p class="fs-7 mb-3">
                    Specify the reason for rejecting document: <strong class="text-info" id="modal_reject_doc_title"></strong>.
                </p>
                <div class="mb-3">
                    <label for="modal_rejection_reason" class="form-label fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" 
                              id="modal_rejection_reason" 
                              rows="3" 
                              placeholder="e.g. Expired document, blurry photo, official seal missing..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-top py-2.5 px-4">
                <button type="button" class="btn btn-outline-secondary rounded-3 px-3 py-1.5" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger rounded-3 px-4 py-1.5 fw-semibold d-inline-flex align-items-center gap-1" id="confirmRejectDocBtn">
                    <i class="bx bx-x fs-6"></i>
                    <span>Confirm Rejection</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Custom Document Requirement Modal -->
<div class="modal fade doc-themed-modal" id="addCustomDocModal" tabindex="-1" aria-labelledby="addCustomDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-bold text-info d-flex align-items-center gap-2" id="addCustomDocModalLabel">
                    <i class="bx bx-file-blank fs-5"></i> Add Custom Required Document (إضافة مستند إضافي مطلوب)
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addCustomDocForm">
                <div class="modal-body p-4">
                    <p class="fs-7 mb-3">
                        Specify an additional official document required specifically from this client. The requirement will immediately reflect in their mobile app.
                    </p>
                    <div class="mb-3">
                        <label for="custom_doc_name_en" class="form-label fw-semibold fs-7">Document Name (English) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" 
                               id="custom_doc_name_en" 
                               name="name_en" 
                               required 
                               placeholder="e.g. Municipal License / Food Safety Certificate">
                    </div>
                    <div class="mb-3">
                        <label for="custom_doc_name_ar" class="form-label fw-semibold fs-7">اسم المستند (بالعربي)</label>
                        <input type="text" class="form-control" 
                               id="custom_doc_name_ar" 
                               name="name_ar" 
                               placeholder="مثال: رخصة البلدية / شهادة سلامة الأغذية">
                    </div>
                    <div class="mb-3">
                        <label for="custom_doc_desc_en" class="form-label fw-semibold fs-7">Instructions / Description (Optional)</label>
                        <textarea class="form-control" 
                                  id="custom_doc_desc_en" 
                                  name="description_en" 
                                  rows="2" 
                                  placeholder="e.g. Please upload the latest valid license issued by the Municipality."></textarea>
                    </div>
                    <div class="p-3 rounded-3 modal-switches-box mb-2">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_required" value="1" id="custom_doc_is_required" checked>
                            <label class="form-check-label fw-semibold cursor-pointer fs-7" for="custom_doc_is_required">
                                Mandatory Requirement (مستند إلزامي)
                            </label>
                        </div>
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" name="send_notif" value="1" id="custom_doc_send_notif" checked>
                            <label class="form-check-label text-info fw-semibold cursor-pointer fs-7" for="custom_doc_send_notif">
                                <i class="bx bx-bell me-1"></i> Send in-app notification to client immediately (إرسال إشعار فوري للتطبيق)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4">
                    <button type="button" class="btn btn-outline-secondary rounded-3 px-3 py-1.5" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info rounded-3 px-4 py-1.5 fw-bold text-dark d-inline-flex align-items-center gap-1" id="btnSaveCustomDoc">
                        <i class="bx bx-check fs-6"></i>
                        <span>Save & Request Document</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
$(document).ready(function() {
    // Approve document via AJAX
    $(document).on('click', '.doc-status-btn', function() {
        var docId = $(this).data('id');
        var status = $(this).data('status');
        var $btn = $(this);

        $btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Processing...');

        $.ajax({
            url: "{{ route('user.document.status.ajax') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: docId,
                status: status
            },
            dataType: "json",
            success: function(resp) {
                $btn.prop('disabled', false).html('<i class="bx bx-check"></i> Approve');
                if (resp.status) {
                    $('#doc_status_badge_' + docId).html(
                        '<span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-25 fs-8"><i class="bx bx-check-circle me-1"></i>Approved</span>'
                    );
                    $('#doc_rejection_box_' + docId).slideUp(200);
                    if (typeof toastr !== 'undefined') {
                        toastr.success(resp.message || 'Document approved successfully!');
                    } else {
                        alert(resp.message || 'Document approved successfully!');
                    }
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="bx bx-check"></i> Approve');
                var err = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error updating document';
                if (typeof toastr !== 'undefined') {
                    toastr.error(err);
                } else {
                    alert(err);
                }
            }
        });
    });

    // Open reject modal
    $(document).on('click', '.doc-reject-modal-btn', function() {
        var docId = $(this).data('id');
        var docTitle = $(this).data('title');
        $('#modal_reject_doc_id').val(docId);
        $('#modal_reject_doc_title').text(docTitle);
        $('#modal_rejection_reason').val('');
        var rejectModal = new bootstrap.Modal(document.getElementById('docRejectModal'));
        rejectModal.show();
    });

    // Confirm reject in modal
    $('#confirmRejectDocBtn').on('click', function() {
        var docId = $('#modal_reject_doc_id').val();
        var reason = $('#modal_rejection_reason').val().trim();
        var $btn = $(this);

        if (!reason) {
            alert('Please specify a rejection reason.');
            return;
        }

        $btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Rejecting...');

        $.ajax({
            url: "{{ route('user.document.status.ajax') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: docId,
                status: 'rejected',
                rejection_reason: reason
            },
            dataType: "json",
            success: function(resp) {
                $btn.prop('disabled', false).html('<i class="bx bx-x fs-6"></i> Confirm Rejection');
                var modalEl = document.getElementById('docRejectModal');
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                if (resp.status) {
                    $('#doc_status_badge_' + docId).html(
                        '<span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-25 fs-8"><i class="bx bx-x-circle me-1"></i>Rejected</span>'
                    );
                    $('#doc_rejection_text_' + docId).text('Reason: ' + reason);
                    $('#doc_rejection_box_' + docId).slideDown(200);
                    if (typeof toastr !== 'undefined') {
                        toastr.warning(resp.message || 'Document marked as rejected.');
                    } else {
                        alert(resp.message || 'Document marked as rejected.');
                    }
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="bx bx-x fs-6"></i> Confirm Rejection');
                var err = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error rejecting document';
                if (typeof toastr !== 'undefined') {
                    toastr.error(err);
                } else {
                    alert(err);
                }
            }
        });
    });

    // Company Document Requirements Management
    function updateReqDocsBadge() {
        var total = $('.req-doc-checkbox').length;
        var checked = $('.req-doc-checkbox:checked').length;
        $('#selectedReqDocsBadge').html('<i class="bx bx-task me-1"></i>' + checked + ' of ' + total + ' Required');

        $('.req-doc-checkbox').each(function() {
            var docId = $(this).data('id');
            var $card = $('#req_doc_card_' + docId);
            if ($(this).is(':checked')) {
                $card.addClass('is-selected')
                     .removeClass('border-secondary border-opacity-20 bg-dark bg-opacity-25 border-info bg-info');
            } else {
                $card.removeClass('is-selected border-info bg-info')
                     .removeClass('border-secondary border-opacity-20 bg-dark bg-opacity-25');
            }
        });
    }

    // Initialize badge on load
    updateReqDocsBadge();

    // Checkbox change listener
    $(document).on('change', '.req-doc-checkbox', function() {
        updateReqDocsBadge();
    });

    // Select All
    $('#btnSelectAllReqDocs').on('click', function() {
        $('.req-doc-checkbox').prop('checked', true);
        updateReqDocsBadge();
    });

    // Deselect All
    $('#btnDeselectAllReqDocs').on('click', function() {
        $('.req-doc-checkbox').prop('checked', false);
        updateReqDocsBadge();
    });

    // Reset to Defaults
    $('#btnResetDefaultReqDocs').on('click', function() {
        $('.req-doc-checkbox').each(function() {
            var isDefault = $(this).data('default') == '1';
            $(this).prop('checked', isDefault);
        });
        updateReqDocsBadge();
    });

    // Send Document Request Notification to Client (Batch / Selected)
    $('#btnSendDocRequestNotification').on('click', function() {
        var $btn = $(this);
        var originalHtml = $btn.html();
        var selectedIds = $('.req-doc-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            if (!confirm('No documents are currently checked as required. Do you still want to notify the client to review their documentation?')) {
                return;
            }
        }

        $btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Sending Request...');

        $.ajax({
            url: "{{ route('user.company.documents.request', $user->id) }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                required_documents: selectedIds
            },
            dataType: "json",
            success: function(resp) {
                $btn.prop('disabled', false).html(originalHtml);
                if (resp.status) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(resp.message || 'Notification sent to client successfully!');
                    } else {
                        alert(resp.message || 'Notification sent to client successfully!');
                    }
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(originalHtml);
                var err = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error sending document request';
                if (typeof toastr !== 'undefined') {
                    toastr.error(err);
                } else {
                    alert(err);
                }
            }
        });
    });

    // Single Document Request Button
    $(document).on('click', '.single-doc-request-btn', function() {
        var $btn = $(this);
        var originalHtml = $btn.html();
        var docId = $btn.data('id');
        var docName = $btn.data('name');

        // Ensure this checkbox is checked and UI is updated
        var $chk = $('#req_doc_check_' + docId);
        if (!$chk.is(':checked')) {
            $chk.prop('checked', true);
            updateReqDocsBadge();
        }

        $btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Requesting...');

        $.ajax({
            url: "{{ route('user.company.documents.request', $user->id) }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                single_document_id: docId
            },
            dataType: "json",
            success: function(resp) {
                $btn.prop('disabled', false).html(originalHtml);
                if (resp.status) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(resp.message || 'Request sent to client for ' + docName);
                    } else {
                        alert(resp.message || 'Request sent to client for ' + docName);
                    }
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(originalHtml);
                var err = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error sending request';
                if (typeof toastr !== 'undefined') {
                    toastr.error(err);
                } else {
                    alert(err);
                }
            }
        });
    });

    // Add Custom Document Form Submission
    $('#addCustomDocForm').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $('#btnSaveCustomDoc');
        var originalBtnHtml = $btn.html();

        var nameEn = $('#custom_doc_name_en').val().trim();
        if (!nameEn) {
            alert('Please enter document name in English');
            return;
        }

        $btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Saving...');

        var formData = {
            _token: "{{ csrf_token() }}",
            name_en: nameEn,
            name_ar: $('#custom_doc_name_ar').val().trim(),
            description_en: $('#custom_doc_desc_en').val().trim(),
            is_required: $('#custom_doc_is_required').is(':checked') ? 1 : 0,
            send_notif: $('#custom_doc_send_notif').is(':checked') ? 1 : 0
        };

        $.ajax({
            url: "{{ route('user.custom.document.store', $user->id) }}",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function(resp) {
                $btn.prop('disabled', false).html(originalBtnHtml);
                if (resp.status && resp.document) {
                    var doc = resp.document;
                    var isReq = doc.is_required ? true : false;

                    var cardHtml = `
                        <div class="col-12 col-md-6" id="req_doc_col_${doc.id}">
                            <div class="p-3 rounded-3 border h-100 position-relative req-doc-card ${isReq ? 'is-selected' : ''}" id="req_doc_card_${doc.id}">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <div class="form-check form-switch m-0 pt-0.5">
                                        <input class="form-check-input req-doc-checkbox" 
                                               type="checkbox" 
                                               name="required_documents[]" 
                                               value="${doc.id}" 
                                               id="req_doc_check_${doc.id}"
                                               data-id="${doc.id}"
                                               data-default="1"
                                               data-is-custom="1"
                                               data-title-en="${doc.name_en}"
                                               data-title-ar="${doc.name_ar}"
                                               ${isReq ? 'checked' : ''}>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                            <label class="form-check-label fw-bold req-doc-title fs-7 mb-0 cursor-pointer d-block text-truncate" for="req_doc_check_${doc.id}">
                                                ${doc.name_en}
                                            </label>
                                            <span class="badge badge-st-custom fs-9"><i class="bx bx-bookmark-plus me-0.5"></i>Custom</span>
                                        </div>
                                        <small class="req-doc-sub d-block fs-8 mb-1">
                                            ${doc.name_ar}
                                        </small>
                                        <div class="d-flex align-items-center flex-wrap gap-1.5 mt-1">
                                            <span class="badge badge-st-custom fs-9">Additional Document</span>
                                            <span class="badge badge-st-notuploaded fs-9"><i class="bx bx-upload me-0.5"></i>Not Uploaded Yet</span>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0 d-flex align-items-center gap-1">
                                        <button type="button" 
                                                class="btn btn-xs btn-outline-info rounded-pill px-2.5 py-1 fs-9 single-doc-request-btn"
                                                data-id="${doc.id}"
                                                data-name="${doc.name_en}"
                                                title="Request client to upload this document specifically">
                                            <i class="bx bx-bell me-0.5"></i>Request
                                        </button>
                                        <button type="button" 
                                                class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1 fs-9 delete-custom-doc-btn"
                                                data-id="${doc.id}"
                                                data-name="${doc.name_en}"
                                                title="Delete this custom document requirement">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    $('#reqDocsGrid').append(cardHtml);
                    updateReqDocsBadge();

                    // Hide modal
                    var modalEl = document.getElementById('addCustomDocModal');
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    $form[0].reset();
                    $('#custom_doc_is_required').prop('checked', true);
                    $('#custom_doc_send_notif').prop('checked', true);

                    if (typeof toastr !== 'undefined') {
                        toastr.success(resp.message || 'Custom document added and client notified!');
                    } else {
                        alert(resp.message || 'Custom document added and client notified!');
                    }
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(originalBtnHtml);
                var err = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error adding custom document requirement';
                if (typeof toastr !== 'undefined') {
                    toastr.error(err);
                } else {
                    alert(err);
                }
            }
        });
    });

    // Delete Custom Document Requirement
    $(document).on('click', '.delete-custom-doc-btn', function() {
        var docId = $(this).data('id');
        var docName = $(this).data('name');
        var $btn = $(this);

        if (!confirm('Are you sure you want to remove the custom document requirement "' + docName + '"? Any uploaded files for this document will also be deleted.')) {
            return;
        }

        $btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i>');

        $.ajax({
            url: "/admin/user/{{ $user->id }}/custom-document/" + docId + "/delete",
            type: "DELETE",
            data: {
                _token: "{{ csrf_token() }}"
            },
            dataType: "json",
            success: function(resp) {
                if (resp.status) {
                    $('#req_doc_col_' + docId).fadeOut(300, function() {
                        $(this).remove();
                        updateReqDocsBadge();
                    });
                    if (typeof toastr !== 'undefined') {
                        toastr.success(resp.message || 'Custom document requirement deleted.');
                    } else {
                        alert(resp.message || 'Custom document requirement deleted.');
                    }
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="bx bx-trash"></i>');
                var err = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error deleting document';
                if (typeof toastr !== 'undefined') {
                    toastr.error(err);
                } else {
                    alert(err);
                }
            }
        });
    });
});
</script>

@endsection
