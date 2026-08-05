<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SALASIL — Smart Freight & Logistics Platform. Coming Soon on App Store & Google Play.">
    <title>SALASIL — Smart Logistics Platform</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            height: 100%;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #080E1D;
            color: #F8FAFC;
            overflow-x: hidden;
        }

        /* ── Background ── */
        .bg-layer {
            position: fixed;
            inset: 0;
            z-index: 0;
            background:
                radial-gradient(ellipse 70% 50% at 50% 0%, rgba(6,182,212,0.14) 0%, transparent 60%),
                radial-gradient(ellipse 40% 40% at 85% 90%, rgba(2,132,199,0.1) 0%, transparent 55%),
                radial-gradient(ellipse 30% 30% at 10% 80%, rgba(20,184,166,0.08) 0%, transparent 55%);
        }

        /* subtle grid */
        .bg-grid {
            position: fixed;
            inset: 0;
            z-index: 0;
            background-image:
                linear-gradient(rgba(6,182,212,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(6,182,212,0.04) 1px, transparent 1px);
            background-size: 56px 56px;
        }

        /* ── Wrapper ── */
        .page {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 24px;
            text-align: center;
        }

        /* ── Logo block ── */
        .logo-block {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            margin-bottom: 48px;
            opacity: 0;
            animation: fadeUp 0.8s ease 0.1s forwards;
        }
        .logo-img-wrap {
            background: #FFFFFF;
            border-radius: 18px;
            padding: 12px 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.4);
        }
        .logo-img-wrap img { height: 52px; width: auto; }
        .logo-name {
            font-family: 'Outfit', sans-serif;
            font-size: 2.4rem;
            font-weight: 900;
            letter-spacing: -0.03em;
            color: #fff;
        }

        /* ── Tagline pill ── */
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 18px;
            background: rgba(6,182,212,0.1);
            border: 1px solid rgba(6,182,212,0.3);
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
            color: #38BDF8;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 28px;
            opacity: 0;
            animation: fadeUp 0.8s ease 0.25s forwards;
        }
        .pill-dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: #06B6D4;
            box-shadow: 0 0 8px #06B6D4;
            animation: blink 1.5s ease-in-out infinite;
        }
        @keyframes blink { 0%,100% { opacity:1; } 50% { opacity:0.3; } }

        /* ── Headline ── */
        h1 {
            font-family: 'Outfit', sans-serif;
            font-size: clamp(2.6rem, 6vw, 4.8rem);
            font-weight: 900;
            letter-spacing: -0.03em;
            line-height: 1.08;
            max-width: 720px;
            margin-bottom: 22px;
            opacity: 0;
            animation: fadeUp 0.9s ease 0.4s forwards;
        }
        h1 .accent {
            background: linear-gradient(135deg, #38BDF8, #14B8A6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ── Subtitle ── */
        .subtitle {
            font-size: 1.05rem;
            color: #94A3B8;
            max-width: 480px;
            line-height: 1.7;
            margin-bottom: 56px;
            font-weight: 400;
            opacity: 0;
            animation: fadeUp 0.9s ease 0.55s forwards;
        }

        /* ── Divider line ── */
        .divider {
            width: 48px;
            height: 2px;
            background: linear-gradient(90deg, #06B6D4, #14B8A6);
            border-radius: 2px;
            margin: 0 auto 56px;
            opacity: 0;
            animation: fadeUp 0.8s ease 0.65s forwards;
        }

        /* ── App store section ── */
        .app-section {
            opacity: 0;
            animation: fadeUp 0.9s ease 0.75s forwards;
        }
        .coming-soon-label {
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #64748B;
            margin-bottom: 20px;
        }
        .store-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            align-items: center;
        }
        .store-badge-wrap {
            position: relative;
            display: inline-block;
            transition: transform 0.3s ease;
        }
        .store-badge-wrap:hover { transform: translateY(-3px); }
        .store-badge-wrap img {
            height: 58px;
            width: auto;
            border-radius: 10px;
            display: block;
            filter: drop-shadow(0 4px 12px rgba(0,0,0,0.5));
        }
        .store-badge-wrap .soon-ribbon {
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #06B6D4, #14B8A6);
            color: #0F172A;
            font-family: 'Outfit', sans-serif;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding: 3px 12px;
            border-radius: 999px;
            white-space: nowrap;
            box-shadow: 0 2px 8px rgba(6,182,212,0.5);
        }

        /* ── Features row ── */
        .features {
            display: flex;
            justify-content: center;
            gap: 32px;
            flex-wrap: wrap;
            margin-top: 64px;
            opacity: 0;
            animation: fadeUp 0.9s ease 0.9s forwards;
        }
        .feature {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.83rem;
            color: #64748B;
            font-weight: 500;
        }
        .feature-icon {
            width: 30px; height: 30px;
            border-radius: 8px;
            background: rgba(6,182,212,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
        }

        /* ── Footer ── */
        .footer-note {
            position: fixed;
            bottom: 24px;
            left: 0; right: 0;
            text-align: center;
            font-size: 0.78rem;
            color: #334155;
            opacity: 0;
            animation: fadeUp 0.8s ease 1.1s forwards;
        }
        .footer-note a { color: #06B6D4; text-decoration: none; }

        /* ── Keyframes ── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Responsive ── */
        @media (max-width: 480px) {
            .store-buttons { flex-direction: column; align-items: center; }
            .store-btn { width: 100%; max-width: 280px; }
            h1 { font-size: 2.4rem; }
        }
    </style>
</head>
<body>

<div class="bg-layer"></div>
<div class="bg-grid"></div>

<div class="page">

    <!-- Logo -->
    <div class="logo-block">
        <div class="logo-img-wrap">
            <img src="/logo.png" alt="SALASIL Logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
            <span style="display:none; font-family:'Outfit',sans-serif; font-size:1.1rem; font-weight:900; color:#06B6D4;">SL</span>
        </div>
        <span class="logo-name">SALASIL</span>
    </div>

    <!-- Pill -->
    <div class="pill">
        <span class="pill-dot"></span>
        Smart Freight & Logistics Platform
    </div>

    <!-- Headline -->
    <h1>
        Shipment,<br>
        <span class="accent">Simplified.</span>
    </h1>

    <!-- Subtitle -->
    <p class="subtitle">
        Connect with verified drivers, track your cargo in real-time, and manage your entire supply chain — all from one powerful platform.
    </p>

    <!-- Divider -->
    <div class="divider"></div>

    <!-- App Store section -->
    <div class="app-section">
        <p class="coming-soon-label">App Coming Soon On</p>
        <div class="store-buttons">

            <!-- Apple App Store Badge -->
            <div class="store-badge-wrap">
                <img src="/apple.png" alt="Download on the App Store" style="height:58px; width:auto; border-radius:10px; display:block; filter: drop-shadow(0 4px 14px rgba(0,0,0,0.6));">
            </div>

            <!-- Google Play Badge -->
            <div class="store-badge-wrap">
                <img src="/google.png" alt="Get it on Google Play" style="height:58px; width:auto; border-radius:10px; display:block; filter: drop-shadow(0 4px 14px rgba(0,0,0,0.6));">
            </div>

        </div>
    </div>




</div>




<p style="position:fixed; bottom:20px; left:0; right:0; text-align:center; font-size:0.75rem; color:#334155; letter-spacing:0.03em;">
    © 2026 SALASIL Logistics Network. All rights reserved.
</p>

</body>

</html>
