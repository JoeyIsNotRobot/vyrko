@props([
    'eyebrow',
    'title',
    'subtitle' => null,
])

<header class="page-header">
    <div class="page-header-main">
        <p class="eyebrow">{{ $eyebrow }}</p>
        <h1>{{ $title }}</h1>
        @if ($subtitle)
            <p>{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="page-header-actions">
            {{ $actions }}
        </div>
    @endisset
</header>
