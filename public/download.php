<?php
/**
 * ============================================================================
 * منصة سلاسل (Salasil) - صفحة التحويل الذكي للتطبيق (Smart App Redirect)
 * ============================================================================
 * وظيفة الصفحة:
 * - التحقق تلقائياً من هوية ونظام تشغيل جهاز الزائر:
 *   1. إذا كان الجهاز Android -> يتم تحويله مباشرة إلى متجر Google Play.
 *   2. إذا كان الجهاز iPhone أو iPad -> يتم تحويله مباشرة إلى متجر App Store.
 *   3. إذا كان جهاز كمبيوتر (Desktop/PC/Mac) -> تظهر صفحة أنيقة تحتوي على
 *      QR Code للمسح بالهاتف وأزرار مباشرة لكلا المتجرين مع رابط الموقع.
 * - دعم خاص لأجهزة iPadOS الحديثة عبر فحص المتصفح.
 * - دعم التوجيه المباشر عبر المعامل: ?platform=android أو ?platform=ios
 * ============================================================================
 */

// ==========================================
// 1. الإعدادات وروابط المتاجر
// ==========================================

// معرف تطبيق الأندرويد في متجر Google Play
$androidPackageId = 'info.salasil';
$androidStoreUrl  = 'https://play.google.com/store/apps/details?id=' . $androidPackageId;

// معرف تطبيق الآيفون في متجر App Store (استبدل XXXXXXXXXX بمعرف التطبيق الخاص بك في App Store Connect)
$appleAppId       = 'XXXXXXXXXX';
$iosStoreUrl      = 'https://apps.apple.com/app/id' . $appleAppId;

// رابط موقع سلاسل الرئيسي
$websiteUrl       = 'https://salasil.info/';

// إذا كنت تريد تحويل أجهزة الكمبيوتر فوراً إلى الموقع الرئيسي، اجعل هذه القيمة true
// وإذا كنت تريد إظهار صفحة أنيقة بها الباركود وروابط المتاجر، اتركها false
$autoRedirectDesktopToWebsite = false;


// ==========================================
// 2. التحقق من المعاملات المباشرة (Query Params)
// ==========================================
if (isset($_GET['platform'])) {
    $p = strtolower(trim($_GET['platform']));
    if ($p === 'android' || $p === 'google') {
        header("Location: " . $androidStoreUrl, true, 302);
        exit;
    } elseif ($p === 'ios' || $p === 'apple' || $p === 'iphone') {
        header("Location: " . $iosStoreUrl, true, 302);
        exit;
    }
}


// ==========================================
// 3. فحص هوية ونظام الجهاز عبر User-Agent
// ==========================================
$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

$isAndroid = (bool) preg_match('/Android/i', $userAgent);
$isIos     = (bool) preg_match('/(iPhone|iPad|iPod)/i', $userAgent);

// أ) إذا كان الجهاز أندرويد -> توجيه فوري لـ Google Play
if ($isAndroid) {
    header("Location: " . $androidStoreUrl, true, 302);
    exit;
}

// ب) إذا كان الجهاز آيفون / آيباد -> توجيه فوري لـ App Store
if ($isIos) {
    header("Location: " . $iosStoreUrl, true, 302);
    exit;
}

// ج) إذا كان جهاز كمبيوتر ومفعّل خيار التحويل التلقائي للموقع
if ($autoRedirectDesktopToWebsite) {
    header("Location: " . $websiteUrl, true, 302);
    exit;
}

// ==========================================
// 4. إعدادات صفحة العرض لأجهزة الكمبيوتر (Desktop)
// ==========================================
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
$protocol = $isHttps ? "https://" : "http://";
$currentHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'salasil.info';
$currentUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/download.php';
$currentUrl = $protocol . $currentHost . $currentUri;

