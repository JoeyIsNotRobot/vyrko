@extends('layouts.app')

@section('content')
    @php($en = app()->getLocale() === 'en')

    <x-career.resource-edit-form
        :eyebrow="$en ? 'Experience' : 'Experiência'"
        :title="$en ? 'Edit experience' : 'Editar experiência'"
        :subtitle="$en ? 'Structure role scope, responsibilities and technologies for stronger evidence matching.' : 'Estruture escopo, responsabilidades e tecnologias para melhorar o match de evidências.'"
        :action="route('experiences.update', $experience)"
        :guidance-title="$en ? 'Strong experience entries' : 'Experiências fortes'"
        :guidance-items="[
            $en ? 'Lead with role scope and systems you owned.' : 'Comece pelo escopo do cargo e sistemas sob sua responsabilidade.',
            $en ? 'Split responsibilities line by line.' : 'Separe responsabilidades linha por linha.',
            $en ? 'List technologies as comma-separated keywords.' : 'Liste tecnologias como palavras-chave separadas por vírgulas.',
        ]"
    >
        <div class="form-row">
            <div class="field">
                <label>{{ $en ? 'Company' : 'Empresa' }}</label>
                <input name="company_name" value="{{ old('company_name', $experience->company_name) }}" placeholder="{{ $en ? 'Company name' : 'Nome da empresa' }}" required>
            </div>
            <div class="field">
                <label>{{ $en ? 'Role' : 'Cargo' }}</label>
                <input name="role_title" value="{{ old('role_title', $experience->role_title) }}" placeholder="{{ $en ? 'Backend Software Engineer' : 'Engenheiro de Software Backend' }}" required>
            </div>
        </div>
        <div class="form-row">
            <div class="field">
                <label>{{ $en ? 'Type' : 'Tipo' }}</label>
                <input name="employment_type" value="{{ old('employment_type', $experience->employment_type) }}" placeholder="CLT, PJ, contractor">
            </div>
            <div class="field">
                <label>{{ $en ? 'Location' : 'Local' }}</label>
                <input name="location" value="{{ old('location', $experience->location) }}" placeholder="{{ $en ? 'Remote · Brazil' : 'Remoto · Brasil' }}">
            </div>
        </div>
        <div class="form-row">
            <div class="field">
                <label>{{ $en ? 'Start' : 'Início' }}</label>
                <input name="start_date" type="date" value="{{ old('start_date', optional($experience->start_date)->format('Y-m-d')) }}">
            </div>
            <div class="field">
                <label>{{ $en ? 'End' : 'Fim' }}</label>
                <input name="end_date" type="date" value="{{ old('end_date', optional($experience->end_date)->format('Y-m-d')) }}">
            </div>
        </div>
        <label><input name="is_current" type="checkbox" value="1" @checked(old('is_current', $experience->is_current))> {{ $en ? 'Current role' : 'Cargo atual' }}</label>
        <div class="field">
            <label>{{ $en ? 'Description' : 'Descrição' }}</label>
            <textarea class="compact-textarea" name="description" placeholder="{{ $en ? 'Summarize product, team, scope and technical context...' : 'Resuma produto, time, escopo e contexto técnico...' }}">{{ old('description', $experience->description) }}</textarea>
        </div>
        <div class="field">
            <label>{{ $en ? 'Responsibilities' : 'Responsabilidades' }}</label>
            <textarea class="compact-textarea" name="responsibilities" placeholder="{{ $en ? 'One responsibility per line...' : 'Uma responsabilidade por linha...' }}">{{ old('responsibilities', implode("\n", $experience->responsibilities ?? [])) }}</textarea>
        </div>
        <div class="field">
            <label>{{ $en ? 'Technologies' : 'Tecnologias' }}</label>
            <input name="technologies" value="{{ old('technologies', implode(', ', $experience->technologies ?? [])) }}" placeholder="Laravel, MySQL, Redis, Docker">
        </div>
    </x-career.resource-edit-form>
@endsection
