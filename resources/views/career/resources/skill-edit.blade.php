@extends('layouts.app')

@section('content')
    @php
        use App\Support\UiText;

        $en = app()->getLocale() === 'en';
    @endphp

    <x-career.resource-edit-form
        :eyebrow="$en ? 'Skill' : 'Habilidade'"
        :title="$en ? 'Edit skill' : 'Editar habilidade'"
        :subtitle="$en ? 'Keep skills categorized and backed by evidence so matching stays precise.' : 'Mantenha habilidades categorizadas e sustentadas por evidências para um match preciso.'"
        :action="route('skills.update', $skill)"
    >
        <div class="form-row">
            <div class="field">
                <label>{{ $en ? 'Name' : 'Nome' }}</label>
                <input name="name" value="{{ old('name', $skill->name) }}" placeholder="Laravel" required>
            </div>
            <div class="field">
                <label>{{ $en ? 'Category' : 'Categoria' }}</label>
                <select name="category">
                    @foreach (\App\Models\CandidateSkill::CATEGORIES as $category)
                        <option value="{{ $category }}" @selected(old('category', $skill->category) === $category)>{{ UiText::label('skill_categories', $category) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="field">
                <label>{{ $en ? 'Level' : 'Nível' }}</label>
                <input name="proficiency_level" value="{{ old('proficiency_level', $skill->proficiency_level) }}" placeholder="{{ $en ? 'Advanced, solid, learning...' : 'Avançado, sólido, aprendendo...' }}">
            </div>
            <div class="field">
                <label>{{ $en ? 'Years' : 'Anos' }}</label>
                <input name="years_of_experience" type="number" min="0" value="{{ old('years_of_experience', $skill->years_of_experience) }}" placeholder="5">
            </div>
        </div>
        <div class="field">
            <label>{{ $en ? 'Evidence' : 'Evidências' }}</label>
            <textarea class="compact-textarea" name="evidence_notes" placeholder="{{ $en ? 'Where and how you used this skill...' : 'Onde e como você usou esta habilidade...' }}">{{ old('evidence_notes', $skill->evidence_notes) }}</textarea>
            <p class="form-help">{{ $en ? 'Mention projects, systems, scale or outcomes.' : 'Mencione projetos, sistemas, escala ou resultados.' }}</p>
        </div>
    </x-career.resource-edit-form>
@endsection
