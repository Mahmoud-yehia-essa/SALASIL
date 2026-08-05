@extends('admin.master_admin')
@section('admin')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<!-- Page Header -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold mb-1 header-title">
            <i class="bx bx-user-plus text-info me-2"></i>Add New Client
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Create a new client or company account on the SALASIL platform.
        </p>
    </div>
    <div>
        <a href="{{ route('all.owners') }}" class="btn btn-outline-info rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-list-ul fs-5"></i>
            <span>View All Clients</span>
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3 px-4">
                <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="bx bx-id-card text-info fs-4"></i>
                    <span>Client Information & Credentials</span>
                </h5>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ route('store.user') }}" enctype="multipart/form-data" id="myForm">
                    @csrf

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
                                           value="{{ old('fname') }}"
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
                                           value="{{ old('lname') }}">
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
                                           value="{{ old('dateofbirth') }}"
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
                                    <option value="en" {{ old('locale') == 'en' ? 'selected' : '' }}>English (en)</option>
                                    <option value="ar" {{ old('locale') == 'ar' ? 'selected' : '' }}>Arabic (ar)</option>
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
                                           value="{{ old('email') }}"
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
                                        <option value="+966" {{ old('country_code', '+966') == '+966' ? 'selected' : '' }}>+966 (KSA)</option>
                                        <option value="+971" {{ old('country_code') == '+971' ? 'selected' : '' }}>+971 (UAE)</option>
                                        <option value="+965" {{ old('country_code') == '+965' ? 'selected' : '' }}>+965 (KWT)</option>
                                        <option value="+968" {{ old('country_code') == '+968' ? 'selected' : '' }}>+968 (OMN)</option>
                                        <option value="+973" {{ old('country_code') == '+973' ? 'selected' : '' }}>+973 (BHR)</option>
                                        <option value="+962" {{ old('country_code') == '+962' ? 'selected' : '' }}>+962 (JOR)</option>
                                        <option value="+20" {{ old('country_code') == '+20' ? 'selected' : '' }}>+20 (EGY)</option>
                                    </select>
                                    <input type="text" 
                                           name="phone" 
                                           id="phone" 
                                           class="form-control" 
                                           placeholder="500000000"
                                           value="{{ old('phone') }}">
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
                                           value="{{ old('secondary_phone') }}">
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
                                            <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
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
                                          placeholder="Enter city, district, street address...">{{ old('address') }}</textarea>
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
                                    <option value="individual_customer" {{ old('role') == 'individual_customer' ? 'selected' : '' }}>Individual Client</option>
                                    <option value="company_customer" {{ old('role') == 'company_customer' ? 'selected' : '' }}>Company / Corporate</option>
                                    <option value="driver" {{ old('role') == 'driver' ? 'selected' : '' }}>Driver</option>
                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>System Administrator</option>
                                </select>
                            </div>

                            <!-- Account Status -->
                            <div class="col-12 col-md-4">
                                <label for="status" class="form-label fw-semibold">Account Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending Review</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="banned" {{ old('status') == 'banned' ? 'selected' : '' }}>Banned</option>
                                </select>
                            </div>

                            <!-- Password -->
                            <div class="col-12 col-md-4">
                                <label for="password" class="form-label fw-semibold">Access Password <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="password" 
                                           name="password" 
                                           id="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           style="padding-left: 48px !important; padding-right: 48px !important;"
                                           placeholder="••••••••"
                                           required>
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

                    <hr class="border-secondary opacity-25 my-4">

                    <!-- Dynamic Corporate Company Profile Section -->
                    <div id="companyProfileSection" style="display: none;" class="p-3 mb-4 rounded-4 border border-info border-opacity-25 bg-info bg-opacity-10">
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
                                           value="{{ old('company_name') }}">
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
                                           value="{{ old('commercial_register') }}">
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
                                           value="{{ old('civil_id') }}">
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
                                           value="{{ old('tax_number') }}">
                                    <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-receipt fs-5"></i>
                                    </span>
                                </div>
                                @error('tax_number')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- CR Document Upload -->
                            <div class="col-12 col-md-6">
                                <label for="commercial_register_doc" class="form-label fw-semibold">Upload Commercial Register Doc</label>
                                <input type="file" 
                                       name="commercial_register_doc" 
                                       id="commercial_register_doc" 
                                       class="form-control live-doc-picker"
                                       data-preview-box="#cr_doc_preview"
                                       data-title="Commercial Register Document"
                                       accept=".pdf,.jpg,.jpeg,.png,.webp">
                                <small class="text-slate-400 d-block mt-1">Allowed formats: PDF, JPG, PNG, WEBP (Max 5MB)</small>
                                <div id="cr_doc_preview" class="mt-2"></div>
                                @error('commercial_register_doc')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Verification Status -->
                            <div class="col-12 col-md-6">
                                <label for="verification_status" class="form-label fw-semibold">Verification Status</label>
                                <select name="verification_status" id="verification_status" class="form-select">
                                    <option value="pending" {{ old('verification_status', 'pending') == 'pending' ? 'selected' : '' }}>Pending Verification</option>
                                    <option value="verified" {{ old('verification_status') == 'verified' ? 'selected' : '' }}>Verified Company</option>
                                    <option value="rejected" {{ old('verification_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
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
                                       value="{{ old('representative_name') }}">
                            </div>

                            <!-- Representative Position -->
                            <div class="col-12 col-md-4">
                                <label for="representative_position" class="form-label fw-semibold">Representative Title</label>
                                <input type="text" 
                                       name="representative_position" 
                                       id="representative_position" 
                                       class="form-control" 
                                       placeholder="e.g. Operations Manager"
                                       value="{{ old('representative_position') }}">
                            </div>

                            <!-- Representative Phone -->
                            <div class="col-12 col-md-4">
                                <label for="representative_phone" class="form-label fw-semibold">Representative Phone</label>
                                <input type="text" 
                                       name="representative_phone" 
                                       id="representative_phone" 
                                       class="form-control" 
                                       placeholder="e.g. +966501234567"
                                       value="{{ old('representative_phone') }}">
                            </div>

                            <!-- Rejection Reason -->
                            <div class="col-12" id="rejectionReasonDiv" style="display: none;">
                                <label for="rejection_reason" class="form-label fw-semibold text-danger">Rejection Reason</label>
                                <textarea name="rejection_reason" 
                                          id="rejection_reason" 
                                          rows="2" 
                                          class="form-control border-danger border-opacity-50" 
                                          placeholder="Specify reason for rejecting this company registration...">{{ old('rejection_reason') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Fleet Driver Profile Section -->
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
                                           value="{{ old('license_number') }}">
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
                                <label for="wallet_balance" class="form-label fw-semibold">Initial Wallet Balance</label>
                                <div class="position-relative">
                                    <input type="number" 
                                           step="0.01"
                                           name="wallet_balance" 
                                           id="wallet_balance" 
                                           class="form-control" 
                                           style="padding-left: 48px !important;"
                                           placeholder="0.00"
                                           value="{{ old('wallet_balance', '0.00') }}">
                                    <span class="position-absolute text-warning" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-wallet fs-5"></i>
                                    </span>
                                </div>
                            </div>

                            <!-- Initial Driver Rating -->
                            <div class="col-12 col-md-3">
                                <label for="rating" class="form-label fw-semibold">Initial Driver Rating</label>
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
                                           value="{{ old('rating', '5.00') }}">
                                    <span class="position-absolute text-warning" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-star fs-5"></i>
                                    </span>
                                </div>
                            </div>

                            <!-- Driving License Photo -->
                            <div class="col-12 col-md-4">
                                <label for="license_photo" class="form-label fw-semibold">Upload Driving License</label>
                                <input type="file" 
                                       name="license_photo" 
                                       id="license_photo" 
                                       class="form-control live-doc-picker"
                                       data-preview-box="#license_photo_preview"
                                       data-title="Driving License Document"
                                       accept=".pdf,.jpg,.jpeg,.png,.webp">
                                <small class="text-slate-400 d-block mt-1">Allowed: PDF, JPG, PNG, WEBP</small>
                                <div id="license_photo_preview" class="mt-2"></div>
                            </div>

                            <!-- Truck Registration Photo -->
                            <div class="col-12 col-md-4">
                                <label for="truck_registration_photo" class="form-label fw-semibold">Upload Truck Registration</label>
                                <input type="file" 
                                       name="truck_registration_photo" 
                                       id="truck_registration_photo" 
                                       class="form-control live-doc-picker"
                                       data-preview-box="#truck_reg_photo_preview"
                                       data-title="Truck Registration Document"
                                       accept=".pdf,.jpg,.jpeg,.png,.webp">
                                <small class="text-slate-400 d-block mt-1">Allowed: PDF, JPG, PNG, WEBP</small>
                                <div id="truck_reg_photo_preview" class="mt-2"></div>
                            </div>

                            <!-- Civil ID Photo -->
                            <div class="col-12 col-md-4">
                                <label for="civil_id_photo" class="form-label fw-semibold">Upload Civil / National ID</label>
                                <input type="file" 
                                       name="civil_id_photo" 
                                       id="civil_id_photo" 
                                       class="form-control live-doc-picker"
                                       data-preview-box="#civil_id_photo_preview"
                                       data-title="Civil ID Document"
                                       accept=".pdf,.jpg,.jpeg,.png,.webp">
                                <small class="text-slate-400 d-block mt-1">Allowed: PDF, JPG, PNG, WEBP</small>
                                <div id="civil_id_photo_preview" class="mt-2"></div>
                            </div>

                            <!-- Availability Status -->
                            <div class="col-12 col-md-6">
                                <label for="availability_status" class="form-label fw-semibold">Availability Status</label>
                                <select name="availability_status" id="availability_status" class="form-select">
                                    <option value="offline" {{ old('availability_status', 'offline') == 'offline' ? 'selected' : '' }}>Offline</option>
                                    <option value="available" {{ old('availability_status') == 'available' ? 'selected' : '' }}>Available for Orders</option>
                                    <option value="busy" {{ old('availability_status') == 'busy' ? 'selected' : '' }}>Busy on Trip</option>
                                </select>
                            </div>

                            <!-- Driver Verification Status -->
                            <div class="col-12 col-md-6">
                                <label for="driver_verification_status" class="form-label fw-semibold">Verification Status</label>
                                <select name="driver_verification_status" id="driver_verification_status" class="form-select">
                                    <option value="pending" {{ old('driver_verification_status', 'pending') == 'pending' ? 'selected' : '' }}>Pending Verification</option>
                                    <option value="verified" {{ old('driver_verification_status') == 'verified' ? 'selected' : '' }}>Verified Driver</option>
                                    <option value="rejected" {{ old('driver_verification_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>

                            <!-- Driver Rejection Reason -->
                            <div class="col-12" id="driverRejectionReasonDiv" style="display: none;">
                                <label for="driver_rejection_reason" class="form-label fw-semibold text-danger">Driver Rejection Reason</label>
                                <textarea name="driver_rejection_reason" 
                                          id="driver_rejection_reason" 
                                          rows="2" 
                                          class="form-control border-danger border-opacity-50" 
                                          placeholder="Specify reason for rejecting this driver application...">{{ old('driver_rejection_reason') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <hr class="border-secondary opacity-25 my-4">

                    <!-- Profile Photo Section -->
                    <div class="mb-4">
                        <h6 class="text-info fw-bold text-uppercase fs-7 mb-3" style="letter-spacing: 0.5px;">
                            <i class="bx bx-image me-1"></i> Profile Photo / Avatar
                        </h6>
                        <div class="row align-items-center g-3">
                            <div class="col-12 col-md-8">
                                <label for="image" class="form-label fw-semibold">Choose Profile Picture</label>
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
                                         src="{{ url('upload/no_image.jpg') }}" 
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
                            <span>Save Client Profile</span>
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
                $('#driverProfileSection').slideUp(300);
                $('#company_name, #commercial_register, #civil_id').prop('required', true);
            } else if (selectedRole === 'driver') {
                $('#driverProfileSection').slideDown(300);
                $('#companyProfileSection').slideUp(300);
                $('#company_name, #commercial_register, #civil_id').prop('required', false);
            } else {
                $('#companyProfileSection').slideUp(300);
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

@endsection
