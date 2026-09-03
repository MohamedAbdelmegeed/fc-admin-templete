<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;
use Src\Contexts\Identity\Domain\Enums\UserStatus;
use Src\Contexts\Identity\Domain\Models\User;
use Src\Contexts\Tenancy\Domain\Models\Tenant;
use Src\Support\Application\Contracts\TenantContext;

/**
 * البذور لازم تنتج الحالة اللي الوثيقة بتطلبها (docs/00 بند ٦):
 *   · مستأجرين اتنين: acme و beta
 *   · super_admin بيشوف كل حاجة
 *   · admin في acme بس
 *   · viewer في acme بصلاحيات قراءة
 *
 * مفيش Permission::create() هنا — الصلاحيات من الكونفيج عبر
 * authorization:sync. (docs/02 بند ١٠)
 */
final class DatabaseSeeder extends Seeder
{
    /**
     * كلمة مرور البذور — للتطوير بس.
     *
     * ⚠️ أقصر من الحد الأدنى اللي Password::defaults() بيفرضه (12 حرف)،
     * فهي بتعدّي هنا لأن السيدر بيعمل Hash مباشرة من غير تحقق. أي تغيير
     * من الواجهة هيتطلب كلمة أطول. متستخدمهاش في staging أو الإنتاج.
     */
    public const PASSWORD = 'fc1234@@';

    /** حساب المدير العام الرئيسي — الدخول باسم المستخدم مباشرة. */
    public const SUPER_USERNAME = 'suadmin';

    public const SUPER_PASSWORD = '123456fc';

    public function run(): void
    {
        Artisan::call('authorization:sync');

        $acme = $this->tenant('acme', 'شركة أكمي', 'Acme Inc.');
        $beta = $this->tenant('beta', 'مؤسسة بيتا', 'Beta Group');

        // المدير العام عضو في المؤسستين — عشان يقدر يشوف الاتنين.
        $this->user(
            'super@fc-admin.test',
            'مدير عام',
            'super_admin',
            [$acme, $beta],
            username: self::SUPER_USERNAME,
            password: self::SUPER_PASSWORD,
        );

        $this->user('admin@fc-admin.test', 'مدير أكمي', 'admin', [$acme], username: 'admin');
        $this->user('editor@fc-admin.test', 'محرّر أكمي', 'editor', [$acme], username: 'editor');
        $this->user('viewer@fc-admin.test', 'مشاهد أكمي', 'viewer', [$acme], username: 'viewer');
        $this->user('beta-admin@fc-admin.test', 'مدير بيتا', 'admin', [$beta], username: 'betaadmin');

        $this->command->newLine();
        $this->command->info('المدير العام: '.self::SUPER_USERNAME.' / '.self::SUPER_PASSWORD);
        $this->command->info('باقي الحسابات كلمة مرورها: '.self::PASSWORD);
    }

    private function tenant(string $slug, string $arabicName, string $englishName): Tenant
    {
        return Tenant::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => ['ar' => $arabicName, 'en' => $englishName],
                'description' => ['ar' => 'مؤسسة تجريبية للبذور', 'en' => 'Demo organization'],
                'primary_color' => '#12454F',
                'is_active' => true,
            ],
        );
    }

    /**
     * @param  list<Tenant>  $tenants
     */
    private function user(
        string $email,
        string $name,
        string $role,
        array $tenants,
        ?string $username = null,
        ?string $password = null,
    ): User {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'username' => $username,
                'password' => Hash::make($password ?? self::PASSWORD),
                'locale' => 'ar',
                'timezone' => config('app.timezone'),
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ],
        );

        // إعادة الضبط عشان تشغيل السيدر تاني يرجّع بيانات الدخول المعروفة.
        $user->forceFill([
            'username' => $username,
            'password' => Hash::make($password ?? self::PASSWORD),
        ])->save();

        $user->tenants()->syncWithoutDetaching(
            collect($tenants)->map(fn (Tenant $tenant): int => $tenant->getKey())->all(),
        );

        // الدور بيتسند **لكل مؤسسة على حدة** — teams بتاعة spatie
        // بتخزّن tenant_id في model_has_roles، فلازم نبدّل السياق.
        foreach ($tenants as $tenant) {
            app(TenantContext::class)->set($tenant->getKey());
            app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getKey());

            $user->unsetRelation('roles');

            if (! $user->hasRole($role)) {
                $user->assignRole($role);
            }
        }

        app(TenantContext::class)->set(null);

        return $user;
    }
}
