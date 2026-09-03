<?php

declare(strict_types=1);

use Src\Contexts\Tenancy\Application\Actions\GrantTenantMembershipAction;
use Src\Contexts\Tenancy\Domain\Models\Tenant;

/*
|--------------------------------------------------------------------------
| «عملت مؤسسة ومقدرتش أدخلها»
|--------------------------------------------------------------------------
| العضوية في tenant_user لوحدها مش كفاية: canAccessTenant() بيطلب كمان
| صلاحية access.panel.* في سياق المؤسسة — واللي مصدرها دور مسجّل بـ
| tenant_id بتاعها في model_has_roles.
*/

it('يفتح لوحة المؤسسة الجديدة بدل ما يرفضها', function (): void {
    $creator = actingAsRole('admin');
    $created = Tenant::factory()->create(['is_active' => true]);

    app(GrantTenantMembershipAction::class)->handle($creator, $created);

    $this->actingAs($creator->fresh())
        ->get("/admin/t/{$created->slug}")
        ->assertSuccessful();
});

it('يرفض لوحة مؤسسة بلا دور داخلها', function (): void {
    $creator = actingAsRole('admin');
    $created = Tenant::factory()->create(['is_active' => true]);

    // العضوية وحدها — من غير الدور. ده بالظبط اللي كان بيحصل قبل الإصلاح.
    $creator->tenants()->syncWithoutDetaching([$created->getKey()]);

    // ⚠️ 404 مش 403: Filament بيخفي المؤسسة اللي مش مسموح بيها بدل ما
    // يقول «ممنوع» — وده سبب إن العطل بيبان كـ «الرابط فاضي».
    $this->actingAs($creator->fresh())
        ->get("/admin/t/{$created->slug}")
        ->assertNotFound();
});

it('يسجّل الدور على المؤسسة الجديدة نفسها لا على المؤسسة الحالية', function (): void {
    $creator = actingAsRole('admin');
    $created = Tenant::factory()->create();

    app(GrantTenantMembershipAction::class)->handle($creator, $created);

    expect(DB::table(config('permission.table_names.model_has_roles'))
        ->where('model_id', $creator->getKey())
        ->where('tenant_id', $created->getKey())
        ->exists())->toBeTrue();
});

it('يحافظ على دور المدير العام في المؤسسات الجديدة', function (): void {
    $super = actingAsRole('super_admin');
    $created = Tenant::factory()->create();

    $role = app(GrantTenantMembershipAction::class)->handle($super, $created);

    expect($role)->toBe('super_admin')
        ->and($super->fresh()->canAccessTenant($created))->toBeTrue();
});

it('يعطي غير المدير العام دور المنشئ من الكونفيج', function (): void {
    config()->set('authorization.tenant_creator_role', 'editor');

    $creator = actingAsRole('admin');
    $created = Tenant::factory()->create();

    $role = app(GrantTenantMembershipAction::class)->handle($creator, $created);

    expect($role)->toBe('editor')
        ->and($creator->fresh()->canAccessTenant($created))->toBeTrue();
});

it('لا يمنح وصولاً لمؤسسة المستخدم ليس عضواً فيها', function (): void {
    $creator = actingAsRole('admin');
    $foreign = Tenant::factory()->create();

    expect($creator->fresh()->canAccessTenant($foreign))->toBeFalse();
});
