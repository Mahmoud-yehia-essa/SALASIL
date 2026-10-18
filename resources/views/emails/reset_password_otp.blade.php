<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رمز استعادة كلمة المرور - سلاسل</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
            color: #334155;
            direction: rtl;
            text-align: right;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f4f7fa;
            padding: 40px 0;
        }
        .container {
            max-width: 540px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        .header {
            background-color: #1e3a8a;
            padding: 30px 24px;
            text-align: center;
        }
        .logo-title {
            color: #ffffff;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: 2px;
            margin: 0;
            text-transform: uppercase;
        }
        .logo-subtitle {
            color: #93c5fd;
            font-size: 13px;
            margin-top: 4px;
            font-weight: 500;
        }
        .content {
            padding: 36px 30px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
        }
        .message-text {
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 24px;
        }
        .otp-box {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 2px dashed #3b82f6;
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            margin: 28px 0;
        }
        .otp-label {
            font-size: 13px;
            color: #1e40af;
            font-weight: 600;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .otp-code {
            font-size: 36px;
            font-weight: 800;
            color: #1d4ed8;
            letter-spacing: 12px;
            margin: 0;
            direction: ltr;
            display: inline-block;
        }
        .expiry-note {
            font-size: 12px;
            color: #64748b;
            margin-top: 8px;
        }
        .warning-box {
            background-color: #fffbeb;
            border-right: 4px solid #f59e0b;
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 13px;
            color: #92400e;
            line-height: 1.5;
            margin-top: 24px;
        }
        .en-section {
            direction: ltr;
            text-align: left;
            margin-top: 30px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px 24px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
        .footer a {
            color: #3b82f6;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <h1 class="logo-title">SALASIL</h1>
                <div class="logo-subtitle">منصة سلاسل للخدمات اللوجستية والنقل</div>
            </div>

            <!-- Content -->
            <div class="content">
                <!-- Arabic Section -->
                <div class="greeting">
                    مرحباً {{ $user->fname ?? 'عميلنا العزيز' }}،
                </div>
                <div class="message-text">
                    لقد تلقينا طلباً لإعادة تعيين كلمة المرور لحسابك في منصة <strong>سلاسل</strong>. يرجى استخدام رمز التحقق (OTP) التالي لإكمال العملية:
                </div>

                <!-- OTP Code Display -->
                <div class="otp-box">
                    <div class="otp-label">رمز التحقق الخاص بك</div>
                    <div class="otp-code">{{ $otp }}</div>
                    <div class="expiry-note">الرمز صالح لمدة 60 دقيقة فقط</div>
                </div>

                <div class="warning-box">
                    ⚠️ إذا لم تقم بطلب إعادة تعيين كلمة المرور، يرجى تجاهل هذه الرسالة أو التواصل فوراً مع فريق الدعم لحماية حسابك.
                </div>

                <!-- English Section -->
                <div class="en-section">
                    <div style="font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">
                        Hello {{ $user->fname ?? 'Valued Customer' }},
                    </div>
                    <div style="font-size: 13px; line-height: 1.5; color: #475569;">
                        We received a request to reset your password for your <strong>SALASIL</strong> account. Please use the verification code above to continue. If you didn't request this, please ignore this email.
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p style="margin: 0 0 6px 0;">© {{ date('Y') }} SALASIL Logistics Platform. جميع الحقوق محفوظة.</p>
                <p style="margin: 0;">لأي استفسار، تواصل معنا عبر <a href="mailto:support@salasil.com">support@salasil.com</a></p>
            </div>
        </div>
    </div>
</body>
</html>