// رابط توليد الباركود QR Code
$qrCodeApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&margin=12&format=svg&data=' . urlencode($currentUrl);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>تحميل تطبيق سلاسل | Salasil App</title>
  <meta name="description" content="حمّل تطبيق سلاسل الآن على هاتفك الذكي عبر App Store أو Google Play لإدارة شحناتك وتتبع الأسطول بكل سهولة." />
  
  <!-- Open Graph -->
  <meta property="og:title" content="تحميل تطبيق سلاسل | Salasil App" />
  <meta property="og:description" content="حمّل تطبيق سلاسل على هاتفك الذكي عبر App Store أو Google Play." />
  <meta property="og:url" content="<?php echo htmlspecialchars($currentUrl, ENT_QUOTES, 'UTF-8'); ?>" />
  <meta property="og:type" content="website" />

  <!-- خطوط عربية حديثة -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet" />

  <!-- سكريبت الفحص الفوري من جهة المتصفح (Client-side Detection) -->
  <script>
    (function() {
      var ua = navigator.userAgent || navigator.vendor || window.opera;
      // التحقق من أجهزة iPadOS الحديثة التي تُعرّف نفسها كـ MacIntel
      var isIPadOS = navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1;
      var isIOS = /iPhone|iPad|iPod/i.test(ua) || isIPadOS;
      var isAndroid = /Android/i.test(ua);

      if (isIOS) {
        window.location.replace("<?php echo addslashes($iosStoreUrl); ?>");
      } else if (isAndroid) {
        window.location.replace("<?php echo addslashes($androidStoreUrl); ?>");
      }
    })();
  </script>

  <style>
    *, *::before, *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    :root {
      --bg-dark: #080E24;
      --bg-card: rgba(15, 26, 69, 0.75);
      --primary-blue: #1B2B6B;
      --button-blue: #1E60B4;
      --button-blue-hover: #164e96;
      --teal-accent: #4ECDC4;
      --text-white: #FFFFFF;
      --text-muted: #94A3B8;
      --border-color: rgba(78, 205, 196, 0.2);
    }

    body {
      font-family: 'Cairo', -apple-system, BlinkMacSystemFont, sans-serif;
      background: radial-gradient(circle at 50% 20%, #15224e 0%, var(--bg-dark) 80%);
      color: var(--text-white);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 24px;
      line-height: 1.6;
    }

    .main-container {
      width: 100%;
      max-width: 520px;
      background: var(--bg-card);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid var(--border-color);
      border-radius: 28px;
      padding: 40px 32px;
      text-align: center;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5), 0 0 40px rgba(30, 96, 180, 0.15);
      position: relative;
      overflow: hidden;
    }

    .main-container::before {
      content: '';
      position: absolute;
      top: -120px;
      left: 50%;
      transform: translateX(-50%);
      width: 240px;
      height: 240px;
      background: radial-gradient(circle, rgba(78, 205, 196, 0.25) 0%, transparent 70%);
      pointer-events: none;
    }

    .logo-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 72px;
      height: 72px;
      background: #FFFFFF;
      border-radius: 20px;
      margin-bottom: 20px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
      padding: 10px;
    }

    .logo-badge img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }

    h1 {
      font-size: 26px;
      font-weight: 800;
      color: var(--text-white);
      margin-bottom: 8px;
      letter-spacing: -0.3px;
    }

    p.subtitle {
      font-size: 15px;
      color: var(--text-muted);
      margin-bottom: 28px;
    }

    /* كرت الباركود QR Code */
    .qr-wrapper {
      background: #FFFFFF;
      border-radius: 20px;
      padding: 18px;
      display: inline-block;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
      margin-bottom: 14px;
      transition: transform 0.2s ease;
    }

    .qr-wrapper:hover {
      transform: scale(1.02);
    }

    .qr-wrapper img {
      width: 180px;
      height: 180px;
      display: block;
    }

    .qr-note {
      font-size: 13px;
      color: var(--teal-accent);
      font-weight: 600;
      margin-bottom: 28px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
    }

    /* أزرار التحميل */
    .buttons-group {
      display: flex;
      flex-direction: column;
      gap: 12px;
      margin-bottom: 24px;
    }

    .store-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 14px;
      padding: 14px 20px;
      border-radius: 16px;
      text-decoration: none;
      color: #FFFFFF;
      font-weight: 700;
      font-size: 15px;
      transition: all 0.25s ease;
      border: 1px solid rgba(255, 255, 255, 0.12);
    }

    .btn-apple {
      background: #000000;
    }

    .btn-apple:hover {
      background: #1a1a1a;
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
    }

    .btn-google {
      background: #111827;
      border-color: rgba(255, 255, 255, 0.18);
    }

    .btn-google:hover {
      background: #1f2937;
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
    }

    .store-btn svg {
      width: 26px;
      height: 26px;
      flex-shrink: 0;
    }

    .btn-content {
      text-align: right;
      display: flex;
      flex-direction: column;
    }

    .btn-small {
      font-size: 11px;
      font-weight: 400;
      opacity: 0.8;
      line-height: 1.2;
    }

    .btn-large {
      font-size: 16px;
      font-weight: 700;
      line-height: 1.2;
    }

    /* تذييل الصفحة */
    .footer-link {
      display: inline-block;
      color: var(--text-muted);
      font-size: 13px;
      text-decoration: none;
      transition: color 0.2s;
    }

    .footer-link:hover {
      color: var(--teal-accent);
    }

    @media (max-width: 480px) {
      .main-container {
        padding: 28px 20px;
        border-radius: 22px;
      }
      h1 {
        font-size: 22px;
      }
      .qr-wrapper img {
        width: 150px;
        height: 150px;
      }
    }
  </style>
