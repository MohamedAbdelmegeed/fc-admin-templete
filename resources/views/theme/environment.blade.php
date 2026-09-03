<div class="fc-env">
    <span class="fc-env__badge fc-env__badge--{{ $environment }}">
        {{ __('navigation.environment.'.$environment) }}
    </span>
    <span class="fc-code">v{{ config('app.version') }}</span>
</div>
