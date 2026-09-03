# سجل تعديلات الوثائق

كل انحراف عن الوثائق الأصلية، وسببه. القاعدة من `README.md` بند ٨:
**عدّل الوثيقة الأول، وبعدين اكتب الكود.**

---

## المرحلة ١ — البيئة

### ✅ تأكيدات (مفيش تعديل مطلوب)

| البند | الوثيقة | الواقع (متحقق منه) |
|---|---|---|
| laravel/framework | `^13.0` | v13.29.0 — موجود |
| filament/filament | `^5.7` | v5.7.6 — موجود |
| spatie/laravel-permission | `^8.3` | 8.3.0 — موجود |
| bezhansalleh/filament-shield | `^4.3` | 4.3.1 — موجود |
| spatie/laravel-medialibrary | `^11.23` | 11.23.5 — موجود |
| spatie/laravel-settings | `^3.9` | 3.9.0 — موجود |
| PHP | 8.4 | Laravel 13 بيطلب `^8.3` — فـ 8.4 صح ومتوافق |

### ✏️ تعديلات على `docs/00-setup.md`

**١. خدمة `minio-init` مضافة لـ `docker-compose.yml`**
الـ compose في الوثيقة بيشغّل MinIO لكن مابيعملش الباكت. من غير الباكت أول رفع
بيفشل بـ `NoSuchBucket`، والمطوّر بيقعد يدوّر. الخدمة الجديدة بتعمل
`mc mb --ignore-existing local/fc-admin` مرة واحدة وقت الإقلاع.

**٢. `healthcheck` مضاف لـ postgres و redis**
من غيره `depends_on` بيستنى إن الحاوية **اشتغلت** مش إنها **جاهزة**، فأول
`php artisan migrate` بيفشل بـ connection refused.

**٣. Node 22 اتثبّت جوه صورة PHP بدل ما يتاخد من المضيف**
الوثيقة بتقول «Node 22 LTS» في متطلبات الجهاز وبتشغّل `npm install` بره Docker.
Node على الجهاز ده **24.14.1**. تثبيت Node 22 جوه الصورة بيخلي الـ build واحد
على كل جهاز — وده أصلاً روح قرار «مفيش شغال عندي» اللي الوثيقة بتقوله عن Docker.
`npm` بقى بيتنفّذ بـ `docker compose exec app npm ...`.

**٤. `docker/php/Dockerfile` و `docker/nginx/default.conf` اتكتبوا**
الوثيقة بتشير للملفين دول في الـ compose لكن مش مديّة محتواهم. اتكتبوا بالامتدادات
اللي الستاك محتاجها (`pdo_pgsql`, `redis`, `gd`, `intl`, `zip`, `bcmath`, `exif`,
`pcntl`, `opcache`) و nginx بـ TLS و `fastcgi_param HTTPS on` عشان
TrustProxies يشوف الطلب https (مصيدة `docs/14` بند ١).

**٥. شهادة self-signed مؤقتة بدل mkcert**
mkcert مش متثبّت على الجهاز وتثبيته محتاج صلاحيات admin. اتولّدت شهادة
self-signed بـ openssl عشان nginx يقوم. **معيار القبول (قفل أخضر) لسه مش متحقق** —
الخطوات في `docker/nginx/certs/README.md`.

**٦. `vendor/` و `node_modules/` على named volumes مش على الـ bind mount**
أول محاولة لـ `composer create-project` **فشلت**: فك ضغط `laravel/framework` لوحده
عدّى الـ 300 ثانية بتاعة `COMPOSER_PROCESS_TIMEOUT` وهو بيكتب على قرص ويندوز من
جوه الحاوية. آلاف الملفات الصغيرة عبر الـ bind mount = بطء قاتل.

الحل: `vendor` و `node_modules` بقوا volumes بتاعة Docker (نظام ملفات Linux)،
والكود المصدري لسه على الـ bind mount عادي عشان تقدر تعدّله من ويندوز.

**التكلفة:** `vendor/` مش ظاهر على الهوست، فالـ IDE مش هيعمل autocomplete للباكدجات.
كل الأدوات (`composer test`, `composer lint`, `artisan`) بتتنفّذ جوه الحاوية أصلاً.
لو احتجت `vendor` على الهوست: `docker compose cp app:/var/www/html/vendor ./vendor`.

