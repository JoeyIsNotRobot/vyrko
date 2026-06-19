<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Vyrko') }}</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
        @stack('styles')
    </head>
    <body>
        <div class="app-shell">
            <header class="topbar">
                <div class="wrap nav">
                    <a class="brand" href="{{ auth()->check() ? route('dashboard') : route('home') }}" aria-label="Vyrko">
                        <span class="brand-mark">VY</span>
                        <span>Vyrko</span>
                    </a>
                    <button class="btn secondary menu-toggle" type="button" aria-label="{{ app()->getLocale() === 'en' ? 'Open menu' : 'Abrir menu' }}" aria-expanded="false" data-menu-toggle>
                        <span aria-hidden="true">☰</span>
                        <span>Menu</span>
                    </button>
                    <nav class="navlinks" data-menu>
                        @auth
                            <div class="nav-section">
                                <a class="navlink @if(request()->routeIs('dashboard')) is-active @endif" href="{{ route('dashboard') }}">{{ __('messages.nav.dashboard') }}</a>
                                <a class="navlink @if(request()->routeIs('career.*', 'experiences.*', 'achievements.*', 'skills.*', 'projects.*', 'educations.*', 'certifications.*', 'languages.*')) is-active @endif" href="{{ route('career.index') }}">{{ __('messages.nav.career') }}</a>
                                <a class="navlink @if(request()->routeIs('jobs.*')) is-active @endif" href="{{ route('jobs.index') }}">{{ app()->getLocale() === 'en' ? 'Jobs' : 'Vagas' }}</a>
                                <a class="navlink @if(request()->routeIs('resumes.*')) is-active @endif" href="{{ route('resumes.index') }}">{{ __('messages.nav.resumes') }}</a>
                                <a class="navlink @if(request()->routeIs('linkedin.*')) is-active @endif" href="{{ route('linkedin.index') }}">{{ __('messages.nav.linkedin') }}</a>
                                <a class="navlink @if(request()->routeIs('account.*')) is-active @endif" href="{{ route('account.connections') }}">{{ app()->getLocale() === 'en' ? 'Connections' : 'Conexões' }}</a>
                            </div>
                            <div class="nav-section nav-actions">
                                <select class="locale-select" aria-label="{{ app()->getLocale() === 'en' ? 'Language' : 'Idioma' }}" data-locale-select>
                                    <option value="{{ route('locale.switch', 'pt_BR') }}" @selected(app()->getLocale() === 'pt_BR')>PT-BR</option>
                                    <option value="{{ route('locale.switch', 'en') }}" @selected(app()->getLocale() === 'en')>EN</option>
                                </select>
                                <form class="nav-form" method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="btn secondary nav-logout" type="submit">{{ __('messages.nav.logout') }}</button>
                                </form>
                                <a class="btn" href="{{ route('jobs.create') }}">{{ app()->getLocale() === 'en' ? 'Analyze new job' : 'Analisar nova vaga' }}</a>
                            </div>
                        @else
                            <div class="nav-section">
                                <a class="navlink" href="{{ route('home') }}">Home</a>
                                <a class="navlink" href="{{ route('home') }}#como-funciona">{{ app()->getLocale() === 'en' ? 'How it works' : 'Como funciona' }}</a>
                                <a class="navlink" href="{{ route('home') }}#recursos">{{ app()->getLocale() === 'en' ? 'Features' : 'Recursos' }}</a>
                                <a class="navlink" href="{{ route('home') }}#precos">{{ app()->getLocale() === 'en' ? 'Pricing' : 'Preços' }}</a>
                            </div>
                            <div class="nav-section nav-actions">
                                <select class="locale-select" aria-label="{{ app()->getLocale() === 'en' ? 'Language' : 'Idioma' }}" data-locale-select>
                                    <option value="{{ route('locale.switch', 'pt_BR') }}" @selected(app()->getLocale() === 'pt_BR')>PT-BR</option>
                                    <option value="{{ route('locale.switch', 'en') }}" @selected(app()->getLocale() === 'en')>EN</option>
                                </select>
                                <a class="navlink @if(request()->routeIs('login')) is-active @endif" href="{{ route('login') }}">{{ __('messages.nav.login') }}</a>
                                <a class="btn @if(request()->routeIs('login', 'register')) secondary @endif" href="{{ route('register') }}">{{ app()->getLocale() === 'en' ? 'Start now' : 'Começar agora' }}</a>
                            </div>
                        @endauth
                    </nav>
                </div>
            </header>

            <main class="wrap page">
                @if (session('status'))
                    <div class="alert ok">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert error">
                        <strong>{{ __('messages.common.review_data') }}</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
        <script>
            window.Vyrko = {
                processingText: @json(app()->getLocale() === 'en' ? 'Processing...' : 'Processando...'),
                reviewDataText: @json(__('messages.common.review_data')),
                savedText: @json(__('messages.common.saved')),
            };
        </script>
        @stack('scripts')
    </body>
</html>
