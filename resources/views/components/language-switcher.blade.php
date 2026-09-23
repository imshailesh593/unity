@php
    $currentLocale = app()->getLocale();
@endphp
<div class="flex items-center gap-0.5 rounded-full bg-neutral-100 p-1 text-xs font-semibold" role="group" aria-label="Language">
    <a
        href="{{ route('locale.switch', 'en') }}"
        class="rounded-full px-2.5 py-1 transition-colors {{ $currentLocale === 'en' ? 'bg-white text-neutral-900 shadow-sm' : 'text-neutral-500 hover:text-neutral-700' }}"
        @if($currentLocale === 'en') aria-current="true" @endif
    >
        EN
    </a>
    <a
        href="{{ route('locale.switch', 'mr') }}"
        class="rounded-full px-2.5 py-1 transition-colors {{ $currentLocale === 'mr' ? 'bg-white text-neutral-900 shadow-sm' : 'text-neutral-500 hover:text-neutral-700' }}"
        @if($currentLocale === 'mr') aria-current="true" @endif
    >
        मर
    </a>
</div>