**٧. المشروع اتنقل بره OneDrive → `D:\PROJECTS\admin-panal-templete`**
OneDrive بيزامن كل ملف في `vendor/` و`node_modules/` — تباطؤ وfile locks بتكسر
التثبيت في نصه. **توصية:** ضيف `D:\PROJECTS` كاستثناء في Windows Defender كمان.

### ✏️ تعديلات على `.env` (مؤقتة)

| المفتاح | الوثيقة | دلوقتي | ليه |
|---|---|---|---|
| `APP_URL` | `https://fc-admin.test:8443` | `https://localhost:8443` | ملف hosts لسه مظبطش. رجّعها بعد ما يتظبط. |
| `BROADCAST_CONNECTION` | `reverb` | `log` | Reverb بيتثبّت في المرحلة ٨. لو سبناها `reverb` دلوقتي التطبيق بيقع. |

### 🗑️ محذوف

- `قالب-لوحة-التحكم-الشغل-مع-Claude-Code.md` — محتواه كان رسالة خطأ JSON
  (`{"type":"error"..."Not found"}`) مش markdown. تحميل فشل واتحفظ غلط.
- `__MACOSX/` و `.DS_Store` — مخلّفات ضغط من macOS.
- `CLAUDE.md` و `AGENTS.md` بتوع skeleton بتاع Laravel 13 (ملفين متطابقين، محتوى
  `laravel-boost` عام). `CLAUDE.md` بتاع القالب هو المرجع، و`AGENTS.md` بقى سطر
  واحد بيشاور عليه.

---

## المرحلة ٢ — بناء القالب (Support + Tenancy + Identity)

### 🐞 باگات حقيقية اتكشفت وأصلحت

الباگات دي **مش** انحرافات عن الوثائق — دي أخطاء في الكود اللي الوثائق
بتوصفه، اتكشفت لما جرّبنا فعلياً. مكتوبة هنا عشان محدش يقع فيها تاني.

**١. الدخول للوحة كان مستحيل — `Gate::allows()` بدل `Gate::forUser()`**

`User::canAccessPanel()` كانت بتسأل `Gate::allows('access.panel.admin')`.
الـ facade دي بتقرا المستخدم من `auth()` — وأثناء تسجيل الدخول لسه مفيش
مستخدم مصادَق عليه، فبترجع `false` دايماً. النتيجة: كل محاولة دخول
بترجع «بيانات الاعتماد غير متطابقة» حتى بكلمة مرور صحيحة.

الصح: `Gate::forUser($this)->allows(...)`.

**٢. الأدوار مربوطة بالمؤسسة، والفحص بيحصل قبل اختيارها**

نفس المسار: Filament بينادي `canAccessPanel()` بعد المصادقة مباشرة،
وقتها فريق spatie/permission = `null`، فأي `hasRole()` بترجع `false`
لأن الإسناد متخزّن بـ `tenant_id`. أضفنا في `AuthorizationServiceProvider`
سؤال «هل معاه الصلاحية دي في **أي** مؤسسة هو عضو فيها؟» يشتغل بس لما
مفيش سياق مؤسسة. ده مش تخفيف للعزل — العزل بيتطبّق بعد الاختيار.

**٣. `RoleResource` كان بيرمي LogicException في كل صفحة**

Filament بيقصر استعلام أي مورد على المؤسسة الحالية، فكان بيدوّر على
علاقة `tenant()` على موديل `Role` — وهي مش موجودة لأن الأدوار **عامة**.
الحل: `protected static bool $isScopedToTenant = false;`

**٤. `UserResource` محتاج `$tenantOwnershipRelationshipName`**

المستخدم عضو في المؤسسة عبر many-to-many (`tenant_user`) مش بعمود
`tenant_id`، فالافتراضي (`tenant`) مش موجود عليه. الحل: `'tenants'`،
و Filament بيقصر بـ `whereHas` تلقائياً.

**٥. جدول `notifications` كان `text` بدل `json`**

ميجريشن Laravel القياسي بيعمل `data` كـ `text`. Filament بيفلتر جرس
الإشعارات بـ `data->>'format'`، و Postgres بيرمي
`operator does not exist: text ->> unknown` — يعني **كل** صفحة في
اللوحة كانت بترجع 500. الحل: `$table->json('data')`.

