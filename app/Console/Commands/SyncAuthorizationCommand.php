<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Src\Support\Infrastructure\Authorization\PermissionBuilder;

/**
 * الصلاحيات بتتعرّف في config/authorization.php وبتتزامن بالأمر ده.
 * مفيش Permission::create() في أي سيدر. (docs/02 بند ٣)
 */
final class SyncAuthorizationCommand extends Command
{
    protected $signature = 'authorization:sync
                            {--prune : حذف الصلاحيات غير الموجودة في الكونفيج}
                            {--dry   : عرض التغييرات من غير تنفيذ}';

    protected $description = 'مزامنة الصلاحيات والأدوار من config/authorization.php';

    public function handle(PermissionBuilder $builder, PermissionRegistrar $registrar): int
    {
        // الأدوار والصلاحيات **عامة** مش تابعة لمستأجر — الربط بالمستأجر
        // بيحصل في model_has_roles وقت الإسناد. لازم نصفّر الـ team id
        // هنا وإلا الأمر هيعمل نسخة من كل دور لكل مستأجر.
        $registrar->setPermissionsTeamId(null);

        $guard = (string) config('authorization.guard');
        $defined = $builder->allPermissionNames();
        $existing = Permission::query()->where('guard_name', $guard)->pluck('name')->all();

        $toCreate = array_values(array_diff($defined, $existing));
        $toPrune = array_values(array_diff($existing, $defined));

        $this->table(
            [__('authorization.sync.operation'), __('authorization.sync.count')],
            [
                [__('authorization.sync.add'), count($toCreate)],
                [__('authorization.sync.prunable'), count($toPrune)],
            ],
        );

        if ($this->option('dry')) {
            $this->listNames($toCreate, 'authorization.sync.add');
            $this->listNames($toPrune, 'authorization.sync.prunable');

            return self::SUCCESS;
        }

        foreach ($toCreate as $name) {
            Permission::create(['name' => $name, 'guard_name' => $guard]);
        }

        if ($this->option('prune') && $toPrune !== []) {
            Permission::query()->whereIn('name', $toPrune)->where('guard_name', $guard)->delete();
        }

        foreach ((array) config('authorization.roles', []) as $roleName => $patterns) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guard]);
            $role->syncPermissions($builder->expandPatterns($patterns));

            $this->line("  ✔ <info>{$roleName}</info> — ".$role->permissions()->count());
        }

        $registrar->forgetCachedPermissions();

        $this->info(__('authorization.sync.done'));

        return self::SUCCESS;
    }

    /** @param  list<string>  $names */
    private function listNames(array $names, string $titleKey): void
    {
        if ($names === []) {
            return;
        }

        $this->newLine();
        $this->line('<comment>'.__($titleKey).'</comment>');

        foreach ($names as $name) {
            $this->line("  · {$name}");
        }
    }
}
