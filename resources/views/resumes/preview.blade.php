@extends('layouts.app')

@push('styles')
    @include('resumes.partials.template-styles')
@endpush

@section('content')
    @php($en = app()->getLocale() === 'en')
    @php($selectedTemplate = $templates[$template])
    @php($report = $resumeVersion->jobMatchReport)
    @php($gaps = $report?->gaps ?? [])
    @php($criticalGaps = $gaps['critical'] ?? [])

    <x-ui.page-header
        :eyebrow="$en ? 'Preview' : 'Prévia'"
        :title="$selectedTemplate[$en ? 'name_en' : 'name_pt']"
        :subtitle="$selectedTemplate[$en ? 'description_en' : 'description_pt']"
    />

    <div class="resume-actions-bar no-print">
        <div>
            <strong>{{ $resumeVersion->title }}</strong>
            <p class="muted" style="margin:4px 0 0">{{ $en ? 'PDF generated from the selected template via browser print.' : 'PDF gerado a partir do modelo selecionado pela impressão do navegador.' }}</p>
        </div>
        <div class="actions">
            <select aria-label="{{ $en ? 'Template selector' : 'Seletor de modelo' }}" onchange="if (this.value) window.location.href = this.value">
                @foreach ($templates as $slug => $templateOption)
                    <option value="{{ route('resumes.preview', [$resumeVersion, $slug]) }}" @selected($slug === $template)>{{ $templateOption[$en ? 'name_en' : 'name_pt'] }}</option>
                @endforeach
            </select>
            <a class="btn secondary" href="{{ route('resumes.templates', $resumeVersion) }}">{{ $en ? 'All templates' : 'Todos os modelos' }}</a>
        </div>
    </div>

    <section class="resume-preview-layout">
        <div class="resume-preview-frame">
            @include('resumes.partials.template-document', compact('resumeVersion', 'template'))
        </div>

        <aside class="resume-side-panel no-print">
            <article class="card">
                <p class="muted">{{ $en ? 'Overall score' : 'Score geral' }}</p>
                @php($overallScore = max(0, min(100, (int) ($report?->overall_score ?? 0))))
                <div class="stat">{{ $overallScore }}<small>/100</small></div>
                <div class="progress"><span style="width: {{ $overallScore }}%"></span></div>
            </article>

            <article class="card stack">
                <div>
                    <p class="eyebrow">ATS</p>
                    <h2>{{ __('messages.resume.ats_checklist') }}</h2>
                </div>
                @if ($resumeVersion->ats_checklist)
                    <p><span class="badge">Score {{ $resumeVersion->ats_checklist['score'] ?? '—' }}</span></p>
                    <ul class="split-list">
                        @foreach ($resumeVersion->ats_checklist['items'] ?? [] as $item)
                            <li>{{ \App\Support\UiText::label('ats', $item['key'] ?? null) }}: {{ \App\Support\UiText::label('ats', $item['status'] ?? null) }}</li>
                        @endforeach
                    </ul>
                @else
                    <x-ui.empty-state
                        :title="$en ? 'Checklist not run yet' : 'Checklist ainda não executado'"
                        :description="$en ? 'Return to resume details and update the ATS checklist before exporting.' : 'Volte aos detalhes do currículo e atualize o checklist ATS antes de exportar.'"
                    />
                @endif
            </article>

            <article class="card stack">
                <div>
                    <p class="eyebrow">{{ $en ? 'Risks' : 'Riscos' }}</p>
                    <h2>{{ $en ? 'Critical gaps' : 'Gaps críticos' }}</h2>
                </div>
                <ul class="split-list">
                    @forelse ($criticalGaps as $gap)
                        <li>{{ $gap }}</li>
                    @empty
                        <li>{{ $en ? 'No critical gaps found in the latest report.' : 'Nenhum gap crítico encontrado no relatório mais recente.' }}</li>
                    @endforelse
                </ul>
            </article>

            <article class="card stack">
                <h2>{{ $en ? 'Actions' : 'Ações' }}</h2>
                <a class="btn" href="{{ route('resumes.print', [$resumeVersion, $template]) }}">{{ $en ? 'Download PDF' : 'Baixar PDF' }}</a>
                <button class="btn secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('previewPlainText').value)">{{ $en ? 'Copy text' : 'Copiar texto' }}</button>
                <a class="btn secondary" href="{{ route('career.index') }}">{{ $en ? 'Edit inventory' : 'Editar inventário' }}</a>
                <textarea id="previewPlainText" hidden>{{ $resumeVersion->plain_text }}</textarea>
            </article>
        </aside>
    </section>
@endsection