**٦. ملفات Fortify كانت بتشاور على `App\Models\User` المحذوف**

`vendor:publish` بتاع Fortify ولّد `app/Actions/Fortify/*` بتستورد
`App\Models\User`، وإحنا نقلنا الموديل لـ `Src\Contexts\Identity`.
كانت هتقع وقت التشغيل. PHPStan هو اللي مسكها.

**٧. ملكية `storage/` جوه الحاوية**

php-fpm بيشتغل كـ `www-data`، والملفات على الـ bind mount بتاع ويندوز
بتتعمل بـ `root`. أول كتابة لـ `storage/framework/views` كانت بتفشل
والصفحة بترجع 500 برسالة `tempnam()` غامضة. الحل: `entrypoint.sh`
بيظبط الملكية عند كل إقلاع.

**٨. تسجيل موارد السياقات كان متأخر**

`Panel::configureUsing()` بيتطبّق وقت بناء الـ Panel اللي بيحصل في
`register()` بتاع `AdminPanelProvider`. لما كان التسجيل في `boot()`
بتاع مزوّدات السياقات، مكانش فيه ولا مورد بيظهر. الحل: التسجيل في
`register()`، ومزوّدات السياقات مرتّبة **قبل** مزوّد اللوحة.

### ✏️ انحرافات عن الوثائق (مقصودة)

| الوثيقة | اللي فيها | اللي عملناه | ليه |
|---|---|---|---|
| `docs/11` بند ٤ | `Spatie\Activitylog\Traits\LogsActivity` | `Spatie\Activitylog\Models\Concerns\LogsActivity` | v5 نقلت الـ namespaces. كمان `dontSubmitEmptyLogs()` → `dontLogEmptyChanges()` و `tapActivity()` → `beforeActivityLogged()` |
| `docs/02` بند ٨ | `bezhansalleh/filament-shield` | `RoleResource` بإيدنا | قيمة Shield الأساسية هي توليد الصلاحيات — وإحنا معطّلينه أصلاً لأن الصلاحيات من الكونفيج. الوثيقة نفسها بتدّي الخيار ده |
| `docs/08` بند ٦ | `pxlrbt/filament-excel` | التصدير المدمج في Filament v5 | first-party، مع طابور وإشعارات جاهزة، ومن غير اعتمادية زيادة |
| `docs/11` بند ٤ | `pxlrbt/filament-activity-log` | اتشال | v3.1.2 مبني على activitylog v4 — بيتعارض مع v5 المطلوبة في نفس الوثيقة |
| `docs/11` بند ٥ | `opcodesio/log-viewer` | مؤجّل | مفيش تكامل Filament مباشر ودعم Laravel 13 غير مؤكد |
| `docs/01` | Domain مايعرفش Filament | استثناء لعقود Filament | الوثائق نفسها بتستخدم `Filament\Support\Contracts` في `Domain/Enums` (docs/16) و `FilamentUser` على المستخدم (docs/02 بند ٦). الاستثناء محصور في الواجهات التوصيفية |
| `docs/03` بند ٥ | كل موديل فيه `tenant_id` عليه `BelongsToTenant` | استثناء لـ `Role` | العمود ده بتاع spatie teams والأدوار **عامة**. الاستثناء موثّق في الاختبار نفسه |
| `docs/00` بند ٦ | ٥٠ ألف صف بيانات وهمية | مؤجّل | يتضاف مع `DemoSeeder` في مرحلة الجداول |

### 🆕 إضافات مش في الوثائق

- **الدخول باسم المستخدم أو البريد** — عمود `username` وشاشة `Login`
  مخصصة بتقرر عمود التحقق حسب وجود `@`. القرار مابياخدش استعلام عشان
  مايبقاش وسيلة لتعداد الحسابات (`docs/20` بند ١٠).
- **سُلَّم الألوان لحد درجة 950** — Filament v5 بيتوقع ١١ درجة مش ١٠.
- **`phpstan-baseline.neon`** — level 6 نضيف، والدين المتبقي (٤٨ خطأ،
  معظمها تعليقات generics على العلاقات) متسجّل بدل ما يتخفي بخفض المستوى.
