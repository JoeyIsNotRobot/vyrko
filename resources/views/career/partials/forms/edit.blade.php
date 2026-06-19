@php
    use App\Models\CandidateSkill;
    use App\Support\UiText;
@endphp

@switch($section)
    @case('experiences')
        <form class="stack" method="POST" action="{{ route('experiences.update', $item) }}" data-career-ajax>
            @csrf
            @method('PUT')
            <div class="form-row">
                <div><label>{{ __('messages.fields.company') }}</label><input name="company_name" value="{{ $item->company_name }}" required></div>
                <div><label>{{ __('messages.fields.role') }}</label><input name="role_title" value="{{ $item->role_title }}" required></div>
            </div>
            <div class="form-row">
                <div><label>{{ __('messages.fields.type') }}</label><input name="employment_type" value="{{ $item->employment_type }}"></div>
                <div><label>{{ __('messages.fields.location') }}</label><input name="location" value="{{ $item->location }}"></div>
            </div>
            <div class="form-row">
                <div><label>{{ __('messages.fields.start') }}</label><input name="start_date" type="date" value="{{ optional($item->start_date)->format('Y-m-d') }}"></div>
                <div><label>{{ __('messages.fields.end') }}</label><input name="end_date" type="date" value="{{ optional($item->end_date)->format('Y-m-d') }}"></div>
            </div>
            <label><input name="is_current" type="checkbox" value="1" @checked($item->is_current)> {{ __('messages.fields.current') }}</label>
            <div><label>{{ __('messages.fields.description') }}</label><textarea name="description">{{ $item->description }}</textarea></div>
            <div><label>{{ __('messages.fields.responsibilities') }}</label><textarea name="responsibilities">{{ implode("\n", $item->responsibilities ?? []) }}</textarea></div>
            <div><label>{{ __('messages.fields.technologies') }}</label><input name="technologies" value="{{ implode(', ', $item->technologies ?? []) }}"></div>
            <div class="actions"><button class="btn" type="submit">{{ __('messages.common.save') }}</button><button class="btn secondary" type="button" data-toggle-edit>{{ __('messages.common.cancel') }}</button></div>
        </form>
        @break

    @case('achievements')
        <form class="stack" method="POST" action="{{ route('achievements.update', $item) }}" data-career-ajax>
            @csrf
            @method('PUT')
            <div><label>{{ __('messages.fields.linked_experience') }}</label><select name="candidate_experience_id">@foreach ($user->candidateExperiences as $experience)<option value="{{ $experience->id }}" @selected($item->candidate_experience_id === $experience->id)>{{ $experience->role_title }} - {{ $experience->company_name }}</option>@endforeach<option value="" @selected(! $item->candidate_experience_id)>Sem vínculo</option></select></div>
            <div><label>{{ __('messages.fields.title') }}</label><input name="title" value="{{ $item->title }}" required></div>
            <div><label>{{ __('messages.fields.description') }}</label><textarea name="description" required>{{ $item->description }}</textarea></div>
            <div><label>{{ __('messages.fields.metric') }}</label><input name="impact_metric" value="{{ $item->impact_metric }}"></div>
            <div><label>{{ __('messages.fields.tags') }}</label><input name="evidence_tags" value="{{ implode(', ', $item->evidence_tags ?? []) }}"></div>
            <div class="actions"><button class="btn" type="submit">{{ __('messages.common.save') }}</button><button class="btn secondary" type="button" data-toggle-edit>{{ __('messages.common.cancel') }}</button></div>
        </form>
        @break

    @case('skills')
        <form class="stack" method="POST" action="{{ route('skills.update', $item) }}" data-career-ajax>
            @csrf
            @method('PUT')
            <div class="form-row">
                <div><label>{{ __('messages.fields.name') }}</label><input name="name" value="{{ $item->name }}" required></div>
                <div><label>{{ __('messages.fields.category') }}</label><select name="category">@foreach (CandidateSkill::CATEGORIES as $category)<option value="{{ $category }}" @selected($item->category === $category)>{{ UiText::label('skill_categories', $category) }}</option>@endforeach</select></div>
            </div>
            <div class="form-row">
                <div><label>{{ __('messages.fields.level') }}</label><input name="proficiency_level" value="{{ $item->proficiency_level }}"></div>
                <div><label>{{ __('messages.fields.years') }}</label><input name="years_of_experience" type="number" min="0" value="{{ $item->years_of_experience }}"></div>
            </div>
            <div><label>{{ __('messages.fields.evidence') }}</label><textarea name="evidence_notes">{{ $item->evidence_notes }}</textarea></div>
            <div class="actions"><button class="btn" type="submit">{{ __('messages.common.save') }}</button><button class="btn secondary" type="button" data-toggle-edit>{{ __('messages.common.cancel') }}</button></div>
        </form>
        @break

    @case('projects')
        <form class="stack" method="POST" action="{{ route('projects.update', $item) }}" data-career-ajax>
            @csrf
            @method('PUT')
            <div class="form-row">
                <div><label>{{ __('messages.fields.name') }}</label><input name="name" value="{{ $item->name }}" required></div>
                <div><label>{{ __('messages.fields.role') }}</label><input name="role" value="{{ $item->role }}"></div>
            </div>
            <div><label>{{ __('messages.fields.description') }}</label><textarea name="description">{{ $item->description }}</textarea></div>
            <div><label>{{ __('messages.fields.technologies') }}</label><input name="technologies" value="{{ implode(', ', $item->technologies ?? []) }}"></div>
            <div class="form-row">
                <div><label>URL</label><input name="url" value="{{ $item->url }}"></div>
                <div><label>{{ __('messages.fields.repository') }}</label><input name="repository_url" value="{{ $item->repository_url }}"></div>
            </div>
            <div><label>{{ __('messages.fields.highlights') }}</label><textarea name="highlights">{{ implode("\n", $item->highlights ?? []) }}</textarea></div>
            <div class="actions"><button class="btn" type="submit">{{ __('messages.common.save') }}</button><button class="btn secondary" type="button" data-toggle-edit>{{ __('messages.common.cancel') }}</button></div>
        </form>
        @break

    @case('educations')
        <form class="stack" method="POST" action="{{ route('educations.update', $item) }}" data-career-ajax>
            @csrf
            @method('PUT')
            <div class="form-row">
                <div><label>{{ __('messages.fields.institution') }}</label><input name="institution" value="{{ $item->institution }}" required></div>
                <div><label>{{ __('messages.fields.degree') }}</label><input name="degree" value="{{ $item->degree }}" required></div>
            </div>
            <div><label>{{ __('messages.fields.field') }}</label><input name="field_of_study" value="{{ $item->field_of_study }}"></div>
            <div class="form-row">
                <div><label>{{ __('messages.fields.start') }}</label><input name="start_date" type="date" value="{{ optional($item->start_date)->format('Y-m-d') }}"></div>
                <div><label>{{ __('messages.fields.end') }}</label><input name="end_date" type="date" value="{{ optional($item->end_date)->format('Y-m-d') }}"></div>
            </div>
            <label><input name="is_current" type="checkbox" value="1" @checked($item->is_current)> {{ __('messages.fields.current') }}</label>
            <div><label>{{ __('messages.fields.description') }}</label><textarea name="description">{{ $item->description }}</textarea></div>
            <div class="actions"><button class="btn" type="submit">{{ __('messages.common.save') }}</button><button class="btn secondary" type="button" data-toggle-edit>{{ __('messages.common.cancel') }}</button></div>
        </form>
        @break

    @case('certifications')
        <form class="stack" method="POST" action="{{ route('certifications.update', $item) }}" data-career-ajax>
            @csrf
            @method('PUT')
            <div class="form-row">
                <div><label>{{ __('messages.fields.name') }}</label><input name="name" value="{{ $item->name }}" required></div>
                <div><label>{{ __('messages.fields.issuer') }}</label><input name="issuer" value="{{ $item->issuer }}"></div>
            </div>
            <div class="form-row">
                <div><label>{{ __('messages.fields.issued_at') }}</label><input name="issued_at" type="date" value="{{ optional($item->issued_at)->format('Y-m-d') }}"></div>
                <div><label>{{ __('messages.fields.expires_at') }}</label><input name="expires_at" type="date" value="{{ optional($item->expires_at)->format('Y-m-d') }}"></div>
            </div>
            <div><label>{{ __('messages.fields.credential_url') }}</label><input name="credential_url" value="{{ $item->credential_url }}"></div>
            <div><label>{{ __('messages.fields.description') }}</label><textarea name="description">{{ $item->description }}</textarea></div>
            <div class="actions"><button class="btn" type="submit">{{ __('messages.common.save') }}</button><button class="btn secondary" type="button" data-toggle-edit>{{ __('messages.common.cancel') }}</button></div>
        </form>
        @break

    @case('languages')
        <form class="stack" method="POST" action="{{ route('languages.update', $item) }}" data-career-ajax>
            @csrf
            @method('PUT')
            <div class="form-row">
                <div><label>{{ __('messages.fields.name') }}</label><input name="language" value="{{ $item->language }}" required></div>
                <div><label>{{ __('messages.fields.proficiency') }}</label><input name="proficiency" value="{{ $item->proficiency }}" required></div>
            </div>
            <div><label>{{ __('messages.fields.notes') }}</label><textarea name="notes">{{ $item->notes }}</textarea></div>
            <div class="actions"><button class="btn" type="submit">{{ __('messages.common.save') }}</button><button class="btn secondary" type="button" data-toggle-edit>{{ __('messages.common.cancel') }}</button></div>
        </form>
        @break
@endswitch
