@props([
    'eyebrow',
    'title',
    'subtitle',
    'action',
    'guidanceTitle' => null,
    'guidanceItems' => [],
])

@php($en = app()->getLocale() === 'en')

<x-ui.page-header :eyebrow="$eyebrow" :title="$title" :subtitle="$subtitle">
    <x-slot:actions>
        <a class="btn secondary" href="{{ route('career.index') }}">{{ $en ? 'Back to inventory' : 'Voltar ao inventário' }}</a>
    </x-slot:actions>
</x-ui.page-header>

<section class="content-grid wide-aside">
    <form class="card stack-lg" method="POST" action="{{ $action }}" data-loading>
        @csrf
        @method('PUT')

        {{ $slot }}

        <div class="actions between">
            <a class="btn secondary" href="{{ route('career.index') }}">{{ $en ? 'Cancel' : 'Cancelar' }}</a>
            <button class="btn" type="submit" data-loading-text="{{ $en ? 'Saving...' : 'Salvando...' }}">{{ __('messages.common.save') }}</button>
        </div>
        <p class="loading-hint">{{ $en ? 'Saving inventory item...' : 'Salvando item do inventário...' }}</p>
    </form>

    <aside class="stack-lg">
        <article class="card stack">
            <div>
                <p class="eyebrow">{{ $en ? 'Guidance' : 'Orientação' }}</p>
                <h2>{{ $guidanceTitle ?: ($en ? 'Keep evidence useful' : 'Mantenha a evidência útil') }}</h2>
            </div>
            <ul class="split-list">
                @forelse ($guidanceItems as $item)
                    <li>{{ $item }}</li>
                @empty
                    <li>{{ $en ? 'Prefer concrete, reusable evidence over generic claims.' : 'Prefira evidências concretas e reutilizáveis em vez de afirmações genéricas.' }}</li>
                    <li>{{ $en ? 'Use technologies, scope and measurable outcomes when possible.' : 'Use tecnologias, escopo e resultados mensuráveis quando possível.' }}</li>
                @endforelse
            </ul>
        </article>
    </aside>
</section>
