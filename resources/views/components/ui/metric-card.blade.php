@props([
    'label',
    'value',
    'suffix' => null,
    'meta' => null,
    'tone' => null,
])

<article class="metric-card {{ $tone ? 'metric-card-'.$tone : '' }}">
    <p class="metric-label">{{ $label }}</p>
    <div class="metric-value">{{ $value }}@if($suffix)<small>{{ $suffix }}</small>@endif</div>
    @if ($meta)
        <p class="metric-meta">{{ $meta }}</p>
    @endif
</article>
