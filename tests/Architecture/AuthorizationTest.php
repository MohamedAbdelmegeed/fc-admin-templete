<?php

declare(strict_types=1);

use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\File;
use Src\Support\Domain\Authorization\Policy;
use Symfony\Component\Finder\SplFileInfo;

/*
|--------------------------------------------------------------------------
| الاختبارات اللي بتفرض قواعد docs/19
|--------------------------------------------------------------------------
| القواعد دي مش اقتراحات — كسر أي واحدة فيها = رفض PR. الاختبارات هنا
| هي اللي بتخلّي الرفض ده آلي بدل ما يعتمد على انتباه المراجع.
*/

/**
 * @return list<SplFileInfo>
 */
function projectPhpFiles(): array
{
    $files = [];

    foreach ([base_path('src'), base_path('app')] as $directory) {
        if (! File::isDirectory($directory)) {
            continue;
        }

        foreach (File::allFiles($directory) as $file) {
            if ($file->getExtension() === 'php') {
                $files[] = $file;
            }
        }
    }

    return $files;
}

it('لا يُستخدم فحص صلاحية مباشر خارج طبقة التفويض', function (): void {
    // المكانان الوحيدان المسموح فيهما: طبقة التفويض نفسها، والـ Policies
    // (مشروع فيها: ->rule(! $target->hasRole('super_admin'), ...)).
    $allowedFiles = [
        'Support/Domain/Authorization/Decision.php',
        'Support/Infrastructure/Authorization/InvariantRegistry.php',
        'Providers/AuthorizationServiceProvider.php',
    ];

    $violations = [];

    foreach (projectPhpFiles() as $file) {
        $relative = str_replace('\\', '/', $file->getRelativePathname());

        if (str_contains($relative, 'Infrastructure/Policies/')) {
            continue;
        }

        if (in_array($relative, $allowedFiles, true)) {
            continue;
        }

        if (preg_match('/->(hasPermissionTo|hasRole|hasAnyRole|hasAllRoles)\(/', $file->getContents(), $matches)) {
            $violations[] = "{$relative}: {$matches[0]}";
        }
    }

    expect($violations)->toBeEmpty(
        "فحص صلاحية مباشر خارج طبقة التفويض:\n".implode("\n", $violations),
    );
})->group('arch');

it('لا تُستخدم أسماء الصلاحيات كنصوص في can()', function (): void {
    $separator = preg_quote((string) config('authorization.separator'), '/');
    $violations = [];

    foreach (projectPhpFiles() as $file) {
        if (! preg_match_all("/->can\(\s*'([a-z_]+{$separator}[a-z_.]+)'/", $file->getContents(), $matches)) {
            continue;
        }

        foreach ($matches[1] as $permission) {
            // استثناء: قدرات الصفحات والودجتس ليها Gates معرّفة.
            if (str_starts_with($permission, 'access.') || str_starts_with($permission, 'widget.')) {
                continue;
            }

            $violations[] = "{$file->getRelativePathname()}: can('{$permission}')";
        }
    }

    expect($violations)->toBeEmpty(
        "استخدام اسم صلاحية بدل اسم قدرة — استخدم can('ability', \$model):\n".implode("\n", $violations),
    );
})->group('arch');

it('لا يُستخدم skipAuthorization إطلاقاً', function (): void {
    $violations = [];

    foreach (projectPhpFiles() as $file) {
        if (str_contains($file->getContents(), '->skipAuthorization(')) {
            $violations[] = $file->getRelativePathname();
        }
    }

    expect($violations)->toBeEmpty('skipAuthorization ممنوع: '.implode(', ', $violations));
})->group('arch');

it('كل Policy ترث الكلاس الأساسي وكل دوالها ترجّع Response', function (): void {
    $policies = [];

    foreach (projectPhpFiles() as $file) {
        if (! str_contains(str_replace('\\', '/', $file->getRelativePathname()), 'Infrastructure/Policies/')) {
            continue;
        }

        $class = 'Src\\'.str_replace(
            ['/', '.php'],
            ['\\', ''],
            str_replace('\\', '/', $file->getRelativePathname()),
        );

        expect(class_exists($class))->toBeTrue("الكلاس {$class} مش موجود");
        expect(is_subclass_of($class, Policy::class))->toBeTrue("{$class} لازم ترث Policy");

        $policies[] = $class;
    }

    expect($policies)->not->toBeEmpty('مفيش أي Policy — الاختبار ده بيتخدع');

    $exempt = ['invariants', 'isInvariant', 'permissionFor', 'resourceName'];

    foreach ($policies as $policy) {
        foreach ((new ReflectionClass($policy))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (in_array($method->name, $exempt, true) || $method->isConstructor()) {
                continue;
            }

            expect((string) $method->getReturnType())
                ->toBe(Response::class, "{$policy}::{$method->name}() لازم ترجّع Response");
        }
    }
})->group('arch');

it('لا يوجد $guarded = [] في أي موديل', function (): void {
    $violations = [];

    foreach (projectPhpFiles() as $file) {
        // لازم يكون تصريح خاصية فعلي — مش ذكر للقاعدة في تعليق.
        if (preg_match('/(protected|public|private)\s+\$guarded\s*=\s*\[\s*\]/', $file->getContents())) {
            $violations[] = $file->getRelativePathname();
        }
    }

    expect($violations)->toBeEmpty('mass assignment مفتوح في: '.implode(', ', $violations));
})->group('arch');

it('لا يوجد whereRaw أو orderByRaw بمتغيّر مباشر', function (): void {
    $violations = [];

    foreach (projectPhpFiles() as $file) {
        if (preg_match('/(whereRaw|orderByRaw|havingRaw)\(\s*["\'][^"\']*\$/', $file->getContents(), $matches)) {
            $violations[] = "{$file->getRelativePathname()}: {$matches[1]}";
        }
    }

    expect($violations)->toBeEmpty("SQL خام بمدخل مباشر:\n".implode("\n", $violations));
})->group('arch');
