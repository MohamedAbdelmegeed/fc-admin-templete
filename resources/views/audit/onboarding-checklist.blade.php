{{-- قائمة التجهيز — بتختفي لوحدها لما كل البنود تخلص (canView) --}}
<x-filament-widgets::widget>
    <x-filament::section
        :heading="__('audit::audit.widgets.onboarding')"
        :description="__('audit::audit.onboarding.help')"
        icon="heroicon-o-rocket-launch"
        collapsible
    >
        <ul class="fc-checklist">
            @foreach ($this->steps() as $step)
                <li class="fc-checklist__item">
                    <span @class([
                        'fc-checklist__mark',
                        'fc-checklist__mark--done' => $step['done'],
                    ])>
                        <x-filament::icon
                            :icon="$step['done'] ? 'heroicon-m-check' : 'heroicon-m-minus'"
                            class="fc-checklist__icon"
                        />
                    </span>

                    <span @class([
                        'fc-checklist__label',
                        'fc-checklist__label--done' => $step['done'],
                    ])>
                        {{ __('audit::audit.onboarding.steps.'.$step['key']) }}
                    </span>

                    @if (! $step['done'] && $step['url'] !== null)
                        <a href="{{ $step['url'] }}" class="fc-checklist__action">
                            {{ __('audit::audit.onboarding.go') }}
                        </a>
                    @endif
                </li>
            @endforeach
        </ul>
    </x-filament::section>
</x-filament-widgets::widget>
