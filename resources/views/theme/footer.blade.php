@php
    $footerText = $appearance?->footer($locale) ?? '';
@endphp

<footer class="fc-footer">
    <div class="fc-footer__inner">
        <span class="fc-footer__copy">{{ $footerText }}</span>

        <span class="fc-footer__by">
            {{ __('common.built_by') }}
            <a href="https://futurecode.example" target="_blank" rel="noopener noreferrer" class="fc-footer__link">
                {{ __('common.company') }}
            </a>
        </span>

        <span class="fc-footer__version fc-code">v{{ config('app.version') }}</span>
    </div>
</footer>
