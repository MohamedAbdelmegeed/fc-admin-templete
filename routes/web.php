<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Src\Contexts\Identity\Application\Actions\SwitchLocaleAction;

Route::redirect('/', '/admin');

Route::middleware('web')->group(function (): void {
    Route::get('/locale/{locale}', function (string $locale) {
        app(SwitchLocaleAction::class)->handle($locale, auth()->user());

        return Redirect::back();
    })->name('fc.locale.switch');
});
