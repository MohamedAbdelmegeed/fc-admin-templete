{{--
    مبدّل اللغة — مجموعة مجزّأة (segmented) مش زرار أيقونة.

    ⚠️ النسخة القديمة كانت بتحطّ **نص** جوه .fi-icon-btn، وده مقاسه
    ثابت ٣٦px متعمّل لأيقونة ٢٠px وعليه هامش سالب — فالنص كان بيطفح
    من المربّع وبيتزحلق فوق باقي الشريط. وكمان .fi-icon-btn-label
    و.fi-dropdown مالهمش أي CSS في Filament v5، فالنتيجة عنصر بلا شكل.

    وكمان: اللغة الحالية كانت بتتشال بـ @continue، فالمستخدم مكانش
    شايف هو على أنهي لغة أصلاً.
--}}
@if (count($locales) > 1)
    <div
        class="fc-locale"
        role="group"
        aria-label="{{ __('common.locale_switcher') }}"
    >
        @foreach ($locales as $code)
            @php($isCurrent = $code === $current)

            <a
                href="{{ route('fc.locale.switch', ['locale' => $code]) }}"
                class="fc-locale__option{{ $isCurrent ? ' fc-locale__option--active' : '' }}"
                hreflang="{{ $code }}"
                title="{{ __('common.locales.'.$code) }}"
                @if ($isCurrent) aria-current="true" @endif
                @if ($isCurrent) tabindex="-1" @endif
            >
                <span aria-hidden="true">{{ __('common.locales_short.'.$code) }}</span>
                <span class="fi-sr-only">{{ __('common.locales.'.$code) }}</span>
            </a>
        @endforeach
    </div>
@endif
