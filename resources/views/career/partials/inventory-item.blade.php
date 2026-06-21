@php
    use App\Support\UiText;

    $en = app()->getLocale() === 'en';
    $title = '';
    $meta = '';
    $description = null;
    $badges = [];

    switch ($section) {
        case 'experiences':
            $title = "{$item->role_title} · {$item->company_name}";
            $meta = trim(implode(' · ', array_filter([
                $item->employment_type,
                $item->location,
                optional($item->start_date)->format('m/Y').($item->is_current ? ' - Atual' : ($item->end_date ? ' - '.optional($item->end_date)->format('m/Y') : '')),
            ])));
            $description = $item->description;
            $badges = $item->technologies ?? [];
            break;
        case 'achievements':
            $title = $item->title;
            $meta = $item->experience ? "{$item->experience->role_title} · {$item->experience->company_name}" : ($en ? 'No linked experience' : 'Sem experiência vinculada');
            $description = $item->impact_metric ?: $item->description;
            $badges = $item->evidence_tags ?? [];
            break;
        case 'skills':
            $title = $item->name;
            $meta = collect([
                UiText::label('skill_categories', $item->category),
                $item->proficiency_level,
                $item->years_of_experience ? ($en ? "{$item->years_of_experience} years" : "{$item->years_of_experience} anos") : null,
            ])->filter()->implode(' · ');
            $description = $item->evidence_notes;
            break;
        case 'projects':
            $title = $item->name;
            $meta = $item->role ?: ($en ? 'Project' : 'Projeto');
            $description = $item->description;
            $badges = $item->technologies ?? [];
            break;
        case 'educations':
            $title = "{$item->degree} · {$item->institution}";
            $meta = $item->field_of_study ?: ($en ? 'Education' : 'Formação');
            $description = $item->description;
            break;
        case 'certifications':
            $title = $item->name;
            $meta = $item->issuer ?: ($en ? 'Issuer not informed' : 'Emissor não informado');
            $description = $item->description;
            break;
        case 'languages':
            $title = $item->language;
            $meta = $item->proficiency;
            $description = $item->notes;
            break;
    }
@endphp

<div class="career-item inventory-item">
    <div class="actions" style="justify-content:space-between; align-items:flex-start">
        <div>
            <div class="career-item-title">
                {{ $title }}
                @if(session('just_imported'))
                    <span class="import-badge" aria-label="Preenchido pela IA durante importação">VIA IA</span>
                @endif
            </div>
            @if ($meta)
                <p class="career-item-meta">{{ $meta }}</p>
            @endif
            @if ($description)
                <p class="career-item-meta">{{ \Illuminate\Support\Str::limit($description, 180) }}</p>
            @endif
            @if ($badges !== [])
                <div class="actions" style="margin-top:10px">
                    @foreach (array_slice($badges, 0, 8) as $badge)
                        <span class="badge">{{ $badge }}</span>
                    @endforeach
                </div>
            @endif
        </div>

        <span class="actions">
            <button class="btn secondary" type="button" data-toggle-edit>{{ __('messages.common.edit') }}</button>
            <form method="POST" action="{{ route($section.'.destroy', $item) }}" data-career-ajax>
                @csrf
                @method('DELETE')
                <button class="btn danger" type="submit">{{ __('messages.common.remove') }}</button>
            </form>
        </span>
    </div>

    <div class="inline-edit" hidden></div>
    <template data-edit-template>
        @include('career.partials.forms.edit', ['section' => $section, 'item' => $item, 'user' => $user])
    </template>
</div>
