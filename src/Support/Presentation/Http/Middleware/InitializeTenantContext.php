<?php

declare(strict_types=1);

namespace Src\Support\Presentation\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Src\Support\Application\Contracts\TenantContext;
use Symfony\Component\HttpFoundation\Response;

/**
 * بيربط مستأجر Filament بـ TenantContext بتاعنا — وده اللي بيزامن فرق
 * spatie/permission وبادئة الكاش وسياق اللوج. لازم يكون persistent
 * عشان يشتغل على طلبات Livewire كمان. (docs/03 بند ٤)
 */
final class InitializeTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        app(TenantContext::class)->set(Filament::getTenant()?->getKey());

        return $next($request);
    }
}
