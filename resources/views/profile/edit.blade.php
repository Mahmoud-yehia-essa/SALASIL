@extends('admin.master_admin')
@section('admin')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<!-- Page Header -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold mb-1 header-title">
            <i class="bx bx-edit text-info me-2"></i>Edit My Profile
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Update information and account credentials for: <span class="text-info fw-bold">{{ $user->fname ?: $user->name }} {{ $user->lname }}</span>
        </p>
    </div>
    <div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-info rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-grid-alt fs-5"></i>
            <span>Dashboard</span>
        </a>
    </div>
</div>

@if (session('status') === 'profile-updated')
    <div class="alert alert-success alert-dismissible fade show border-0 bg-success bg-opacity-10 text-success fw-semibold mb-4 rounded-3" role="alert">
        <i class="bx bx-check-circle me-2 fs-5 align-middle"></i>Profile information updated successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3 px-4">
                <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="bx bx-id-card text-info fs-4"></i>
                    <span>Edit Profile Details</span>
                </h5>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" id="editForm">
                    @csrf
                    @method('patch')

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
                                           value="{{ old('dateofbirth', $user->dateofbirth ? (is_string($user->dateofbirth) ? $user->dateofbirth : $user->dateofbirth->format('Y-m-d')) : '') }}"
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
                                <label for="phone" class="form-label fw-semibold">Phone Number</label>
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

                    <!-- Credentials & Password Section -->
                    <div class="mb-4">
                        <h6 class="text-info fw-bold text-uppercase fs-7 mb-3" style="letter-spacing: 0.5px;">
                            <i class="bx bx-shield-quarter me-1"></i> Account Role & Access Password
                        </h6>
                        <div class="row g-3">
                            <!-- Account Role -->
                            <div class="col-12 col-md-6">
                                <label for="role_display" class="form-label fw-semibold">Account Role</label>
                                <div class="position-relative">
                                    <input type="text" 
                                           id="role_display" 
                                           class="form-control text-capitalize fw-bold text-info" 
                                           style="padding-left: 48px !important;"
                                           value="{{ str_replace('_', ' ', $user->role ?: 'User') }}"
                                           disabled>
                                    <span class="position-absolute text-info" style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;">
                                        <i class="bx bx-badge-check fs-5"></i>
                                    </span>
                                </div>
                            </div>

                            <!-- Password -->
                            <div class="col-12 col-md-6">
                                <label for="password" class="form-label fw-semibold">New Password <small class="text-slate-400 fw-normal">(Leave blank to keep current)</small></label>
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
                                       class="form-control @error('photo') is-invalid @enderror"
                                       accept="image/*">
                                <small class="text-slate-400 d-block mt-1">
                                    Allowed formats: JPG, PNG, WEBP, GIF (Max size: 2MB).
                                </small>
                                @error('photo')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4 text-center text-md-start">
                                <label class="form-label fw-semibold d-block">Image Preview (Click to Enlarge)</label>
                                <div class="position-relative d-inline-block media-zoomable cursor-pointer" data-title="Profile Photo Preview" style="cursor: pointer;">
                                    <img id="showImage" 
                                         src="{{ (!empty($user->photo) && file_exists(public_path('upload/user_images/'.$user->photo))) ? url('upload/user_images/'.$user->photo) : ((!empty($user->photo) && file_exists(public_path('upload/admin_images/'.$user->photo))) ? url('upload/admin_images/'.$user->photo) : url('upload/no_image.jpg')) }}" 
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
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3 fw-semibold">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 fw-semibold d-inline-flex align-items-center gap-2">
                            <i class="bx bx-check-circle fs-5"></i>
                            <span>Update Profile</span>
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
