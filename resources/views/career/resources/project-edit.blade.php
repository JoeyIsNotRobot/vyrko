@extends('layouts.app')

@section('content')
    @php($en = app()->getLocale() === 'en')

    <x-career.resource-edit-form
        :eyebrow="$en ? 'Project' : 'Projeto'"
        :title="$en ? 'Edit project' : 'Editar projeto'"
        :subtitle="$en ? 'Use projects as proof of execution, stack fluency and product thinking.' : 'Use projetos como prova de execução, domínio de stack e pensamento de produto.'"
        :action="route('projects.update', $project)"
    >
        <div class="form-row">
            <div class="field">
                <label>{{ $en ? 'Name' : 'Nome' }}</label>
                <input name="name" value="{{ old('name', $project->name) }}" placeholder="{{ $en ? 'Vyrko MVP' : 'MVP Vyrko' }}" required>
            </div>
            <div class="field">
                <label>{{ $en ? 'Role' : 'Papel' }}</label>
                <input name="role" value="{{ old('role', $project->role) }}" placeholder="{{ $en ? 'Fullstack Developer' : 'Desenvolvedor Fullstack' }}">
            </div>
        </div>
        <div class="field">
            <label>{{ $en ? 'Description' : 'Descrição' }}</label>
            <textarea class="compact-textarea" name="description" placeholder="{{ $en ? 'What the project does and why it matters...' : 'O que o projeto faz e por que importa...' }}">{{ old('description', $project->description) }}</textarea>
        </div>
        <div class="field">
            <label>{{ $en ? 'Technologies' : 'Tecnologias' }}</label>
            <input name="technologies" value="{{ old('technologies', implode(', ', $project->technologies ?? [])) }}" placeholder="Laravel, Sail, MySQL, Tailwind">
        </div>
        <div class="form-row">
            <div class="field">
                <label>URL</label>
                <input name="url" value="{{ old('url', $project->url) }}" placeholder="https://...">
            </div>
            <div class="field">
                <label>{{ $en ? 'Repository' : 'Repositório' }}</label>
                <input name="repository_url" value="{{ old('repository_url', $project->repository_url) }}" placeholder="https://github.com/...">
            </div>
        </div>
        <div class="field">
            <label>{{ $en ? 'Highlights' : 'Destaques' }}</label>
            <textarea class="compact-textarea" name="highlights" placeholder="{{ $en ? 'One highlight per line...' : 'Um destaque por linha...' }}">{{ old('highlights', implode("\n", $project->highlights ?? [])) }}</textarea>
        </div>
    </x-career.resource-edit-form>
@endsection
