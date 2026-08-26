# 💼 Creative Tasks — المنظومة الذكية لإدارة المهام والمتابعة الميدانية
### Enterprise Smart Tasks & Field Workforce Attendance Platform

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white)
![Inertia.js](https://img.shields.io/badge/Inertia.js-Modern_SPA-9553E9?style=for-the-badge&logo=inertia&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Leaflet](https://img.shields.io/badge/Leaflet-GPS_GIS-199900?style=for-the-badge&logo=leaflet&logoColor=white)
![PHPUnit](https://img.shields.io/badge/Tests-35%20Passed-brightgreen?style=for-the-badge&logo=php)

---

## 🌟 نظرة عامة عن المنظومة (Platform Overview)

منصة مؤسسية شاملة ومتطورة لإدارة وتوزيع المهام والتكليفات اليومية، وتوثيق الحضور الميداني والمكتبي لفريق العمل والكوادر التشغيلية في الشركات والمؤسسات. تجمع المنظومة بين قوة وأمان **Laravel 12**، وسرعة واستجابة **Vue 3 (Inertia.js SPA)**، مع محرك تتبع جغرافي ذكي (**GPS Geofencing**) يربط إنجاز المهام بمواقع العمل المعتمدة ومقرات الفروع بدقة وكفاءة عالية.

---

## 🚀 المميزات الرئيسية (Core Features)

### 1. 🛰️ نظام الحضور الميداني والتتبع الجغرافي (GPS & Geofencing)
- **بوابة التحقق الجغرافي الذكية (Smart Location Gate)**: التحقق التلقائي من تواجد الكوادر الميدانية ضمن النطاق الجغرافي المعتمد لمقر العمل قبل فتح لوحة المهام.
- **الخريطة التفاعلية (Interactive Leaflet Map)**: تحديد المواقع وتعيين الإحداثيات الجغرافية عبر خريطة حية مزودة بنقاط وصول سريعة لمقرات وفروع الشركة.
- **أنماط حضور مرنة ومستقلة لكل مستخدم**:
  - **نمط GPS الميداني**: تتبع حي للأجهزة الذكية والمتابعة الخارجية والفرق المتنقلة.
  - **نمط المقر المكتبي الثابت**: توثيق حضور الموظفين الإداريين والمكتبيين تلقائياً داخل مكاتب الشركة.

### 2. 📋 إدارة المهام والتكليفات اليومية (Task Management)
- تكليف وتوزيع المهام على مستوى الأقسام والإدارات وفرق العمل.
- تتبع نسب الإنجاز الآنية ومراحل التنفيذ (قيد الانتظار، جاري العمل، مكتملة).
- إمكانية التكليف الذاتي للمهام اليومية للموظف مع اعتمادها المباشر من مدير القسم.

### 3. 👥 إدارة فريق العمل واستيراد الإكسل الذكي (Team & Excel Engine)
- **قالب إكسل تفاعلي 5 أعمدة**: تحميل قالب رسمي يتضمن **قوائم منسدلة مدمجة (Native Dropdown Lists)** لاختيار الأقسام والأدوار مباشرة داخل ملف Excel لمنع الأخطاء الإملائية.
- **استيراد جماعي ذكي (Smart Bulk Import)**: معالجة سريعة للملفات، إنشاء الأقسام الجديدة غير المسجلة تلقائياً، وضبط كلمات المرور ونمط الـ GPS الافتراضي لكل موظف.
- **تعديل متكامل لكل مستخدم**: تعديل البيانات ونمط الحضور والموقع الجغرافي من نافذة واحدة مخصصة.

### 4. 📊 مركز التقارير والإحصائيات التراكمية (Reports & Analytics)
- توليد تقارير أداء دورية (يومية، أسبوعية، شهرية) لجميع الأقسام والكوادر.
- تصدير فوري لتقارير معتمدة بصيغتي **PDF** و **Excel (XLSX)** بضغطة زر.
- لوحة مؤشرات بيانية تحليلية لقياس كفاءة الأقسام ونسب الإنجاز العامة.

### 5. 🎨 تجربة مستخدم عصرية (Modern UX/UI)
- دعم كامل ومتزامن للغتين **العربية (RTL)** و **الإنجليزية (LTR)**.
- منتقي ألوان الطابع والتخصيص البصري (**Accent Color Picker**).
- وضع نهاري وليلي ديناميكي فائق النقاء (**Dark / Light Mode**).
- نظام إشعارات عائم ذكي (**Auto-Dismiss Toasts**) بمؤقت اختفاء 3 ثوانٍ.
- شاشة تسجيل دخول تفاعلية مزودة بأزرار الدخول السريع للحسابات التجريبية.

---

## 🏛️ هيكلية الأدوار والصلاحيات (Role-Based Access Control)

| الدور (Role) | الصلاحيات والمسؤوليات |
| :--- | :--- |
| **المدير التنفيذي / الإدارة العامة (`admin`)** | الوصول الشامل لكافة الأقسام، إدارة الكوادر والمستخدمين، استيراد وتصدير البيانات، التقارير المركزية، وضبط معايير النظام. |
| **مدير القسم / المشرف (`head`)** | إدارة وتكليف كوادر القسم التابع له، متابعة المهام اليومية، وتوليد تقارير إنجاز وحضور القسم. |
| **الموظف / الأخصائي الميداني (`employee`)** | بوابة المهام الشخصية، تحديث نسب الإنجاز، تسجيل الحضور الجغرافي، وإضافة الملاحظات اليومية. |

---

## 📂 بنية قاعدة البيانات ونظام الـ Seeders

قاعدة البيانات مبنية بمرونة عالية تدعم **SQLite** للتشغيل السريع والخوادم المحلية، و **MySQL / PostgreSQL** للإنتاج الموسع:

### 1. الـ Seeders الأساسية (Production Seeder)
- لتجهيز بيئة الإنتاج النظيفة للمؤسسة:
```bash
php artisan migrate:fresh --seed
```
*يقوم بإنشاء حساب المدير التنفيذي الرسمي (`admin@creativetasks.io`) والأقسام المؤسسية الأساسية.*

### 2. بيانات العرض والتجربة (Demo Data Seeder)
- لتعبئة النظام ببيانات تجريبية كاملة للمعاينة والاختبار:
```bash
php artisan db:seed --class=DemoDataSeeder
```

---

## ⚙️ متطلبات التشغيل والتثبيت (Installation Guide)

### المتطلبات الأساسية:
- **PHP 8.2** أو أحدث مع الامتدادات (`pdo_sqlite`, `mbstring`, `zip`, `gd`, `bcmath`, `fileinfo`).
- **Composer 2.x**.
- **Node.js 18+** و **NPM**.

### خطوات التثبيت السريع:

```bash
# 1. استنساخ المستودع
git clone https://github.com/mr-creative-hmh/creative-tasks.git
cd creative-tasks

# 2. تثبيت حزم الـ Backend
composer install

# 3. إعداد ملف البيئة
cp .env.example .env
php artisan key:generate

# 4. بناء قاعدة البيانات وتوليد الحسابات الأساسية
touch database/database.sqlite
php artisan migrate:fresh --seed

# 5. تثبيت حزم الواجهات وبناء الأصول
npm install
npm run build

# 6. تشغيل خادم التطوير
php artisan serve
```

---

## 🧪 الاختبارات والجودة (Testing & QA)

النظام مغطى بحزمة اختبارات تكاملية وآلية متكاملة (35 Feature & Unit Tests):

```bash
php artisan test
```

---

## 🔒 إرشادات النشر للإنتاج (Production Deployment)

1. ضبط بيئة الإنتاج في ملف `.env`:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://tasks.yourdomain.com
   ```
2. تحسين وتخزين الكاش:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   npm run build
   ```
3. منح صلاحيات القراءة والكتابة لملف `database/database.sqlite` ومجلد `storage/`.

---

## 👨‍💻 المطور وحقوق الملكية (Creator & Credits)

- **برمجة وتطوير:** المهندس حسن محمد حسن
- **منصة Creative Tasks Platform**
- **جميع الحقوق محفوظة © 2026**