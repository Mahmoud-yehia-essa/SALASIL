# 🚀 توثيق واجهات برمجة التطبيقات لمنظومة سلاسل (SALASIL API Documentation)

دليل شامل واحترافي لجميع واجهات برمجة التطبيقات (RESTful APIs) الخاصة بمنظومة **سلاسل (SALASIL)** اللوجستية، المخصصة للربط مع تطبيقات الهواتف الذكية (تطبيق العميل، تطبيق السائق)، ولوحات التحكم الخارجية، والأنظمة السحابية.

---

## 📌 معلومات عامة وإعدادات الاتصال (General & Configuration)

- **Base URL (بيئة التطوير المحلية):** `http://localhost:8888`
- **Base API Path:** `http://localhost:8888/api`
- **تنسيق البيانات (Data Format):** `JSON (application/json)`
- **طريقة التوثيق والتحقق (Authentication):** `Laravel Sanctum (Bearer Token)`
- **رأس الطلبات الموصى به (Default Headers):**
  ```http
  Accept: application/json
  Content-Type: application/json
  Authorization: Bearer {YOUR_ACCESS_TOKEN}
  ```

---

## 🧭 الفهرس السريع (Table of Contents)

1. [المصادقة وإدارة الحسابات (Authentication & Security)](#1-المصادقة-وإدارة-الحسابات-authentication--security)
2. [الملف الشخصي والإعدادات (Profile Management)](#2-الملف-الشخصي-والإعدادات-profile-management)
3. [الدول والمدن (Countries & Cities)](#3-الدول-والمدن-countries--cities)
4. [أسطول الشاحنات والمركبات (Trucks & Fleet Management)](#4-أسطول-الشاحنات-والمركبات-trucks--fleet-management)
5. [ربط وتوثيق السائقين والشاحنات (Driver & Truck Assignments)](#5-ربط-وتوثيق-السائقين-والشاحنات-driver--truck-assignments)
6. [الشحنات واللوجستيات والتتبع (Shipments & Logistics)](#6-الشحنات-واللوجستيات-والتتبع-shipments--logistics)
7. [المسارات والرحلات المجدولة (Scheduled Trips & Routes)](#7-المسارات-والرحلات-المجدولة-scheduled-trips--routes)
8. [الفواتير والمدفوعات (Invoices & Billing)](#8-الفواتير-والمدفوعات-invoices--billing)
9. [المحافظ والأرصدة (Wallets & Balances)](#9-المحافظ-والأرصدة-wallets--balances)
10. [المحادثات المباشرة والبث الحي (Direct Chat & Communications)](#10-المحادثات-المباشرة-والبث-الحي-direct-chat--communications)
11. [أحداث البث الفوري (WebSockets / Reverb Events)](#11-أحداث-البث-الفوري-websockets--reverb-events)

---

## 1. المصادقة وإدارة الحسابات (Authentication & Security)

### 1.1 تسجيل مستخدم جديد (Register)
- **Endpoint:** `POST /api/register`
- **الوصف:** تسجيل حساب جديد مع تحديد الدور (`individual_customer`, `company_customer`, `driver`).
- **Body (JSON):**
  ```json
  {
    "fname": "محمد",
    "lname": "علي",
    "email": "mohammed@example.com",
    "phone": "0501234567",
    "country_code": "+966",
    "role": "individual_customer",
    "password": "Password@123",
    "password_confirmation": "Password@123",
    "locale": "ar",
    "country_id": 2,
    "city_id": 2,
    "address": "الرياض - حي النخيل",
    "company_name": "شركة التقنية للحلول",
    "commercial_reg_no": "1010293847"
  }
  ```
- **Response (201 Created):**
  ```json
  {
    "status": true,
    "message": "User registered successfully",
    "token": "1|abc123token...",
    "user": {
      "id": 8,
      "client_code": "CU-78675",
      "fname": "محمد",
      "lname": "علي",
      "email": "mohammed@example.com",
      "phone": "0501234567",
      "role": "individual_customer"
    }
  }
  ```

---

### 1.2 تسجيل الدخول (Login)
- **Endpoint:** `POST /api/login`
- **الوصف:** تسجيل الدخول بواسطة البريد الإلكتروني أو رقم الهاتف أو كود العميل (`client_code`).
- **Body (JSON):**
  ```json
  {
    "login": "0501234567",
    "password": "Password@123",
    "firebase_token": "fcm_token_sample..."
  }
  ```

---

### 1.3 نسيت كلمة المرور (Forgot Password)
- **Endpoint:** `POST /api/forgot-password`
- **Body (JSON):**
  ```json
  {
    "email": "mohammed@example.com"
  }
  ```

---

### 1.4 التحقق من كود الاستعادة (Verify Reset Code)
- **Endpoint:** `POST /api/verify-reset-code`
- **Body (JSON):**
  ```json
  {
    "email": "mohammed@example.com",
    "code": "482910"
  }
  ```

---

### 1.5 إعادة تعيين كلمة المرور (Reset Password)
- **Endpoint:** `POST /api/reset-password`
- **Body (JSON):**
  ```json
  {
    "email": "mohammed@example.com",
    "code": "482910",
    "password": "NewPassword@123",
    "password_confirmation": "NewPassword@123"
  }
  ```

---

### 1.6 بيانات المستخدم الحالي (Get Authenticated User)
- **Endpoint:** `GET /api/user` (Requires Bearer Token)

---

### 1.7 تسجيل الخروج (Logout)
- **Endpoint:** `POST /api/logout` (Requires Bearer Token)

---

## 2. الملف الشخصي والإعدادات (Profile Management)

| Endpoint | Method | الوصف |
| :--- | :---: | :--- |
| `/api/profile` | `GET` | جلب الملف الشخصي مع بيانات الشركة أو السائق والمحفظة |
| `/api/profile/update` | `POST` | تحديث البيانات الشخصية (الاسم، الهاتف، الصورة، العنوان، الشركة) |
| `/api/profile/change-password` | `POST` | تغيير كلمة المرور (`current_password`, `new_password`) |
| `/api/profile/update-firebase-token` | `POST` | تحديث رمز إشعارات Firebase Cloud Messaging (FCM) |

---

## 3. الدول والمدن (Countries & Cities)

| Endpoint | Method | Auth | الوصف |
| :--- | :---: | :---: | :--- |
| `/api/countries` | `GET` | Public | جلب قائمة الدول المتاحة مع كود الهاتف والعملة |
| `/api/countries/{id}` | `GET` | Public | تفاصيل دولة محددة |
| `/api/countries/{country_id}/cities` | `GET` | Public | جلب جميع المدن التابعة لدولة محددة |
| `/api/cities` | `GET` | Public | جلب قائمة المدن |
| `/api/cities/{id}` | `GET` | Public | تفاصيل مدينة محددة |
| `/api/countries` | `POST` | Auth | إضافة دولة جديدة |
| `/api/countries/{id}/update` | `POST` | Auth | تعديل دولة |
| `/api/countries/{id}/status` | `POST` | Auth | تفعيل / تعطيل دولة |
| `/api/countries/{id}` | `DELETE` | Auth | حذف دولة |
| `/api/cities` | `POST` | Auth | إضافة مدينة جديدة |
| `/api/cities/{id}/update` | `POST` | Auth | تعديل مدينة |
| `/api/cities/{id}/status` | `POST` | Auth | تفعيل / تعطيل مدينة |
| `/api/cities/{id}` | `DELETE` | Auth | حذف مدينة |

---

## 4. أسطول الشاحنات والمركبات (Trucks & Fleet Management)

### 4.1 براندات الشاحنات (Truck Brands)
- `GET /api/truck-brands`: قائمة الماركات (Volvo, Mercedes, MAN...)
- `GET /api/truck-brands/{brand_id}/models`: موديلات تابعة لماركة محددة
- `POST /api/truck-brands`: إضافة ماركة
- `POST /api/truck-brands/{id}/update`: تعديل ماركة
- `POST /api/truck-brands/{id}/status`: تغيير الحالة
- `DELETE /api/truck-brands/{id}`: حذف ماركة

### 4.2 موديلات الشاحنات (Truck Models)
- `GET /api/truck-models`: قائمة الموديلات
- `POST /api/truck-models`: إضافة موديل
- `POST /api/truck-models/{id}/update`: تعديل موديل
- `POST /api/truck-models/{id}/status`: تغيير الحالة
- `DELETE /api/truck-models/{id}`: حذف موديل

### 4.3 أنواع الشاحنات الرئيسية (Truck Types)
- `GET /api/truck-types`: قائمة الأنواع الرئيسية (Trailer, Container, Reefer...)
- `GET /api/truck-types/{truck_type_id}/sub-types`: الأنواع الفرعية التابعة لنوع محدد
- `POST /api/truck-types`: إضافة نوع شاحنة رئيسي
- `POST /api/truck-types/{id}/update`: تعديل نوع الشاحنة
- `POST /api/truck-types/{id}/status`: تغيير الحالة
- `DELETE /api/truck-types/{id}`: حذف نوع الشاحنة

### 4.4 الأنواع الفرعية للشاحنات (Truck Sub-Types)
- `GET /api/truck-sub-types`: قائمة الأنواع الفرعية مع أقصى حمولة (`max_payload`)
- `POST /api/truck-sub-types`: إضافة نوع فرعي
- `POST /api/truck-sub-types/{id}/update`: تعديل نوع فرعي
- `POST /api/truck-sub-types/{id}/status`: تغيير الحالة
- `DELETE /api/truck-sub-types/{id}`: حذف نوع فرعي

---

## 5. ربط وتوثيق السائقين والشاحنات (Driver & Truck Assignments)

| Endpoint | Method | الوصف |
| :--- | :---: | :--- |
| `/api/driver-trucks` | `GET` | قائمة شاحنات السائقين مع دعم التصفية والبحث |
| `/api/driver-trucks/my-trucks` | `GET` | جلب الشاحنات الخاصة بالسائق المسجل دخوله حالياً |
| `/api/driver-trucks/{id}` | `GET` | تفاصيل شاحنة سائق محددة |
| `/api/driver-trucks` | `POST` | تسجيل وتعيين شاحنة لسائق (`driver_id`, `truck_type_id`, `plate_number`, `manufacturing_year`, `axles_count`) |
| `/api/driver-trucks/{id}/update` | `POST` | تعديل بيانات الشاحنة |
| `/api/driver-trucks/{id}/set-default` | `POST` | تعيين الشاحنة كشاحنة افتراضية رئيسية للسائق |
| `/api/driver-trucks/{id}/verify` | `POST` | توثيق واعتماد الشاحنة من الإدارة (`is_verified`) |
| `/api/driver-trucks/{id}` | `DELETE` | إلغاء تخصيص الشاحنة |

---

## 6. الشحنات واللوجستيات والتتبع (Shipments & Logistics)

### 6.1 أنواع وطبيعة الشحنات (Types & Natures)
- `GET /api/shipment-types`: قائمة أنواع الشحنات
- `GET /api/shipment-natures`: قائمة طبيعة الشحنات (صلبة، سائلة، غازية، خطرة)

### 6.2 الاستعلام الجمركي والتحليل الذكي (HS Code Lookup & AI Analysis)
- **Endpoint:** `POST /api/shipment/hscode/lookup`
- **Body:** `{"hs_code": "8517.13.00"}`
- **الوصف:** استعلام شامل عن البند الجمركي، وحساب الرسوم الجمركية وضريبة القيمة المضافة ZATCA والاشتراطات وتحليل متطلبات النقل والشحن الموصى بها.

### 6.3 إدارة وتتبع الشحنات (Shipment Orders)
- **`GET /api/shipments`**: قائمة الشحنات مع الفلترة حسب الحالة، الدفع، العميل، السائق، والمدينة.
- **`GET /api/shipments/my-shipments`**: شحنات المستخدم الحالي (عميل أو سائق).
- **`GET /api/shipments/statistics`**: إحصائيات KPI overview (إجمالي، جديدة، معتمدة، قيد النقل، مسلمة، القيمة المالية).
- **`GET /api/shipments/{id}`**: جلب تفاصيل الشحنة الكاملة (أكثر من 40 حقلاً تشمل العميل، السائق، الفاتورة، المواقع، الشاحنة، جهات الاتصال، ومراحل التتبع).
- **`POST /api/shipments`**: إنشاء طلب شحنة جديد بجميع الخطوات (المواصفات، الأبعاد، الوزن، الأماكن، جهات الاتصال، السعر) مع **توليد الفاتورة المالية تلقائياً** `INV-YYYYMMDD-XXXX`.
- **`POST /api/shipments/{id}/update`**: تعديل بيانات ومواصفات الشحنة.
- **`POST /api/shipments/{id}/status`**: تحديث حالة الشحنة (`new`, `under_review`, `pending_approval`, `approved`, `in_transit`, `delivered`, `rejected`, `canceled`).
- **`POST /api/shipments/{id}/assign-driver`**: تعيين سائق للشحنة وتحديث حالتها إلى `approved`.
- **`DELETE /api/shipments/{id}`**: حذف الشحنة.

### 6.4 التتبع الحي والموقع الجغرافي GPS (Live GPS Tracking)
- **`GET /api/shipment-tracking`**: قائمة الشحنات المتاحة للتتبع المباشر.
- **`GET /api/shipment-tracking/{id}`**: بيانات خريطة التتبع المباشر وإحداثيات البداية والوصول والموقع اللحظي وسجل المحطات.
- **`POST /api/shipment-tracking/update-location`**: إرسال إحداثيات GPS الحية من تطبيق السائق (`latitude`, `longitude`, `speed`, `heading`, `is_stop_point`, `location_description`) وبثها الفوري عبر WebSocket/Reverb.
- **`POST /api/shipment-tracking/update-status`**: تسجيل مرحلة جديدة للشحنة مع الموقع وبث الحدث.
- **`GET /api/shipment-tracking/{id}/logs`**: جلب سجل المسار والنقاط الجغرافية الكامل.
- **`DELETE /api/shipment-tracking/{id}/logs`**: إعادة ضبط مسار التتبع.

### 6.5 دعوات وتكليف السائقين (Driver Invitations)
- **`GET /api/shipment-invitations`**: قائمة جميع الدعوات المرسلة للسائقين.
- **`GET /api/shipment-invitations/my-invitations`**: دعوات الشحن الواردة للسائق المسجل دخوله في التطبيق.
- **`POST /api/shipment-invitations`**: إرسال دعوة عرض شحنة لسائق مع السعر المعروض والقناة (`in_app`, `whatsapp`, `sms`).
- **`POST /api/shipment-invitations/{id}/respond`**: رد السائق (`action`: `accept` أو `reject`). عند القبول، يتم تعيين السائق على الشحنة فورياً وتحديث حالتها إلى معتمدة `approved`.

---

## 7. المسارات والرحلات المجدولة (Scheduled Trips & Routes)

### 7.1 المسارات الثابتة (Fixed Routes)
- `GET /api/routes`: قائمة المسارات الثابتة (محلية / دولية) بين المدن والدول.
- `GET /api/routes/statistics`: إحصائيات المسارات.
- `GET /api/routes/{id}`: تفاصيل مسار محدد والرحلات التابعة له.
- `POST /api/routes`: إضافة مسار جديد (`quote_type`, `origin_country_id`, `origin_city_id`, `destination_country_id`, `destination_city_id`, `estimated_distance`).
- `POST /api/routes/{id}/update`: تعديل المسار.
- `POST /api/routes/{id}/status`: تفعيل أو تعطيل المسار.
- `DELETE /api/routes/{id}`: حذف المسار.

### 7.2 الرحلات المجدولة (Scheduled Trips)
- `GET /api/scheduled-trips`: قائمة الرحلات المجدولة مع الفلترة حسب المسار، الشاحنة، السائق، والتواريخ.
- `GET /api/scheduled-trips/my-trips`: الرحلات المسندة للسائق المسجل دخوله.
- `GET /api/scheduled-trips/statistics`: إحصائيات الرحلات (`published`, `boarding`, `in_transit`, `completed`, `canceled`).
- `GET /api/scheduled-trips/{id}`: تفاصيل الرحلة المجدولة الكاملة.
- `POST /api/scheduled-trips`: إنشاء ونشر رحلة مجدولة (`route_id`, `truck_type_id`, `number_of_trucks`, `total_weight_ton`, `driver_id`, `trip_date`, `trip_time`, `price`, `total_capacity`, `available_capacity`, `status`).
- `POST /api/scheduled-trips/{id}/update`: تعديل رحلة مجدولة.
- `POST /api/scheduled-trips/{id}/status`: تحديث حالة الرحلة.
- `POST /api/scheduled-trips/{id}/assign-driver`: تعيين سائق للرحلة المجدولة.
- `DELETE /api/scheduled-trips/{id}`: حذف رحلة مجدولة.

---

## 8. الفواتير والمدفوعات (Invoices & Billing)

### 8.1 الفواتير المالية (Invoices)
- `GET /api/invoices`: قائمة بجميع الفواتير مع العميل والشحنة والمدفوعات والفلترة.
- `GET /api/invoices/my-invoices`: فواتير العميل المسجل دخوله.
- `GET /api/invoices/statistics`: إحصائيات الفواتير وإجمالي الإيرادات والمبالغ المعلقة.
- `GET /api/invoices/{id}`: تفاصيل الفاتورة ومكوناتها وسجل مدفوعاتها.
- `POST /api/invoices`: إنشاء فاتورة جديدة مع احتساب الضريبة والخصم وتوليد رقم الفاتورة تلقائياً.
- `POST /api/invoices/{id}/update`: تعديل مبالغ الفاتورة وتاريخ الاستحقاق.
- `POST /api/invoices/{id}/status`: تغيير حالة الفاتورة (`unpaid`, `partially_paid`, `paid`, `canceled`).
- `DELETE /api/invoices/{id}`: حذف الفاتورة.

### 8.2 عمليات الدفع والتحصيل (Payments)
- `GET /api/payments`: قائمة عمليات الدفع والتحصيل.
- `GET /api/payments/my-payments`: سجل مدفوعات العميل.
- `GET /api/payments/statistics`: إحصائيات المدفوعات وإجمالي المحصل.
- `GET /api/payments/{id}`: تفاصيل عملية الدفع وإيصال السداد.
- `POST /api/payments`: تسجيل عملية دفع (`knet`, `credit_card`, `apple_pay`, `bank_transfer`, `cash`) ورفع الإيصال **ومزامنة حالة الفاتورة والشحنة تلقائياً إلى مدفوعة (Paid)**.
- `POST /api/payments/{id}/update`: تعديل عملية الدفع وإعادة مزامنة الفاتورة.
- `POST /api/payments/{id}/status`: تغيير حالة الدفع (`pending`, `completed`, `failed`, `refunded`).
- `DELETE /api/payments/{id}`: حذف عملية الدفع وإعادة احتساب رصيد الفاتورة.

---

## 9. المحافظ والأرصدة (Wallets & Balances)

### 9.1 أرصدة المحافظ (Wallets Overview)
- `GET /api/wallets`: قائمة محافظ المستخدمين مع الرصيد اللحظي، عدد العمليات، وتاريخ آخر نشاط.
- `GET /api/wallets/my-wallet`: محفظة المستخدم الحالي (الرصيد، إجمالي الإيداعات، إجمالي السحوبات، وآخر 10 حركات).
- `GET /api/wallets/statistics`: إحصائيات إجمالي المحافظ والأرصدة المتداولة.
- `GET /api/wallets/{user_id}`: تفاصيل محفظة مستخدم محدد مع كشف الحساب الكامل (Ledger).

### 9.2 الحركات المالية (Transactions)
- `GET /api/wallets/transactions`: سجل جميع الحركات المالية مع الفلترة والبحث.
- `GET /api/wallets/my-transactions`: كشف حساب حركات المستخدم الحالي.
- `POST /api/wallets/transactions`: تسجيل حركة مالية (`deposit`, `withdrawal`, `trip_earnings`, `commission_deduction`, `refund`) واحتساب الرصيد بعد الحركة `balance_after` تلقائياً.
- `POST /api/wallets/transactions/{id}/update`: تعديل حركة مالية وإعادة احتساب كشف الحساب الزمني للمستخدم.
- `DELETE /api/wallets/transactions/{id}`: حذف حركة مالية وإعادة ضبط تسلسل الرصيد.

---

## 10. المحادثات المباشرة والبث الحي (Direct Chat & Communications)

- `GET /api/chat/conversations`: قائمة المحادثات (المباشرة direct والدعم support) مع الرسائل غير المقروءة.
- `GET /api/chat/statistics`: إحصائيات المحادثات والرسائل غير المقروءة.
- `POST /api/chat/conversations`: بدء محادثة جديدة مع مستخدم أو سائق أو الدعم وإرسال الرسالة الأولى.
- `GET /api/chat/conversations/{id}`: تفاصيل المحادثة وسجل الرسائل وتحديث حالة القراءة تلقائياً إلى `read`.
- `POST /api/chat/send`: إرسال رسالة شات (`text`, `image`, `audio` تسجيل صوتي، `location` موقع جغرافي، `file`) وبثها الفوري عبر WebSocket.
- `POST /api/chat/conversations/{id}/status`: تغيير حالة المحادثة (`open` <-> `closed`).
- `POST /api/chat/conversations/{id}/channel`: تغيير قناة المحادثة (`in_app`, `whatsapp`, `sms`).
- `DELETE /api/chat/messages/{id}`: حذف رسالة (Soft Delete).
- `DELETE /api/chat/conversations/{id}`: حذف المحادثة وسجلها بالكامل.

---

## 11. أحداث البث الفوري (WebSockets / Reverb Events)

تدعم منظومة سلاسل البث الفوري عبر **Laravel Reverb** على القنوات العامة والخاصة:

### 1. تحديث موقع الشاحنة اللحظي (Live GPS Location)
- **Event Class:** `App\Events\ShipmentLocationUpdated`
- **Channel:** `presence-shipment.{shipment_id}` أو `shipment.{shipment_id}`
- **Payload:**
  ```json
  {
    "shipment_id": 1,
    "latitude": 24.6877,
    "longitude": 46.7219,
    "speed": 85.5,
    "heading": 145,
    "is_stop_point": false,
    "location_description": "على طريق الرياض - الطائف السريع",
    "status": "in_transit",
    "timestamp": "2026-08-22T15:30:00Z"
  }
  ```

### 2. تحديث مرحلة وحالة الشحنة (Shipment Milestone Status)
- **Event Class:** `App\Events\ShipmentStatusUpdated`
- **Channel:** `shipment.{shipment_id}`
- **Payload:**
  ```json
  {
    "shipment_id": 1,
    "status": "تم التحميل بنجاح وفي الطريق للتسليم",
    "location_description": "نقطة تفتيش المجمع",
    "lat": 24.6877,
    "lng": 46.7219
  }
  ```

### 3. إرسال واستقبال رسائل الشات اللحظية (Real-Time Messages)
- **Event Class:** `App\Events\MessageSent`
- **Channel:** `chat.{conversation_id}`
- **Payload:**
  ```json
  {
    "message": {
      "id": 105,
      "conversation_id": 1,
      "sender_id": 4,
      "message_type": "text",
      "content": "نعم تم استلام الشحنة وهي الآن في طريقها لمدينة جدة.",
      "metadata": null,
      "reply_to": null,
      "delivery_status": "sent",
      "created_at": "2026-08-22T15:35:00Z"
    }
  }
  ```

---

## 💻 ملحق: ملف Postman الجاهز للاستخدام المباشر

تم تضمين كافة الـ Endpoints المذكورة أعلاه في ملف Postman متكامل يحتوي على البيئات (`Variables`)، ونماذج البيانات الحقيقية (`Body Examples`)، والتوثيق التوضيحي لكل طلب:
📄 **[SALASIL.postman_collection.json](file:///Volumes/KINGSTON/SALASIL_Project/site_cpanel/salasil/SALASIL.postman_collection.json)**
