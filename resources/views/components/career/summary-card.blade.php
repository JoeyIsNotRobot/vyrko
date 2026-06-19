@props([
    'title',
    'count' => null,
    'meta' => null,
    'status' => null,
    'target' => null,
])

<article class="career-summary-card" @if($target) data-career-tab="{{ $target }}" role="button" tabindex="0" @endif>
    <div>
        <p class="career-summary-label">{{ $title }}</p>
        <strong>{{ $count }}</strong>
    </div>
    @if ($status)
        <span class="badge {{ $status === 'empty' ? 'warning' : '' }}">{{ $status === 'empty' ? (app()->getLocale() === 'en' ? 'Missing' : 'Pendente') : (app()->getLocale() === 'en' ? 'Ready' : 'Ok') }}</span>
    @endif
    @if ($meta)
        <p>{{ $meta }}</p>
    @endif
</article>
