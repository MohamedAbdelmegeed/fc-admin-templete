{{-- سُلَّم لون العلامة بيتحقن هنا عشان يتغيّر لكل مستأجر من غير build جديد --}}
<style>
    :root {
        @foreach ($scale as $shade => $hex)
            --fc-brand-{{ $shade }}: {{ $hex }};
        @endforeach
    }
</style>
