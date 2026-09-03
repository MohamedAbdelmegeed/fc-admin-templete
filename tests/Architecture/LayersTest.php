<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| قواعد الطبقات (docs/01)
|--------------------------------------------------------------------------
| الاتجاه: Presentation → Application → Domain.
| Domain مايعرفش أي حد فوقه.
|
| استثناء موثّق: Domain مسموح له يستخدم عقود Filament فقط
| (Filament\Models\Contracts و Filament\Support\Contracts و Filament\Panel)
| — دي واجهات توصيف بس، والوثيقة نفسها بتستخدمها في Domain/Enums
| (docs/16، AnnouncementStatus) وفي موديل المستخدم (docs/02 بند ٦).
*/

arch('طبقة Application لا تعرف Filament')
    ->expect('Src\Contexts\Identity\Application')
    ->not->toUse(['Filament', 'Livewire'])
    ->group('arch');

arch('لا أدوات تصحيح متروكة في الكود')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r'])
    ->not->toBeUsed()
    ->group('arch');

arch('لا يُستخدم env() خارج ملفات config')
    ->expect('env')
    ->not->toBeUsedIn('Src')
    ->group('arch');

arch('كل الـ Actions نهائية وغير قابلة للتغيير')
    ->expect('Src\Contexts\Identity\Application\Actions')
    ->toBeFinal()
    ->toBeReadonly()
    ->group('arch');

arch('كل موديلات الدومين نهائية')
    ->expect('Src\Contexts\Identity\Domain\Models')
    ->toBeFinal()
    ->group('arch');

arch('كل ملفات المشروع فيها strict types')
    ->expect('Src')
    ->toUseStrictTypes()
    ->group('arch');

arch('سياق الوسائط لا يستورد موديلات الهوية مباشرة')
    ->expect('Src\Contexts\Media')
    ->not->toUse('Src\Contexts\Identity\Domain\Models')
    ->group('arch');
