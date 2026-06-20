@extends('layouts.app')

@section('content')
    @php
        $en = app()->getLocale() === 'en';
        $content = $resumeVersion->content;
    @endphp

    <x-ui.page-header
        :eyebrow="$en ? 'Resume templates' : 'Modelos de currículo'"
        :title="$en ? 'Choose the best format for this application' : 'Qual currículo enviar para esta vaga?'"
        :subtitle="$en ? 'Preview each template, then print or save as PDF using the browser dialog.' : 'Visualize cada modelo e depois imprima ou salve em PDF pelo diálogo do navegador.'"
    >
        <x-slot:actions>
            <a class="btn secondary" href="{{ route('resumes.show', $resumeVersion) }}">{{ $en ? 'Resume details' : 'Detalhes do currículo' }}</a>
            @if ($resumeVersion->jobPost)
                <a class="btn secondary" href="{{ route('jobs.show', $resumeVersion->jobPost) }}">{{ $en ? 'Back to job' : 'Voltar à vaga' }}</a>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <section class="summary-grid">
        <x-ui.metric-card :label="$en ? 'Template options' : 'Opções de modelo'" :value="count($templates)" :meta="$en ? 'ATS, tech and international formats' : 'Formatos ATS, tech e internacional'" />
        <x-ui.metric-card :label="$en ? 'Resume language' : 'Idioma do currículo'" :value="$resumeVersion->language" :meta="$en ? 'Defined during generation' : 'Definido na geração'" />
        <x-ui.metric-card :label="$en ? 'Resume type' : 'Tipo de currículo'" :value="$resumeVersion->resume_type" :meta="$en ? 'Controls content density' : 'Controla densidade do conteúdo'" />
        <x-ui.metric-card :label="$en ? 'Export mode' : 'Modo de exportação'" value="PDF" tone="success" :meta="$en ? 'Print or save from preview' : 'Imprima ou salve pela prévia'" />
    </section>

    <section class="grid grid-3">
        @foreach ($templates as $slug => $template)
            <article class="card template-card">
                <div class="template-mini">
                    <h3>{{ $template[$en ? 'name_en' : 'name_pt'] }}</h3>
                    <p>{{ $content['header']['name'] ?? $resumeVersion->title }}</p>
                    <div style="height:8px;background:#dbe3ef;border-radius:999px;margin:12px 0"></div>
                    <div style="height:8px;background:#cbd5e1;border-radius:999px;width:82%;margin-bottom:8px"></div>
                    <div style="height:8px;background:#cbd5e1;border-radius:999px;width:64%;margin-bottom:20px"></div>
                    <div style="height:72px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc"></div>
                </div>
                <div class="stack">
                    <div class="actions between">
                        <h2>{{ $template[$en ? 'name_en' : 'name_pt'] }}</h2>
                        <span class="badge">{{ $template[$en ? 'tag_en' : 'tag_pt'] }}</span>
                    </div>
                    <p>{{ $template[$en ? 'description_en' : 'description_pt'] }}</p>
                    <p>{{ $template[$en ? 'best_for_en' : 'best_for_pt'] }}</p>
                </div>
                <div class="actions">
                    <a class="btn" href="{{ route('resumes.preview', [$resumeVersion, $slug]) }}">{{ $en ? 'Preview' : 'Visualizar' }}</a>
                    <a class="btn secondary" href="{{ route('resumes.print', [$resumeVersion, $slug]) }}">{{ $en ? 'Download PDF' : 'Baixar PDF' }}</a>
                    <button class="btn secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('resumePlainText').value)">{{ $en ? 'Copy text' : 'Copiar texto' }}</button>
                </div>
            </article>
        @endforeach
    </section>
    <textarea id="resumePlainText" hidden>{{ $resumeVersion->plain_text }}</textarea>
@endsection
