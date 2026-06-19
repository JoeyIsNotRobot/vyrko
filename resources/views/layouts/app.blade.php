<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('meta_title', config('app.name', 'Vyrko'))</title>
        <meta name="description" content="@yield('meta_description', 'Analise vagas, identifique gaps e gere currículos personalizados com base em evidências reais do seu perfil.')">
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
                                <a class="navlink @if(request()->routeIs('linkedin-search.*')) is-active @endif" href="{{ route('linkedin-search.index') }}">
                                    {{ app()->getLocale() === 'en' ? 'Search Builder' : 'Search Builder' }}
                                    <span class="badge-free" style="margin-left:4px;">{{ app()->getLocale() === 'en' ? 'Free' : 'Grátis' }}</span>
                                </a>
                                <a class="navlink @if(request()->routeIs('account.*')) is-active @endif" href="{{ route('account.index') }}">{{ app()->getLocale() === 'en' ? 'My account' : 'Minha conta' }}</a>
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
                                <a class="navlink" href="{{ route('home') }}#home" data-section-link="home">Home</a>
                                <a class="navlink" href="{{ route('home') }}#como-funciona" data-section-link="como-funciona">{{ app()->getLocale() === 'en' ? 'How it works' : 'Como funciona' }}</a>
                                <a class="navlink" href="{{ route('home') }}#recursos" data-section-link="recursos">{{ app()->getLocale() === 'en' ? 'Features' : 'Recursos' }}</a>
                                <a class="navlink" href="{{ route('home') }}#precos" data-section-link="precos">{{ app()->getLocale() === 'en' ? 'Pricing' : 'Preços' }}</a>
                            </div>
                            <div class="nav-section nav-actions">
                                <select class="locale-select" aria-label="{{ app()->getLocale() === 'en' ? 'Language' : 'Idioma' }}" data-locale-select>
                                    <option value="{{ route('locale.switch', 'pt_BR') }}" @selected(app()->getLocale() === 'pt_BR')>PT-BR</option>
                                    <option value="{{ route('locale.switch', 'en') }}" @selected(app()->getLocale() === 'en')>EN</option>
                                </select>
                                <a class="navlink @if(request()->routeIs('login')) is-active @endif" href="{{ route('login') }}">{{ __('messages.nav.login') }}</a>
                                <a class="btn @if(request()->routeIs('login', 'register')) secondary @endif" href="{{ route('register') }}">{{ app()->getLocale() === 'en' ? 'Create my Inventory' : 'Criar meu Inventário' }}</a>
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

            <footer class="wrap footer-links">
                <span>© {{ date('Y') }} Vyrko</span>
                <a href="{{ route('legal.terms') }}">{{ app()->getLocale() === 'en' ? 'Terms' : 'Termos' }}</a>
                <a href="{{ route('legal.privacy') }}">{{ app()->getLocale() === 'en' ? 'Privacy' : 'Privacidade' }}</a>
                <a href="{{ route('legal.data-consent') }}">{{ app()->getLocale() === 'en' ? 'AI data consent' : 'Consentimento de IA' }}</a>
                <a href="{{ route('legal.social-data') }}">{{ app()->getLocale() === 'en' ? 'Social data' : 'Dados sociais' }}</a>
            </footer>
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
