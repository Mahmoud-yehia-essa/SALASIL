<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invalid Driver Invitation - SALASIL Logistics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        body {
            background: #0F172A;
            color: #F8FAFC;
            font-family: system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .invalid-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%);
            border: 1px solid rgba(244, 63, 94, 0.3);
            border-radius: 24px;
            padding: 40px 32px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5);
        }
        .icon-box {
            width: 72px;
            height: 72px;
            background: rgba(244, 63, 94, 0.15);
            border: 1px solid rgba(244, 63, 94, 0.4);
            color: #F43F5E;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin: 0 auto 24px;
        }
    </style>
</head>
<body>
    <div class="invalid-card">
        <div class="icon-box">
            <i class="bx bx-error-alt"></i>
        </div>
        <h4 class="fw-bold mb-2 text-white">Invalid or Expired Invitation</h4>
        <p class="text-slate-400 mb-4" style="color: #94A3B8; font-size: 0.95rem;">
            This shipment invitation magic link is invalid, has expired, or was removed from the SALASIL platform.
        </p>
        <a href="/" class="btn btn-outline-light rounded-3 px-4 py-2 fw-semibold">
            <i class="bx bx-home me-1"></i> Back to Homepage
        </a>
    </div>
</body>
</html>
