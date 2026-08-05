<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ $user->fname }} {{ $user->lname }} — SALASIL Digital Profile & Verification</title>
  <meta name="description" content="Official SALASIL Logistics Verified Digital Profile for {{ $user->fname }} {{ $user->lname }} ({{ $user->client_code }})." />

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

  <!-- Boxicons & Bootstrap 5 -->
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

  <style>
    :root {
      --navy-dark:  #080E24;
      --navy-mid:   #0D1535;
      --navy-card:  #141E47;
      --teal:       #4ECDC4;
      --teal-light: #72E8E0;
      --teal-dark:  #2BA8A0;
      --text-white: #F0F4FF;
      --text-muted: #8A9BC4;
      --border-glow:rgba(78, 205, 196, 0.25);
      --font-head:  'Outfit', sans-serif;
      --font-body:  'Inter', sans-serif;
    }

    body {
      background: radial-gradient(ellipse 80% 60% at 50% 10%, rgba(27,43,107,0.7) 0%, transparent 70%),
                  linear-gradient(160deg, #080E24 0%, #0D1535 50%, #080E24 100%);
      color: var(--text-white);
      font-family: var(--font-body);
      min-height: 100vh;
      padding-bottom: 60px;
    }

    .glass-card {
      background: rgba(20, 30, 71, 0.65);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 1px solid var(--border-glow);
      border-radius: 24px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
    }

    .brand-header {
      padding: 24px 0;
      border-bottom: 1px solid rgba(78, 205, 196, 0.15);
      margin-bottom: 40px;
    }
    .brand-header img {
      height: 48px;
      width: auto;
      filter: drop-shadow(0 0 12px rgba(78, 205, 196, 0.4));
    }

    .verified-badge-glow {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px 18px;
      background: rgba(78, 205, 196, 0.12);
      border: 1px solid rgba(78, 205, 196, 0.35);
      border-radius: 50px;
      font-size: 0.82rem;
      font-weight: 700;
      color: var(--teal);
      letter-spacing: 0.05em;
    }
    .verified-badge-glow .dot {
      width: 8px; height: 8px;
      background: var(--teal);
      border-radius: 50%;
      box-shadow: 0 0 10px var(--teal);
      animation: pulse 1.8s infinite;
    }
    @keyframes pulse { 0%,100%{opacity:1;transform:scale(1);} 50%{opacity:0.3;transform:scale(0.8);} }

    .avatar-ring {
      position: relative;
      width: 120px;
      height: 120px;
      margin: 0 auto 20px;
      border-radius: 50%;
      padding: 4px;
      background: linear-gradient(135deg, var(--teal), #1B2B6B);
      box-shadow: 0 0 30px rgba(78, 205, 196, 0.4);
    }
    .avatar-ring img {
      width: 100%; height: 100%;
      object-fit: cover;
      border-radius: 50%;
      border: 3px solid #080E24;
    }

    .user-name {
      font-family: var(--font-head);
      font-weight: 800;
      font-size: 2rem;
      color: #FFFFFF;
      margin-bottom: 6px;
    }

    .client-code-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 16px;
      background: rgba(8, 14, 36, 0.8);
      border: 1px solid rgba(78, 205, 196, 0.4);
      border-radius: 12px;
      font-family: monospace;
      font-weight: 700;
      font-size: 1.05rem;
      color: var(--teal);
      letter-spacing: 1px;
    }

    .section-title {
      font-family: var(--font-head);
      font-size: 1.15rem;
      font-weight: 700;
      color: var(--teal);
      border-bottom: 1px solid rgba(78, 205, 196, 0.15);
      padding-bottom: 12px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .info-label {
      font-size: 0.78rem;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--text-muted);
      font-weight: 600;
      margin-bottom: 4px;
    }
    .info-value {
      font-size: 0.98rem;
      font-weight: 600;
      color: #FFFFFF;
      word-break: break-word;
    }

    .doc-preview-card {
      padding: 14px;
      background: rgba(8, 14, 36, 0.5);
      border: 1px dashed rgba(78, 205, 196, 0.3);
      border-radius: 16px;
      text-align: center;
      transition: all 0.3s ease;
    }
    .doc-preview-card:hover {
      border-color: var(--teal);
      background: rgba(78, 205, 196, 0.08);
      transform: translateY(-3px);
    }

    .qr-container {
      background: #FFFFFF;
      padding: 16px;
      border-radius: 20px;
      display: inline-block;
      box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }
    .qr-container img {
      width: 160px; height: 160px;
    }

    .copy-btn {
      background: rgba(78, 205, 196, 0.15);
      border: 1px solid rgba(78, 205, 196, 0.3);
      color: var(--teal);
      transition: all 0.3s ease;
    }
    .copy-btn:hover {
      background: var(--teal);
      color: #080E24;
      box-shadow: 0 0 20px rgba(78, 205, 196, 0.5);
    }
  </style>
</head>
<body>

  <!-- Brand Navbar Header -->
  <header class="brand-header">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-3">
      <a href="/" class="d-flex align-items-center gap-2 text-decoration-none">
        <img src="/logo.png" alt="SALASIL Logo" />
      </a>
      <div class="verified-badge-glow">
        <span class="dot"></span>
        OFFICIAL VERIFIED DIGITAL PROFILE
      </div>
    </div>
  </header>

  <main class="container">
    <div class="row g-4 justify-content-center">
      
      <!-- Left Column: Hero Overview & QR Code -->
      <div class="col-12 col-lg-4">
        
        <!-- Main User Card -->
        <div class="glass-card p-4 text-center mb-4">
          <div class="avatar-ring">
            <img src="{{ (!empty($user->photo) && file_exists(public_path('upload/user_images/'.$user->photo))) ? url('upload/user_images/'.$user->photo) : url('upload/no_image.jpg') }}" alt="{{ $user->fname }} {{ $user->lname }}">
          </div>

          <h2 class="user-name">{{ $user->fname }} {{ $user->lname }}</h2>
          
          <div class="mb-3">
            <div class="client-code-pill">
              <i class="bx bx-barcode fs-5"></i>
              <span>{{ $user->client_code }}</span>
            </div>
          </div>

          <!-- Account Role Badge -->
          <div class="mb-3">
            @if($user->role == 'company_customer')
              <span class="badge px-3 py-2 rounded-pill fw-semibold" style="background: rgba(6, 182, 212, 0.2); color: #38BDF8; border: 1px solid rgba(6, 182, 212, 0.5);">
                <i class="bx bx-building-house me-1"></i> Corporate Client (شركة)
              </span>
            @elseif($user->role == 'individual_customer')
              <span class="badge px-3 py-2 rounded-pill fw-semibold" style="background: rgba(59, 130, 246, 0.2); color: #60A5FA; border: 1px solid rgba(59, 130, 246, 0.5);">
                <i class="bx bx-user me-1"></i> Individual Client (أفراد)
              </span>
            @elseif($user->role == 'driver')
              <span class="badge px-3 py-2 rounded-pill fw-semibold" style="background: rgba(245, 158, 11, 0.2); color: #F59E0B; border: 1px solid rgba(245, 158, 11, 0.5);">
                <i class="bx bx-car me-1"></i> Professional Driver (سائق)
              </span>
            @else
              <span class="badge px-3 py-2 rounded-pill fw-semibold" style="background: rgba(244, 63, 94, 0.2); color: #F43F5E; border: 1px solid rgba(244, 63, 94, 0.5);">
                <i class="bx bx-shield me-1"></i> System Administrator
              </span>
            @endif
          </div>

          <!-- Status Badge -->
          <div class="d-flex justify-content-center gap-2">
            @if($user->status === 'active')
              <span class="badge bg-success bg-opacity-20 text-success border border-success px-3 py-1.5 rounded-pill fs-7">
                <i class="bx bx-check-circle me-1"></i> Account Active
              </span>
            @elseif($user->status === 'pending')
              <span class="badge bg-warning bg-opacity-20 text-warning border border-warning px-3 py-1.5 rounded-pill fs-7">
                <i class="bx bx-time-five me-1"></i> Pending Verification
              </span>
            @else
              <span class="badge bg-danger bg-opacity-20 text-danger border border-danger px-3 py-1.5 rounded-pill fs-7">
                <i class="bx bx-block me-1"></i> {{ ucfirst($user->status) }}
              </span>
            @endif
          </div>
        </div>

        <!-- Live QR Code Card -->
        <div class="glass-card p-4 text-center">
          <h6 class="section-title justify-content-center mb-3">
            <i class="bx bx-qr-scan fs-4"></i>
            <span>Scan Digital QR Code</span>
          </h6>
          <p class="text-slate-400 fs-7 mb-3">Scan with any smartphone camera to instantly verify this profile.</p>

          <div class="qr-container mb-3">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode(request()->url()) }}" alt="QR Code">
          </div>

          <div>
            <button class="btn copy-btn rounded-3 px-4 py-2 fw-semibold fs-7" onclick="copyProfileUrl()">
              <i class="bx bx-copy me-1"></i> Copy Shareable Link
            </button>
          </div>
        </div>

      </div>

      <!-- Right Column: Full Profile Details -->
      <div class="col-12 col-lg-8">
        
        <!-- Personal & Contact Details Card -->
        <div class="glass-card p-4 mb-4">
          <h5 class="section-title">
            <i class="bx bx-id-card"></i>
            <span>Personal & Contact Information</span>
          </h5>

          <div class="row g-4">
            <div class="col-12 col-sm-6">
              <div class="info-label"><i class="bx bx-user me-1"></i> Full Name</div>
              <div class="info-value">{{ $user->fname }} {{ $user->lname }}</div>
            </div>

            <div class="col-12 col-sm-6">
              <div class="info-label"><i class="bx bx-envelope me-1"></i> Email Address</div>
              <div class="info-value">{{ $user->email ?? 'N/A' }}</div>
            </div>

            <div class="col-12 col-sm-6">
              <div class="info-label"><i class="bx bx-phone me-1"></i> Primary Phone Number</div>
              <div class="info-value text-info">{{ $user->country_code ?? '+966' }} {{ $user->phone ?? 'N/A' }}</div>
            </div>

            <div class="col-12 col-sm-6">
              <div class="info-label"><i class="bx bx-phone-call me-1"></i> Secondary Phone</div>
              <div class="info-value text-info">{{ $user->secondary_phone ?? 'N/A' }}</div>
            </div>

            <div class="col-12 col-sm-6">
              <div class="info-label"><i class="bx bx-calendar me-1"></i> Date of Birth</div>
              <div class="info-value">{{ $user->dateofbirth ? $user->dateofbirth->format('M d, Y') : 'N/A' }}</div>
            </div>

            <div class="col-12 col-sm-6">
              <div class="info-label"><i class="bx bx-globe me-1"></i> Country / City</div>
              <div class="info-value">
                @if($user->country || $user->city)
                  {{ $user->country ? $user->country->name_en : '' }}
                  @if($user->country && $user->city) - @endif
                  {{ $user->city ? $user->city->name_en : '' }}
                @else
                  N/A
                @endif
              </div>
            </div>

            <div class="col-12 col-sm-6">
              <div class="info-label"><i class="bx bx-map me-1"></i> Address</div>
              <div class="info-value">{{ $user->address ?? 'N/A' }}</div>
            </div>

            <div class="col-12 col-sm-6">
              <div class="info-label"><i class="bx bx-time me-1"></i> Member Since</div>
              <div class="info-value">{{ $user->created_at ? $user->created_at->format('F d, Y') : 'N/A' }}</div>
            </div>
          </div>
        </div>

        <!-- Corporate Client Extended Details (Company Profile) -->
        @if($user->role === 'company_customer' && $user->companyProfile)
        <div class="glass-card p-4 mb-4">
          <h5 class="section-title" style="color: #38BDF8;">
            <i class="bx bx-building-house"></i>
            <span>Corporate Company Profile Details</span>
          </h5>

          <div class="row g-4 mb-4">
            <div class="col-12 col-sm-6">
              <div class="info-label"><i class="bx bx-building me-1"></i> Company Legal Name</div>
              <div class="info-value fs-5 text-info">{{ $user->companyProfile->company_name }}</div>
            </div>

            <div class="col-12 col-sm-6">
              <div class="info-label"><i class="bx bx-file me-1"></i> Commercial Register (CR) Number</div>
              <div class="info-value font-monospace text-warning">{{ $user->companyProfile->commercial_register }}</div>
            </div>

            <div class="col-12 col-sm-6">
              <div class="info-label"><i class="bx bx-id-card me-1"></i> Civil ID / National ID</div>
              <div class="info-value font-monospace">{{ $user->companyProfile->civil_id }}</div>
            </div>

            <div class="col-12 col-sm-6">
              <div class="info-label"><i class="bx bx-receipt me-1"></i> Tax / VAT ID Number</div>
              <div class="info-value font-monospace">{{ $user->companyProfile->tax_number ?? 'N/A' }}</div>
            </div>

            <div class="col-12 col-sm-4">
              <div class="info-label"><i class="bx bx-user me-1"></i> Representative Name</div>
              <div class="info-value">{{ $user->companyProfile->representative_name ?? 'N/A' }}</div>
            </div>

            <div class="col-12 col-sm-4">
              <div class="info-label"><i class="bx bx-briefcase me-1"></i> Representative Position</div>
              <div class="info-value">{{ $user->companyProfile->representative_position ?? 'N/A' }}</div>
            </div>

            <div class="col-12 col-sm-4">
              <div class="info-label"><i class="bx bx-phone me-1"></i> Representative Phone</div>
              <div class="info-value">{{ $user->companyProfile->representative_phone ?? 'N/A' }}</div>
            </div>

            <div class="col-12 col-sm-6">
              <div class="info-label"><i class="bx bx-badge-check me-1"></i> CR Verification Status</div>
              <div class="info-value">
                @if($user->companyProfile->verification_status === 'verified')
                  <span class="badge bg-success bg-opacity-20 text-success border border-success px-3 py-1 rounded-pill">
                    <i class="bx bx-check-double me-1"></i> Officially Verified CR
                  </span>
                @elseif($user->companyProfile->verification_status === 'rejected')
                  <span class="badge bg-danger bg-opacity-20 text-danger border border-danger px-3 py-1 rounded-pill">
                    <i class="bx bx-x me-1"></i> Verification Rejected
                  </span>
                @else
                  <span class="badge bg-warning bg-opacity-20 text-warning border border-warning px-3 py-1 rounded-pill">
                    <i class="bx bx-time me-1"></i> Verification Pending
                  </span>
                @endif
              </div>
            </div>
          </div>

          <!-- CR Document Download -->
          @if($user->companyProfile->commercial_register_doc)
          <div class="doc-preview-card d-flex align-items-center justify-content-between p-3">
            <div class="d-flex align-items-center gap-3">
              <i class="bx bxs-file-pdf text-danger fs-1"></i>
              <div class="text-start">
                <span class="d-block fw-bold text-white fs-6">Commercial Register (CR) Document</span>
                <span class="text-slate-400 fs-7">Official Corporate Registration File</span>
              </div>
            </div>
            <a href="{{ url('upload/company_docs/' . $user->companyProfile->commercial_register_doc) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-3 px-3">
              <i class="bx bx-show me-1"></i> View Document
            </a>
          </div>
          @endif

        </div>
        @endif

        <!-- Driver Profile & Fleet Details (for Driver Role) -->
        @if($user->role === 'driver' && $user->driverProfile)
        <div class="glass-card p-4 mb-4">
          <h5 class="section-title" style="color: #F59E0B;">
            <i class="bx bx-car"></i>
            <span>Professional Driver Details</span>
          </h5>

          <div class="row g-4 mb-4">
            <div class="col-12 col-sm-6">
              <div class="info-label"><i class="bx bx-id-card me-1"></i> License Number</div>
              <div class="info-value font-monospace text-warning fs-5">{{ $user->driverProfile->license_number ?? 'N/A' }}</div>
            </div>

            <div class="col-12 col-sm-6">
              <div class="info-label"><i class="bx bx-star me-1"></i> Driver Rating</div>
              <div class="info-value text-warning fs-5">
                ★ {{ number_format($user->driverProfile->rating ?? 5.0, 1) }} / 5.0
              </div>
            </div>

            <div class="col-12 col-sm-6">
              <div class="info-label"><i class="bx bx-wifi me-1"></i> Availability Status</div>
              <div class="info-value">
                @if($user->driverProfile->availability_status === 'available')
                  <span class="badge bg-success bg-opacity-20 text-success border border-success px-3 py-1 rounded-pill">Available</span>
                @elseif($user->driverProfile->availability_status === 'busy')
                  <span class="badge bg-danger bg-opacity-20 text-danger border border-danger px-3 py-1 rounded-pill">Busy On Trip</span>
                @else
                  <span class="badge bg-secondary bg-opacity-20 text-secondary border border-secondary px-3 py-1 rounded-pill">Offline</span>
                @endif
              </div>
            </div>

            <div class="col-12 col-sm-6">
              <div class="info-label"><i class="bx bx-shield-check me-1"></i> Driver Verification</div>
              <div class="info-value">
                @if($user->driverProfile->verification_status === 'verified')
                  <span class="badge bg-success bg-opacity-20 text-success border border-success px-3 py-1 rounded-pill">Verified Driver</span>
                @else
                  <span class="badge bg-warning bg-opacity-20 text-warning border border-warning px-3 py-1 rounded-pill">Pending Verification</span>
                @endif
              </div>
            </div>
          </div>

          <!-- Driver Documents Grid -->
          <div class="row g-3 mb-4">
            @if($user->driverProfile->license_photo)
            <div class="col-12 col-md-4">
              <div class="doc-preview-card">
                <i class="bx bx-image text-info fs-2 mb-2 d-block"></i>
                <span class="d-block fw-semibold fs-7 text-white mb-2">Driver License Photo</span>
                <a href="{{ url('upload/driver_docs/' . $user->driverProfile->license_photo) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-3 w-100">View File</a>
              </div>
            </div>
            @endif

            @if($user->driverProfile->truck_registration_photo)
            <div class="col-12 col-md-4">
              <div class="doc-preview-card">
                <i class="bx bx-image text-info fs-2 mb-2 d-block"></i>
                <span class="d-block fw-semibold fs-7 text-white mb-2">Truck Registration Photo</span>
                <a href="{{ url('upload/driver_docs/' . $user->driverProfile->truck_registration_photo) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-3 w-100">View File</a>
              </div>
            </div>
            @endif

            @if($user->driverProfile->civil_id_photo)
            <div class="col-12 col-md-4">
              <div class="doc-preview-card">
                <i class="bx bx-image text-info fs-2 mb-2 d-block"></i>
                <span class="d-block fw-semibold fs-7 text-white mb-2">Civil ID Photo</span>
                <a href="{{ url('upload/driver_docs/' . $user->driverProfile->civil_id_photo) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-3 w-100">View File</a>
              </div>
            </div>
            @endif
          </div>

          <!-- Assigned Trucks Section -->
          @if($user->trucks && count($user->trucks) > 0)
          <h6 class="text-info fw-bold mb-3"><i class="bx bx-truck me-1"></i> Assigned Fleet Vehicles</h6>
          <div class="table-responsive">
            <table class="table table-dark table-striped align-middle rounded-3 overflow-hidden">
              <thead>
                <tr>
                  <th>Vehicle Type</th>
                  <th>Sub-Type</th>
                  <th>Plate Number</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach($user->trucks as $truck)
                <tr>
                  <td class="fw-bold">{{ $truck->truckType->name_en ?? 'N/A' }}</td>
                  <td>{{ $truck->truckSubType->name_en ?? 'General' }}</td>
                  <td><span class="badge bg-dark text-warning font-monospace">{{ $truck->plate_number ?? 'N/A' }}</span></td>
                  <td>
                    @if($truck->is_verified)
                      <span class="badge bg-success bg-opacity-20 text-success border border-success px-2.5 py-1">Verified</span>
                    @else
                      <span class="badge bg-warning bg-opacity-20 text-warning border border-warning px-2.5 py-1">Unverified</span>
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @endif

        </div>
        @endif

        <!-- Trust & Verification Footer Seal -->
        <div class="glass-card p-4 text-center">
          <div class="d-flex align-items-center justify-content-center gap-2 mb-2 text-info fs-4 fw-bold">
            <i class="bx bx-shield-quarter"></i>
            <span>SALASIL E-LOGISTICS VERIFIED SEAL</span>
          </div>
          <p class="text-slate-400 fs-7 mb-0">
            This digital profile is cryptographically generated and issued by the SALASIL Freight & Logistics Platform.
          </p>
        </div>

      </div>

    </div>
  </main>

  <script>
    function copyProfileUrl() {
      navigator.clipboard.writeText(window.location.href).then(() => {
        alert('Public Profile URL copied to clipboard!');
      });
    }
  </script>
</body>
</html>
