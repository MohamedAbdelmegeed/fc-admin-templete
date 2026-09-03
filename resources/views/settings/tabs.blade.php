{{--
    شريط تبويبات الإعدادات — بيظهر فوق كل صفحة إعدادات.

    التبويبات بتتفلتر بـ canAccess()، فمفيش تبويب بيودّي لصفحة ممنوعة.
    ده تنظيم للقائمة مش حماية — السياسة على كل صفحة هي اللي بتحمي.
--}}
@if (count($tabs) > 1)
    <nav class="fc-settings-tabs" aria-label="{{ __('settings::settings.pages.index') }}">
        @foreach ($tabs as $tab)
            <a
                href="{{ $tab['url'] }}"
                class="fc-settings-tabs__item{{ $tab['active'] ? ' fc-settings-tabs__item--active' : '' }}"
                @if ($tab['active']) aria-current="page" @endif
            >
                {{ $tab['label'] }}
            </a>
        @endforeach
    </nav>
@endif
