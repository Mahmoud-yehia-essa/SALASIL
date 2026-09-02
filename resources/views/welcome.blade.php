<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SALASIL — Find a Truck for Your Shipment. Find a Driver. Forget the Payment Worries.">
    <title>SALASIL — Logistics Solution</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --bg: #070B18;
            --primary: #06B6D4;
            --primary-light: #38BDF8;
            --teal: #14B8A6;
            --text-main: #F8FAFC;
            --text-sub: #94A3B8;
            --card-bg: rgba(15, 23, 42, 0.6);
            --card-border: rgba(6, 182, 212, 0.18);
        }

        html, body {
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text-main);
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* ── Background Glow ── */
        .bg-glow {
            position: fixed;
            inset: 0;
            z-index: 0;
            background:
                radial-gradient(ellipse 70% 50% at 50% -10%, rgba(6, 182, 212, 0.15) 0%, transparent 60%),
                radial-gradient(ellipse 50% 40% at 85% 90%, rgba(2, 132, 199, 0.08) 0%, transparent 60%),
                radial-gradient(ellipse 40% 40% at 15% 85%, rgba(20, 184, 166, 0.06) 0%, transparent 60%);
            pointer-events: none;
        }

        .bg-grid {
            position: fixed;
            inset: 0;
            z-index: 0;
            background-image:
                linear-gradient(rgba(6, 182, 212, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(6, 182, 212, 0.03) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
        }

        /* ── Container ── */
        .wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 860px;
            padding: 50px 24px;
            margin: auto;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* ── Logo ── */
        .logo-block {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
            text-decoration: none;
            opacity: 0;
            animation: fadeUp 0.8s ease 0.1s forwards;
        }
        .logo-box {
            background: #FFFFFF;
            border-radius: 12px;
            padding: 6px 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(0,0,0,0.4);
        }
        .logo-box img { height: 36px; width: auto; display: block; }
        .logo-text {
            font-family: 'Outfit', sans-serif;
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #FFFFFF;
        }

        /* ── Main Headline ── */
        h1 {
            font-family: 'Outfit', sans-serif;
            font-size: clamp(2.2rem, 4.5vw, 3.4rem);
            font-weight: 900;
            letter-spacing: -0.03em;
            line-height: 1.18;
            max-width: 800px;
            margin-bottom: 18px;
            opacity: 0;
            animation: fadeUp 0.8s ease 0.25s forwards;
        }
        .accent {
            background: linear-gradient(135deg, #38BDF8 0%, #14B8A6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ── Subtitle ── */
        .subtitle {
            font-size: clamp(1.05rem, 2vw, 1.25rem);
            color: var(--text-sub);
            max-width: 650px;
            line-height: 1.65;
            margin-bottom: 36px;
            opacity: 0;
            animation: fadeUp 0.8s ease 0.35s forwards;
        }

        /* ── 3 Core Feature Items ── */
        .features-row {
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 100%;
            max-width: 680px;
            margin-bottom: 32px;
            opacity: 0;
            animation: fadeUp 0.8s ease 0.45s forwards;
        }
        .feature-item {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 14px;
            padding: 14px 22px;
            display: flex;
            align-items: center;
            gap: 14px;
            text-align: left;
            font-size: 1.05rem;
            font-weight: 600;
            color: #FFFFFF;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        .feature-item:hover {
            transform: translateX(6px);
            border-color: rgba(6, 182, 212, 0.5);
            background: rgba(15, 28, 56, 0.8);
            box-shadow: 0 4px 20px rgba(6, 182, 212, 0.15);
        }
        .feature-item span.emoji {
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        /* ── Partner Highlight ── */
        .partner-box {
            width: 100%;
            max-width: 680px;
            background: linear-gradient(135deg, rgba(14, 26, 52, 0.7) 0%, rgba(8, 14, 30, 0.8) 100%);
            border: 1px solid rgba(6, 182, 212, 0.22);
            border-radius: 16px;
            padding: 22px 28px;
            margin-bottom: 32px;
            text-align: center;
            backdrop-filter: blur(10px);
            opacity: 0;
            animation: fadeUp 0.8s ease 0.55s forwards;
            position: relative;
        }
        .partner-box::before {
            content: '';
            position: absolute;
            top: 0; left: 20%; right: 20%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #06B6D4, #14B8A6, transparent);
        }
        .partner-box p {
            font-size: 1.05rem;
            color: #E2E8F0;
            line-height: 1.65;
            font-weight: 400;
        }

        /* ── Brand Manifesto ── */
        .manifesto-box {
            margin-bottom: 36px;
            opacity: 0;
            animation: fadeUp 0.8s ease 0.65s forwards;
        }
        .manifesto-lead {
            font-size: 1.1rem;
            color: var(--text-sub);
            margin-bottom: 6px;
        }
        .manifesto-title {
            font-family: 'Outfit', sans-serif;
            font-size: clamp(1.6rem, 3vw, 2.2rem);
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        /* ── CTA & App Store ── */
        .cta-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            opacity: 0;
            animation: fadeUp 0.8s ease 0.75s forwards;
        }
        .cta-text {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            color: #38BDF8;
            letter-spacing: 0.02em;
        }
        .store-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            align-items: center;
            margin-top: 8px;
        }
        .store-badge-wrap {
            position: relative;
            display: inline-block;
            padding-top: 12px;
            transition: transform 0.3s ease;
            z-index: 1;
        }
        .store-badge-wrap:hover { transform: translateY(-3px); }
        .store-badge-wrap img {
            height: 48px;
            width: auto;
            border-radius: 9px;
            display: block;
            filter: drop-shadow(0 4px 12px rgba(0,0,0,0.5));
            position: relative;
            z-index: 1;
        }
        .soon-ribbon {
            position: absolute;
            top: 0px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #06B6D4, #14B8A6);
            color: #070B18;
            font-family: 'Outfit', sans-serif;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding: 3px 12px;
            border-radius: 999px;
            white-space: nowrap;
            box-shadow: 0 2px 10px rgba(6, 182, 212, 0.6);
            pointer-events: none;
            z-index: 20;
        }

        /* ── Footer ── */
        .footer {
            margin-top: 40px;
            font-size: 0.75rem;
            color: #334155;
            opacity: 0;
            animation: fadeUp 0.8s ease 0.85s forwards;
        }

        /* ── Animations ── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 600px) {
            .wrapper { padding: 36px 16px; }
            .feature-item { font-size: 0.95rem; padding: 12px 16px; }
            .partner-box { padding: 18px 20px; }
        }
    </style>
</head>
<body>

<div class="bg-glow"></div>
<div class="bg-grid"></div>

<div class="wrapper">

    <!-- Logo -->
    <a href="/" class="logo-block">
        <div class="logo-box">
            <img src="/logo.png" alt="SALASIL Logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
            <span style="display:none; font-family:'Outfit',sans-serif; font-size:1.1rem; font-weight:900; color:#06B6D4;">SL</span>
        </div>
        <span class="logo-text">SALASIL</span>
    </a>

    <!-- Headline (Exact Copy) -->
    <h1>
        Find a Truck for Your Shipment. Find a Driver.<br>
        <span class="accent">Forget the Payment Worries.</span>
    </h1>

    <!-- Subtitle (Exact Copy) -->
    <p class="subtitle">
        With Salasil, you don’t just find a driver — you find a complete logistics solution.
    </p>

    <!-- 3 Items (Exact Copy) -->
    <div class="features-row">
        <div class="feature-item">
            <span class="emoji">🚛</span>
            <span>Find the right truck for your shipment.</span>
        </div>
        <div class="feature-item">
            <span class="emoji">🤝</span>
            <span>Connect with reliable drivers and transport partners.</span>
        </div>
        <div class="feature-item">
            <span class="emoji">💳</span>
            <span>Load Now. Pay Later.</span>
        </div>
    </div>

    <!-- Partner Statement (Exact Copy) -->
    <div class="partner-box">
        <p>
            If you’re our partner, you don’t have to worry about paying immediately. Salasil handles the payment solution so you can focus on your business.
        </p>
    </div>

    <!-- Brand Statement (Exact Copy) -->
    <div class="manifesto-box">
        <p class="manifesto-lead">Salasil is not just a marketplace for finding drivers.</p>
        <h2 class="manifesto-title">
            <span class="accent">Salasil is a logistics solution.</span>
        </h2>
    </div>

    <!-- CTA & Apps (Exact Copy Tagline) -->
    <div class="cta-box">
        <div class="cta-text">Load Now. Pay Later.</div>
        
        <div class="store-buttons">
            <div class="store-badge-wrap">
                <span class="soon-ribbon">Coming Soon</span>
                <img src="/apple.png" alt="Download on the App Store">
            </div>
            <div class="store-badge-wrap">
                <span class="soon-ribbon">Coming Soon</span>
                <img src="/google.png" alt="Get it on Google Play">
            </div>
        </div>
    </div>

    <!-- Footer -->
    <p class="footer">
        © 2026 SALASIL Platform. All rights reserved.
    </p>

</div>

</body>
</html>
