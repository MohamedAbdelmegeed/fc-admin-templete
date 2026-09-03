<?php

declare(strict_types=1);

namespace Src\Support\Infrastructure\Authorization;

/**
 * المصدر الوحيد لأسماء الصلاحيات — بيفكّ config/authorization.php لأسماء
 * نهائية، وبيوسّع أنماط الـ wildcard، وبيجمّعها للعرض في شاشة الأدوار.
 *
 * مفيش Permission::create() في أي سيدر. الكونفيج هو الحقيقة. (docs/02 بند ٢)
 */
final class PermissionBuilder
{
    /** @var list<string>|null */
    private ?array $cachedNames = null;

    /**
     * كل أسماء الصلاحيات المعرّفة في الكونفيج.
     *
     * @return list<string>
     */
    public function allPermissionNames(): array
    {
        if ($this->cachedNames !== null) {
            return $this->cachedNames;
        }

        $names = [];

        foreach (array_keys($this->resources()) as $resource) {
            foreach ($this->actionsFor($resource) as $action) {
                $names[] = $this->name($action, $resource);
            }
        }

        $names = [
            ...$names,
            ...array_keys((array) config('authorization.pages', [])),
            ...array_keys((array) config('authorization.widgets', [])),
        ];

        return $this->cachedNames = array_values(array_unique($names));
    }

    /**
     * الأفعال المتاحة لمورد معيّن (القياسية أو المحددة + الإضافية).
     *
     * @return list<string>
     */
    public function actionsFor(string $resource): array
    {
        $definition = $this->resources()[$resource] ?? null;

        if ($definition === null) {
            return [];
        }

        $actions = $definition['actions'] ?? null
            ?: (array) config('authorization.default_actions', []);

        return array_values(array_unique([
            ...$actions,
            ...($definition['extra'] ?? []),
        ]));
    }

    /**
     * @return list<string>
     */
    public function permissionsForResource(string $resource): array
    {
        return array_map(
            fn (string $action): string => $this->name($action, $resource),
            $this->actionsFor($resource),
        );
    }

    /**
     * يفكّ أنماط الأدوار لأسماء صلاحيات فعلية.
     *
     * '*'           → كل حاجة
     * 'users.*'     → كل صلاحيات المورد users        (المورد على الشمال)
     * 'view_any.*'  → الفعل view_any على كل الموارد  (الفعل على الشمال)
     * 'widget.*'    → كل الودجتس                     (بادئة حرفية)
     *
     * @param  list<string>  $patterns
     * @return list<string>
     */
    public function expandPatterns(array $patterns): array
    {
        $all = $this->allPermissionNames();
        $expanded = [];

        foreach ($patterns as $pattern) {
            if ($pattern === '*') {
                return $all;
            }

            $expanded = [...$expanded, ...$this->expand($pattern, $all)];
        }

        return array_values(array_unique($expanded));
    }

    /**
     * الصلاحيات مجمّعة للعرض في شاشة الأدوار.
     *
     * الشكل: group => section => list<permission>
     * والـ section إما اسم مورد، أو '_pages'، أو '_widgets'.
     *
     * @return array<string, array<string, list<string>>>
     */
    public function groups(): array
    {
        $groups = [];

        foreach ($this->resources() as $resource => $definition) {
            $group = $definition['group'] ?? 'system';
            $groups[$group][$resource] = $this->permissionsForResource($resource);
        }

        foreach (['pages' => '_pages', 'widgets' => '_widgets'] as $configKey => $section) {
            foreach ((array) config("authorization.{$configKey}", []) as $permission => $group) {
                $groups[$group][$section][] = $permission;
            }
        }

        return $groups;
    }

    public function name(string $action, string $resource): string
    {
        return $action.$this->separator().$resource;
    }

    /**
     * @param  list<string>  $all
     * @return list<string>
     */
    private function expand(string $pattern, array $all): array
    {
        $separator = $this->separator();

        if (! str_contains($pattern, $separator)) {
            return in_array($pattern, $all, true) ? [$pattern] : [];
        }

        [$left, $right] = explode($separator, $pattern, 2);

        if ($right !== '*') {
            return in_array($pattern, $all, true) ? [$pattern] : [];
        }

        // 'users.*' — الشمال اسم مورد، يبقى المطابقة على النهاية.
        if (array_key_exists($left, $this->resources())) {
            return $this->permissionsForResource($left);
        }

        // 'view_any.*' و 'access.*' و 'widget.*' — مطابقة بادئة.
        return array_values(array_filter(
            $all,
            static fn (string $permission): bool => str_starts_with($permission, $left.$separator),
        ));
    }

    /** @return array<string, array{group?: string, actions?: list<string>|null, extra?: list<string>}> */
    private function resources(): array
    {
        return (array) config('authorization.resources', []);
    }

    private function separator(): string
    {
        return (string) config('authorization.separator', '.');
    }
}
