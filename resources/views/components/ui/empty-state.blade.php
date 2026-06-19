@props([
    'title',
    'description',
    'ctaHref' => null,
    'ctaLabel' => null,
    'example' => null,
])

<div class="empty-state">
    <div>
        <h3>{{ $title }}</h3>
        <p>{{ $description }}</p>
        @if ($example)
            <p class="empty-example">{{ $example }}</p>
        @endif
    </div>

    @if ($ctaHref && $ctaLabel)
        <a class="btn" href="{{ $ctaHref }}">{{ $ctaLabel }}</a>
    @endif
</div>
