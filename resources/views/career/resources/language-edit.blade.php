@extends('layouts.app')

@section('content')
    @php($en = app()->getLocale() === 'en')

    <x-career.resource-edit-form
        :eyebrow="$en ? 'Language' : 'Idioma'"
        :title="$en ? 'Edit language' : 'Editar idioma'"
        :subtitle="$en ? 'Clarify communication range for national and international applications.' : 'Clarifique alcance de comunicação para candidaturas nacionais e internacionais.'"
        :action="route('languages.update', $language)"
    >
        <div class="form-row">
            <div class="field">
                <label>{{ $en ? 'Language' : 'Idioma' }}</label>
                <input name="language" value="{{ old('language', $language->language) }}" placeholder="{{ $en ? 'English' : 'Inglês' }}" required>
            </div>
            <div class="field">
                <label>{{ $en ? 'Proficiency' : 'Proficiência' }}</label>
                <input name="proficiency" value="{{ old('proficiency', $language->proficiency) }}" placeholder="{{ $en ? 'Advanced, fluent, native...' : 'Avançado, fluente, nativo...' }}" required>
            </div>
        </div>
        <div class="field">
            <label>{{ $en ? 'Notes' : 'Notas' }}</label>
            <textarea class="compact-textarea" name="notes" placeholder="{{ $en ? 'Certifications, business usage, interviews or writing context...' : 'Certificações, uso profissional, entrevistas ou contexto de escrita...' }}">{{ old('notes', $language->notes) }}</textarea>
        </div>
    </x-career.resource-edit-form>
@endsection
