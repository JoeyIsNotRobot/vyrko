@extends('layouts.app')

@section('content')
    @php($en = app()->getLocale() === 'en')

    <x-career.resource-edit-form
        :eyebrow="$en ? 'Achievement' : 'Conquista'"
        :title="$en ? 'Edit achievement' : 'Editar conquista'"
        :subtitle="$en ? 'Refine impact stories used as evidence in tailored resumes.' : 'Refine histórias de impacto usadas como evidência nos currículos personalizados.'"
        :action="route('achievements.update', $achievement)"
        :guidance-title="$en ? 'Make the impact visible' : 'Deixe o impacto visível'"
        :guidance-items="[
            $en ? 'Use measurable outcomes when possible.' : 'Use resultados mensuráveis quando possível.',
            $en ? 'Connect the achievement to technologies, business impact or delivery context.' : 'Conecte a conquista a tecnologias, impacto de negócio ou contexto de entrega.',
            $en ? 'Keep tags short and reusable for ATS matching.' : 'Mantenha tags curtas e reutilizáveis para match ATS.',
        ]"
    >
        <div class="field">
            <label>{{ $en ? 'Title' : 'Título' }}</label>
            <input name="title" value="{{ old('title', $achievement->title) }}" placeholder="{{ $en ? 'Reduced API latency by 35%' : 'Redução de latência de API em 35%' }}" required>
            <p class="form-help">{{ $en ? 'A short, evidence-first title.' : 'Um título curto e orientado a evidência.' }}</p>
        </div>
        <div class="field">
            <label>{{ $en ? 'Description' : 'Descrição' }}</label>
            <textarea class="compact-textarea" name="description" placeholder="{{ $en ? 'Describe context, action and outcome...' : 'Descreva contexto, ação e resultado...' }}" required>{{ old('description', $achievement->description) }}</textarea>
        </div>
        <div class="form-row">
            <div class="field">
                <label>{{ $en ? 'Metric' : 'Métrica' }}</label>
                <input name="impact_metric" value="{{ old('impact_metric', $achievement->impact_metric) }}" placeholder="{{ $en ? '35% faster response time' : '35% menos tempo de resposta' }}">
            </div>
            <div class="field">
                <label>Tags</label>
                <input name="evidence_tags" value="{{ old('evidence_tags', implode(', ', $achievement->evidence_tags ?? [])) }}" placeholder="Laravel, Redis, performance">
                <p class="form-help">{{ $en ? 'Separate tags with commas.' : 'Separe as tags com vírgulas.' }}</p>
            </div>
        </div>
    </x-career.resource-edit-form>
@endsection
