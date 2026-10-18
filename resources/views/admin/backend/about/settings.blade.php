@extends('admin.master_admin')
@section('admin')

<!-- Page Header -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold header-title mb-1">
            <i class="bx bx-info-circle text-info me-2"></i>About Salasil App Settings
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Manage and customize official website URL, overview description, Terms of Service, Privacy Policy, and Social Media links.
        </p>
    </div>
</div>

<div class="row">
    <div class="col-12 col-xl-10 mx-auto">
        <form method="POST" action="{{ route('admin.about.settings.update') }}">
            @csrf

            <!-- 1. GENERAL APP CONTENT -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background-color: var(--card-bg, #1e293b);">
                <h5 class="fw-bold text-white mb-3 d-flex align-items-center gap-2">
                    <i class="bx bx-file-blank text-info"></i>
                    <span>General Content & Overview</span>
                </h5>

                <!-- Official Website URL -->
                <div class="mb-4">
                    <label class="form-label text-slate-300 fw-semibold">
                        <i class="bx bx-globe text-primary me-1"></i>Official Website URL <span class="text-danger">*</span>
                    </label>
                    <input type="url" name="about_website" class="form-control rounded-3 @error('about_website') is-invalid @enderror" value="{{ old('about_website', $settings['about_website']) }}" placeholder="https://salasel.com" required>
                    <small class="text-muted">Users tapping "Official Website" in the app will be directed to this link.</small>
                    @error('about_website')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Overview Description -->
                <div class="mb-4">
                    <label class="form-label text-slate-300 fw-semibold">
                        <i class="bx bx-detail text-info me-1"></i>App Overview Description <span class="text-danger">*</span>
                    </label>
                    <textarea name="about_description" rows="3" class="form-control rounded-3 @error('about_description') is-invalid @enderror" placeholder="Brief summary of Salasil..." required>{{ old('about_description', $settings['about_description']) }}</textarea>
                    <small class="text-muted">Displayed under the "Overview" section in the About Salasil screen.</small>
                    @error('about_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- 2. SOCIAL MEDIA CHANNELS -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background-color: var(--card-bg, #1e293b);">
                <h5 class="fw-bold text-white mb-3 d-flex align-items-center gap-2">
                    <i class="bx bx-share-alt text-warning"></i>
                    <span>Connect With Us (Social Media Links)</span>
                </h5>
                <p class="text-muted small mb-4">Clicking on any social media icon at the bottom of the "About Salasil" screen opens these configured links.</p>

                <div class="row g-3">
                    <!-- LinkedIn -->
                    <div class="col-md-6">
                        <label class="form-label text-slate-300 fw-semibold">
                            <i class="bx bxl-linkedin text-primary me-1"></i>LinkedIn URL
                        </label>
                        <input type="url" name="social_linkedin_url" class="form-control rounded-3" value="{{ old('social_linkedin_url', $settings['social_linkedin_url']) }}" placeholder="https://www.linkedin.com/company/...">
                    </div>

                    <!-- Twitter / X -->
                    <div class="col-md-6">
                        <label class="form-label text-slate-300 fw-semibold">
                            <i class="bx bxl-twitter text-info me-1"></i>X (Twitter) URL
                        </label>
                        <input type="url" name="social_twitter_url" class="form-control rounded-3" value="{{ old('social_twitter_url', $settings['social_twitter_url']) }}" placeholder="https://twitter.com/...">
                    </div>

                    <!-- Instagram -->
                    <div class="col-md-6">
                        <label class="form-label text-slate-300 fw-semibold">
                            <i class="bx bxl-instagram text-danger me-1"></i>Instagram URL
                        </label>
                        <input type="url" name="social_instagram_url" class="form-control rounded-3" value="{{ old('social_instagram_url', $settings['social_instagram_url']) }}" placeholder="https://instagram.com/...">
                    </div>

                    <!-- Facebook -->
                    <div class="col-md-6">
                        <label class="form-label text-slate-300 fw-semibold">
                            <i class="bx bxl-facebook text-primary me-1"></i>Facebook URL
                        </label>
                        <input type="url" name="social_facebook_url" class="form-control rounded-3" value="{{ old('social_facebook_url', $settings['social_facebook_url']) }}" placeholder="https://facebook.com/...">
                    </div>

                    <!-- Website Icon Link -->
                    <div class="col-12">
                        <label class="form-label text-slate-300 fw-semibold">
                            <i class="bx bx-link-external text-success me-1"></i>Web Portal URL (Globe Icon)
                        </label>
                        <input type="url" name="social_website_url" class="form-control rounded-3" value="{{ old('social_website_url', $settings['social_website_url']) }}" placeholder="https://salasel.com">
                    </div>
                </div>
            </div>

            <!-- 3. LEGAL POLICIES -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background-color: var(--card-bg, #1e293b);">
                <h5 class="fw-bold text-white mb-3 d-flex align-items-center gap-2">
                    <i class="bx bx-shield-quarter text-success"></i>
                    <span>Legal Documents (Terms & Privacy)</span>
                </h5>

                <!-- Terms of Service -->
                <div class="mb-4">
                    <label class="form-label text-slate-300 fw-semibold">
                        <i class="bx bx-file text-warning me-1"></i>Terms of Service <span class="text-danger">*</span>
                    </label>
                    <textarea name="about_terms_of_service" rows="8" class="form-control rounded-3 font-monospace @error('about_terms_of_service') is-invalid @enderror" placeholder="Write full terms of service..." required>{{ old('about_terms_of_service', $settings['about_terms_of_service']) }}</textarea>
                    <small class="text-muted">Full terms of service text presented when clicking "Terms of Service" in the app.</small>
                    @error('about_terms_of_service')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Privacy Policy -->
                <div class="mb-3">
                    <label class="form-label text-slate-300 fw-semibold">
                        <i class="bx bx-shield-quarter text-success me-1"></i>Privacy Policy <span class="text-danger">*</span>
                    </label>
                    <textarea name="about_privacy_policy" rows="8" class="form-control rounded-3 font-monospace @error('about_privacy_policy') is-invalid @enderror" placeholder="Write full privacy policy..." required>{{ old('about_privacy_policy', $settings['about_privacy_policy']) }}</textarea>
                    <small class="text-muted">Full data governance and privacy policy text presented when clicking "Privacy Policy" in the app.</small>
                    @error('about_privacy_policy')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-5">
                <button type="submit" class="btn btn-primary rounded-3 px-4 py-2.5 fw-semibold d-inline-flex align-items-center gap-2 shadow">
                    <i class="bx bx-check-double fs-5"></i>
                    <span>Save All Settings</span>
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
