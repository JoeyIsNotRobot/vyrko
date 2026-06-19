@props([
    'googleLabel',
    'linkedinLabel',
])

<div class="social-stack">
    <a class="social-button google" href="{{ route('auth.social.redirect', 'google') }}" data-loading-link data-loading-text="{{ app()->getLocale() === 'en' ? 'Connecting to Google...' : 'Conectando ao Google...' }}">
        <span class="social-mark">G</span>
        <span data-label>{{ $googleLabel }}</span>
    </a>
    <a class="social-button linkedin" href="{{ route('auth.social.redirect', 'linkedin') }}" data-loading-link data-loading-text="{{ app()->getLocale() === 'en' ? 'Connecting to LinkedIn...' : 'Conectando ao LinkedIn...' }}">
        <span class="social-mark">in</span>
        <span data-label>{{ $linkedinLabel }}</span>
    </a>
</div>
