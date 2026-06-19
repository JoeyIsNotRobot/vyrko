@php
    use App\Models\CandidateSkill;
    use App\Support\UiText;
@endphp

@switch($section)
    @case('experiences')
        <form class="stack" method="POST" action="{{ route('experiences.store') }}" data-career-ajax data-reset-on-success>
            @csrf
            <div class="form-row">
                <div><label>{{ __('messages.fields.company') }}</label><input name="company_name" required></div>
                <div><label>{{ __('messages.fields.role') }}</label><input name="role_title" required></div>
            </div>
            <div class="form-row">
                <div><label>{{ __('messages.fields.type') }}</label><input name="employment_type" placeholder="CLT, PJ, contrato"></div>
                <div><label>{{ __('messages.fields.location') }}</label><input name="location"></div>
            </div>
            <div class="form-row">
                <div><label>{{ __('messages.fields.start') }}</label><input name="start_date" type="date"></div>
                <div><label>{{ __('messages.fields.end') }}</label><input name="end_date" type="date"></div>
            </div>
            <label><input name="is_current" type="checkbox" value="1"> {{ __('messages.fields.current') }}</label>
            <div><label>{{ __('messages.fields.description') }}</label><textarea name="description"></textarea></div>
            <div><label>{{ __('messages.fields.responsibilities') }}</label><textarea name="responsibilities"></textarea></div>
            <div><label>{{ __('messages.fields.technologies') }}</label><input name="technologies" placeholder="Laravel, MySQL, Redis"></div>
            <button class="btn" type="submit">{{ __('messages.actions.add_experience') }}</button>
        </form>
        @break

    @case('achievements')
        <form class="stack" method="POST" action="{{ route('achievements.store') }}" data-career-ajax data-reset-on-success>
            @csrf
            <div><label>{{ __('messages.fields.linked_experience') }}</label><select id="achievement-experience-options" name="candidate_experience_id">@include('career.partials.experience-options', ['experiences' => $user->candidateExperiences])</select></div>
            <div><label>{{ __('messages.fields.title') }}</label><input name="title" required></div>
            <div><label>{{ __('messages.fields.description') }}</label><textarea name="description" required></textarea></div>
            <div><label>{{ __('messages.fields.metric') }}</label><input name="impact_metric" placeholder="Ex: tempo médio 35% menor"></div>
            <div><label>{{ __('messages.fields.tags') }}</label><input name="evidence_tags" placeholder="Laravel, MySQL, performance"></div>
            <button class="btn" type="submit">{{ __('messages.actions.add_achievement') }}</button>
        </form>
        @break

    @case('skills')
        <form class="stack" method="POST" action="{{ route('skills.store') }}" data-career-ajax data-reset-on-success>
            @csrf
            <div class="form-row">
                <div><label>{{ __('messages.fields.name') }}</label><input name="name" required></div>
                <div><label>{{ __('messages.fields.category') }}</label><select name="category">@foreach (CandidateSkill::CATEGORIES as $category)<option value="{{ $category }}">{{ UiText::label('skill_categories', $category) }}</option>@endforeach</select></div>
            </div>
            <div class="form-row">
                <div><label>{{ __('messages.fields.level') }}</label><input name="proficiency_level"></div>
                <div><label>{{ __('messages.fields.years') }}</label><input name="years_of_experience" type="number" min="0"></div>
            </div>
            <div><label>{{ __('messages.fields.evidence') }}</label><textarea name="evidence_notes"></textarea></div>
            <button class="btn" type="submit">{{ __('messages.actions.add_skill') }}</button>
        </form>
        @break

    @case('projects')
        <form class="stack" method="POST" action="{{ route('projects.store') }}" data-career-ajax data-reset-on-success>
            @csrf
            <div class="form-row">
                <div><label>{{ __('messages.fields.name') }}</label><input name="name" required></div>
                <div><label>{{ __('messages.fields.role') }}</label><input name="role"></div>
            </div>
            <div><label>{{ __('messages.fields.description') }}</label><textarea name="description"></textarea></div>
            <div><label>{{ __('messages.fields.technologies') }}</label><input name="technologies" placeholder="Laravel, Redis"></div>
            <div class="form-row">
                <div><label>URL</label><input name="url"></div>
                <div><label>{{ __('messages.fields.repository') }}</label><input name="repository_url"></div>
            </div>
            <div><label>{{ __('messages.fields.highlights') }}</label><textarea name="highlights"></textarea></div>
            <button class="btn" type="submit">{{ __('messages.actions.add_project') }}</button>
        </form>
        @break

    @case('educations')
        <form class="stack" method="POST" action="{{ route('educations.store') }}" data-career-ajax data-reset-on-success>
            @csrf
            <div class="form-row">
                <div><label>{{ __('messages.fields.institution') }}</label><input name="institution" required></div>
                <div><label>{{ __('messages.fields.degree') }}</label><input name="degree" required></div>
            </div>
            <div><label>{{ __('messages.fields.field') }}</label><input name="field_of_study"></div>
            <div class="form-row">
                <div><label>{{ __('messages.fields.start') }}</label><input name="start_date" type="date"></div>
                <div><label>{{ __('messages.fields.end') }}</label><input name="end_date" type="date"></div>
            </div>
            <label><input name="is_current" type="checkbox" value="1"> {{ __('messages.fields.current') }}</label>
            <div><label>{{ __('messages.fields.description') }}</label><textarea name="description"></textarea></div>
            <button class="btn" type="submit">{{ __('messages.actions.add_education') }}</button>
        </form>
        @break

    @case('certifications')
        <form class="stack" method="POST" action="{{ route('certifications.store') }}" data-career-ajax data-reset-on-success>
            @csrf
            <div class="form-row">
                <div><label>{{ __('messages.fields.name') }}</label><input name="name" required></div>
                <div><label>{{ __('messages.fields.issuer') }}</label><input name="issuer"></div>
            </div>
            <div class="form-row">
                <div><label>{{ __('messages.fields.issued_at') }}</label><input name="issued_at" type="date"></div>
                <div><label>{{ __('messages.fields.expires_at') }}</label><input name="expires_at" type="date"></div>
            </div>
            <div><label>{{ __('messages.fields.credential_url') }}</label><input name="credential_url"></div>
            <div><label>{{ __('messages.fields.description') }}</label><textarea name="description"></textarea></div>
            <button class="btn" type="submit">{{ __('messages.actions.add_certification') }}</button>
        </form>
        @break

    @case('languages')
        <form class="stack" method="POST" action="{{ route('languages.store') }}" data-career-ajax data-reset-on-success>
            @csrf
            <div class="form-row">
                <div><label>{{ __('messages.fields.name') }}</label><input name="language" required></div>
                <div><label>{{ __('messages.fields.proficiency') }}</label><input name="proficiency" required></div>
            </div>
            <div><label>{{ __('messages.fields.notes') }}</label><textarea name="notes"></textarea></div>
            <button class="btn" type="submit">{{ __('messages.actions.add_language') }}</button>
        </form>
        @break
@endswitch
