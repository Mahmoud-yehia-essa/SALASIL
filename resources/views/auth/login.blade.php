<!doctype html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SALASIL - Sign In | Control Panel</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Plugins & Styles -->
    <link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/assets/css/bootstrap-extended.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/assets/css/icons.css') }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">

    <style>
        :root {
            --salasil-navy: #16284F;
            --salasil-navy-dark: #0A142F;
            --salasil-cyan: #06B6D4;
            --salasil-cyan-light: #38BDF8;
            --salasil-teal: #14B8A6;
            --salasil-accent: #0284C7;
            --salasil-bg: #0F172A;
            --salasil-card-bg: rgba(30, 41, 59, 0.75);
            --salasil-border: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--salasil-bg);
            color: #F8FAFC;
            min-height: 100vh;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6, .brand-title {
            font-family: 'Outfit', sans-serif;
        }

        /* Left Side Hero Banner */
        .salasil-hero-section {
            background: linear-gradient(135deg, rgba(10, 20, 47, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%), 
                        radial-gradient(circle at 10% 20%, rgba(6, 182, 212, 0.15) 0%, transparent 50%),
                        radial-gradient(circle at 90% 80%, rgba(2, 132, 199, 0.15) 0%, transparent 50%);
            position: relative;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3.5rem;
            overflow: hidden;
        }

        /* Mesh Grid Animation in Background */
        .salasil-hero-section::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        .hero-glow-orb {
            position: absolute;
            width: 350px;
            height: 350px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(6, 182, 212, 0.25) 0%, rgba(2, 132, 199, 0.05) 70%, transparent 100%);
            filter: blur(50px);
            animation: orbFloat 10s ease-in-out infinite alternate;
            pointer-events: none;
        }

        @keyframes orbFloat {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(40px, -30px) scale(1.15); }
        }

        /* Logo Glow Container */
        .salasil-logo-hero {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 18px 30px;
            background: rgba(255, 255, 255, 0.96);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), 0 0 30px rgba(6, 182, 212, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }

        .salasil-logo-hero:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5), 0 0 40px rgba(6, 182, 212, 0.45);
        }

        .salasil-logo-hero img {
            max-height: 70px;
            width: auto;
            object-fit: contain;
            image-rendering: -webkit-optimize-contrast;
            display: block;
        }

        /* Feature Cards */
        .feature-badge {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .feature-badge:hover {
            background: rgba(255, 255, 255, 0.09);
            border-color: rgba(6, 182, 212, 0.4);
            transform: translateX(6px);
        }

        .feature-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--salasil-navy) 0%, var(--salasil-accent) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #06B6D4;
            box-shadow: 0 8px 16px rgba(6, 182, 212, 0.2);
        }

        /* Right Side Auth Container */
        .auth-right-section {
            background-color: var(--salasil-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.5rem;
            position: relative;
        }

        .login-card {
            background: var(--salasil-card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--salasil-border);
            border-radius: 24px;
            padding: 3rem 2.5rem;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5), 0 0 1px rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 10;
        }

        .login-header-logo {
            background: #FFFFFF;
            padding: 12px 24px;
            border-radius: 16px;
            display: inline-block;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            margin-bottom: 1.5rem;
        }

        .login-header-logo img {
            height: 48px;
            width: auto;
            object-fit: contain;
            image-rendering: -webkit-optimize-contrast;
            display: block;
        }

        /* Form Labels & Inputs */
        .form-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #E2E8F0;
            margin-bottom: 0.5rem;
            letter-spacing: 0.3px;
        }

        .input-group-custom {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-group-custom .input-icon {
            position: absolute;
            left: 16px;
            font-size: 20px;
            color: #64748B;
            transition: color 0.3s ease;
            z-index: 5;
            pointer-events: none;
        }

        .form-control-custom {
            width: 100%;
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1.5px solid rgba(255, 255, 255, 0.12) !important;
            border-radius: 12px !important;
            padding: 13px 16px 13px 48px !important;
            font-size: 0.95rem;
            color: #F8FAFC !important;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }

        .form-control-custom::placeholder {
            color: #64748B !important;
        }

        .form-control-custom:focus {
            outline: none !important;
            border-color: var(--salasil-cyan) !important;
            background: rgba(15, 23, 42, 0.85) !important;
            box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.25) !important;
        }

        .input-group-custom:focus-within .input-icon {
            color: var(--salasil-cyan);
        }

        /* Password Toggle */
        .password-toggle-btn {
            position: absolute;
            right: 14px;
            background: transparent;
            border: none;
            color: #64748B;
            font-size: 20px;
            cursor: pointer;
            z-index: 5;
            padding: 4px;
            transition: color 0.2s ease;
        }

        .password-toggle-btn:hover {
            color: var(--salasil-cyan);
        }

        /* Checkbox & Links */
        .form-check-input {
            background-color: rgba(15, 23, 42, 0.6);
            border-color: rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: var(--salasil-cyan);
            border-color: var(--salasil-cyan);
        }

        .form-check-label {
            color: #94A3B8;
            font-size: 0.875rem;
            cursor: pointer;
        }

        .forgot-password-link {
            color: var(--salasil-cyan-light);
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .forgot-password-link:hover {
            color: #38BDF8;
            text-decoration: underline;
        }

        /* Submit Button */
        .btn-salasil-primary {
            background: linear-gradient(135deg, #16284F 0%, #0284C7 100%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 14px 24px;
            font-size: 1rem;
            font-weight: 700;
            color: #FFFFFF;
            letter-spacing: 0.5px;
            box-shadow: 0 10px 25px rgba(2, 132, 199, 0.35);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            width: 100%;
        }

        .btn-salasil-primary:hover {
            background: linear-gradient(135deg, #0F1C3F 0%, #06B6D4 100%);
            box-shadow: 0 14px 30px rgba(6, 182, 212, 0.45);
            transform: translateY(-2px);
            color: #FFFFFF;
        }

        .btn-salasil-primary:active {
            transform: translateY(0);
            box-shadow: 0 6px 15px rgba(2, 132, 199, 0.25);
        }

        .footer-copyright {
            color: #64748B;
            font-size: 0.8125rem;
            text-align: center;
            margin-top: 2rem;
        }
    </style>
</head>

<body>
    <div class="container-fluid p-0">
        <div class="row g-0">

            <!-- Left Hero Section (Desktop View) -->
            <div class="col-lg-6 col-xl-7 d-none d-lg-flex salasil-hero-section">
                <div class="hero-glow-orb"></div>
                
                <!-- Hero Header / Logo -->
                <div class="z-2">
                    <div class="salasil-logo-hero">
                        <img src="{{ asset('logo.png') }}" alt="SALASIL Logo">
                    </div>
                </div>

                <!-- Hero Body Content -->
                <div class="z-2 my-auto py-5 max-w-xl">
                    <span class="badge rounded-pill bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2 mb-3 fw-bold text-uppercase" style="letter-spacing: 1.5px;">
                        Enterprise Logistics Platform
                    </span>
                    <h1 class="display-4 fw-extrabold text-white mb-3" style="line-height: 1.2;">
                        Smart Shipping and Logistics
                    </h1>
                    <p class="lead text-slate-300 mb-4" style="color: #94A3B8; font-size: 1.15rem; line-height: 1.6;">
                        An integrated dashboard to manage shipping operations, monitor drivers and customers, and track in real-time.
                    </p>

                    <!-- Feature Badges -->
                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <div class="feature-badge d-flex align-items-center gap-3">
                                <div class="feature-icon-box">
                                    <i class="bx bx-radar"></i>
                                </div>
                                <div>
                                    <h6 class="text-white mb-0 fw-bold">Live Tracking</h6>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="feature-badge d-flex align-items-center gap-3">
                                <div class="feature-icon-box">
                                    <i class="bx bx-shield-quarter"></i>
                                </div>
                                <div>
                                    <h6 class="text-white mb-0 fw-bold">Secure Infrastructure</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hero Footer -->
                <div class="z-2 d-flex justify-content-between align-items-center text-slate-400" style="color: #64748B; font-size: 0.875rem;">
                    <span>&copy; {{ date('Y') }} SALASIL Logistics Network. All rights reserved.</span>
                </div>
            </div>

            <!-- Right Auth Section -->
            <div class="col-12 col-lg-6 col-xl-5 auth-right-section">
                <div class="login-card">
                    <!-- Brand Logo -->
                    <div class="text-center">
                        <div class="login-header-logo">
                            <img src="{{ asset('logo.png') }}" alt="SALASIL Logo">
                        </div>
                        <h3 class="fw-bold text-white mb-1">Sign In</h3>
                        <p style="color: #94A3B8; font-size: 0.925rem;" class="mb-4">
                            Welcome back! Please enter your credentials to proceed.
                        </p>
                    </div>

                    <!-- Login Form -->
                    <form method="POST" action="{{ route('login') }}" class="row g-3">
                        @csrf

                        <!-- Email Input -->
                        <div class="col-12">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group-custom">
                                <i class="bx bx-envelope input-icon"></i>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       required 
                                       autofocus 
                                       class="form-control-custom" 
                                       placeholder="name@company.com">
                            </div>
                        </div>

                        <!-- Password Input -->
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="password" class="form-label mb-0">Password</label>
                            </div>
                            <div class="input-group-custom">
                                <i class="bx bx-lock-alt input-icon"></i>
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       required 
                                       class="form-control-custom" 
                                       placeholder="Enter your password">
                                <button type="button" id="togglePasswordBtn" class="password-toggle-btn" aria-label="Toggle password visibility">
                                    <i class="bx bx-hide" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Remember Me -->
                        <div class="col-12 d-flex align-items-center justify-content-between mt-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
                                <label class="form-check-label ms-1" for="remember_me">
                                    Keep me signed in
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-salasil-primary d-flex align-items-center justify-content-center gap-2">
                                <span>Sign In to Dashboard</span>
                                <i class="bx bx-right-arrow-alt fs-4"></i>
                            </button>
                        </div>
                    </form>

                    <div class="footer-copyright">
                        Powered by <strong>SALASIL</strong> Logistics Ecosystem
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('backend/assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('backend/assets/js/jquery.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- Password Toggle Handler -->
    <script>
        $(document).ready(function () {
            $('#togglePasswordBtn').on('click', function (e) {
                e.preventDefault();
                const passwordInput = $('#password');
                const icon = $('#togglePasswordIcon');
                
                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    icon.removeClass('bx-hide').addClass('bx-show');
                } else {
                    passwordInput.attr('type', 'password');
                    icon.removeClass('bx-show').addClass('bx-hide');
                }
            });
        });

        @if(Session::has('message'))
            var type = "{{ Session::get('alert-type','info') }}";
            switch(type){
                case 'info':
                    toastr.info("{{ Session::get('message') }}");
                    break;
                case 'success':
                    toastr.success("{{ Session::get('message') }}");
                    break;
                case 'warning':
                    toastr.warning("{{ Session::get('message') }}");
                    break;
                case 'error':
                    toastr.error("{{ Session::get('message') }}");
                    break;
            }
        @endif
    </script>
</body>
</html>
