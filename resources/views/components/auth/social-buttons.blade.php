@props([
    'googleLabel',
])

<div class="social-stack">
    <a class="social-button google" href="{{ route('auth.social.redirect', 'google') }}" data-loading-link data-loading-text="{{ app()->getLocale() === 'en' ? 'Connecting to Google...' : 'Conectando ao Google...' }}">
        <span class="social-mark">G</span>
        <span data-label>{{ $googleLabel }}</span>
    </a>
</div>
