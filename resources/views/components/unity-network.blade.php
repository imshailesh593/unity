{{-- A network of individual people (dots) converging into one connected
     community (the centre node) — a literal visualisation of "Unity". --}}
<div class="mx-auto mt-14 max-w-3xl">
    <svg viewBox="0 0 800 220" class="w-full" role="img" aria-label="{{ __('site.home.network_aria_label') }}">
        <g stroke-width="1.5" fill="none" opacity="0.35">
            <line x1="400" y1="110" x2="120" y2="60" stroke="var(--color-brand-orange)" />
            <line x1="400" y1="110" x2="230" y2="150" stroke="var(--color-brand-teal)" />
            <line x1="400" y1="110" x2="320" y2="45" stroke="var(--color-brand-purple)" />
            <line x1="400" y1="110" x2="480" y2="40" stroke="var(--color-brand-magenta)" />
            <line x1="400" y1="110" x2="565" y2="130" stroke="var(--color-brand-teal)" />
            <line x1="400" y1="110" x2="655" y2="65" stroke="var(--color-brand-orange)" />
            <line x1="400" y1="110" x2="700" y2="155" stroke="var(--color-brand-purple)" />
            <line x1="400" y1="110" x2="250" y2="180" stroke="var(--color-brand-blue)" />
            <line x1="400" y1="110" x2="555" y2="185" stroke="var(--color-brand-blue)" />
            <line x1="120" y1="60" x2="320" y2="45" stroke="#d4d4d4" />
            <line x1="230" y1="150" x2="250" y2="180" stroke="#d4d4d4" />
            <line x1="480" y1="40" x2="655" y2="65" stroke="#d4d4d4" />
            <line x1="565" y1="130" x2="555" y2="185" stroke="#d4d4d4" />
            <line x1="655" y1="65" x2="700" y2="155" stroke="#d4d4d4" />
        </g>

        <circle class="unity-node" cx="400" cy="110" r="17" fill="var(--color-brand-blue)" style="animation-delay:0s" />

        <circle class="unity-node" cx="120" cy="60" r="9" fill="var(--color-brand-orange)" style="animation-delay:.2s" />
        <circle class="unity-node" cx="230" cy="150" r="8" fill="var(--color-brand-teal)" style="animation-delay:.4s" />
        <circle class="unity-node" cx="320" cy="45" r="10" fill="var(--color-brand-purple)" style="animation-delay:.6s" />
        <circle class="unity-node" cx="480" cy="40" r="8" fill="var(--color-brand-magenta)" style="animation-delay:.8s" />
        <circle class="unity-node" cx="565" cy="130" r="9" fill="var(--color-brand-teal)" style="animation-delay:1s" />
        <circle class="unity-node" cx="655" cy="65" r="10" fill="var(--color-brand-orange)" style="animation-delay:1.2s" />
        <circle class="unity-node" cx="700" cy="155" r="8" fill="var(--color-brand-purple)" style="animation-delay:1.4s" />
        <circle class="unity-node" cx="250" cy="180" r="7" fill="var(--color-brand-blue)" style="animation-delay:1.6s" />
        <circle class="unity-node" cx="555" cy="185" r="7" fill="var(--color-brand-blue)" style="animation-delay:1.8s" />
    </svg>
    <p class="mt-4 text-center text-sm text-neutral-500">{{ __('site.home.network_caption') }}</p>
</div>