</head>
<body>

  <div class="main-container">
    <!-- الشعار -->
    <div class="logo-badge">
      <img src="<?php echo htmlspecialchars($websiteUrl, ENT_QUOTES, 'UTF-8'); ?>logo.png" 
           alt="Salasil Logo" 
           onerror="this.onerror=null; this.src='https://salasil.info/logo.png';" />
    </div>

    <!-- العناوين -->
    <h1>حمّل تطبيق سلاسل</h1>
    <p class="subtitle">امسح الرمز بكاميرا هاتفك أو اختر المتجر للتحميل المباشر</p>

    <!-- رمز الـ QR Code -->
    <div class="qr-wrapper">
      <img src="<?php echo htmlspecialchars($qrCodeApiUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Scan QR Code to Download" />
    </div>

    <div class="qr-note">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
        <line x1="12" y1="18" x2="12.01" y2="18"></line>
      </svg>
      امسح الرمز بكاميرا هاتفك لفتح المتجر المناسب فوراً
    </div>

    <!-- أزرار المتاجر المباشرة -->
    <div class="buttons-group">
      <!-- أبل آب ستور -->
      <a href="<?php echo htmlspecialchars($iosStoreUrl, ENT_QUOTES, 'UTF-8'); ?>" class="store-btn btn-apple" id="appleBtn">
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 0.93-2.85-.9.04-1.99.6-2.64 1.35-.57.65-1.07 1.71-.93 2.73 1 .08 2.02-.48 2.64-1.23z"/>
        </svg>
        <div class="btn-content">
          <span class="btn-small">متوفر على متجر</span>
          <span class="btn-large">App Store</span>
        </div>
      </a>

      <!-- جوجل بلاي -->
      <a href="<?php echo htmlspecialchars($androidStoreUrl, ENT_QUOTES, 'UTF-8'); ?>" class="store-btn btn-google" id="googleBtn">
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M3.609 1.814L13.792 12 3.61 22.186a2.38 2.38 0 0 1-.61-.926V2.74c.14-.37.36-.69.609-.926zm11.3 9.072L6.14 1.386C6.73.974 7.44.75 8.187.75c.877 0 1.717.314 2.395.892l8.47 6.974-4.143 2.27zm0 2.228l4.143 2.27-8.47 6.974c-.678.578-1.518.892-2.395.892-.747 0-1.457-.224-2.047-.636l8.769-9.5zm1.185-1.114l4.63-2.54c.78-.428 1.266-1.22 1.266-2.094 0-.874-.486-1.666-1.266-2.094l-4.63-2.54 3.02 5.094-3.02 4.174z"/>
        </svg>
        <div class="btn-content">
          <span class="btn-small">احصل عليه من</span>
          <span class="btn-large">Google Play</span>
        </div>
      </a>
    </div>

    <!-- رابط الموقع الرئيسي -->
    <a href="<?php echo htmlspecialchars($websiteUrl, ENT_QUOTES, 'UTF-8'); ?>" class="footer-link">
      الانتقال إلى موقع سلاسل الإلكتروني ←
    </a>
  </div>

</body>
</html>

