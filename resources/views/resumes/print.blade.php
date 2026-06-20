<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $resumeVersion->title }} · PDF</title>
        <style>
            :root {
                --border: #334155;
            }

            body {
                margin: 0;
                color: #111827;
                background: #e5e7eb;
                font-family: "Geist Sans", Arial, Helvetica, sans-serif;
            }

            .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 40px;
                border-radius: 10px;
                padding: 9px 14px;
                border: 1px solid #007acc;
                background: #007acc;
                color: #fff;
                font-weight: 800;
                text-decoration: none;
                cursor: pointer;
            }

            .btn.secondary {
                border-color: #cbd5e1;
                background: #fff;
                color: #172033;
            }

            .actions {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }

            .print-toolbar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 14px;
                border: 1px solid #cbd5e1;
                border-radius: 16px;
                padding: 14px;
                margin-bottom: 18px;
                background: #fff;
            }

            .print-toolbar p {
                margin: 4px 0 0;
                color: #64748b;
            }
        </style>
        @include('resumes.partials.template-styles')
    </head>
    <body>
        @php($en = app()->getLocale() === 'en')
        <main class="resume-print-page">
            <div class="resume-print-shell">
                <div class="print-toolbar no-print">
                    <div>
                        <strong>{{ $templates[$template][$en ? 'name_en' : 'name_pt'] }}</strong>
                        <p>{{ $en ? 'PDF generated from the selected template. Choose “Save as PDF” in the print dialog.' : 'PDF gerado a partir do modelo selecionado. Escolha “Salvar como PDF” no diálogo de impressão.' }}</p>
                    </div>
                    <div class="actions">
                        <a class="btn secondary" href="{{ route('resumes.preview', [$resumeVersion, $template]) }}">{{ $en ? 'Back' : 'Voltar' }}</a>
                        <button class="btn" type="button" onclick="window.print()">{{ $en ? 'Download / Save PDF' : 'Baixar / Salvar PDF' }}</button>
                    </div>
                </div>

                @include('resumes.partials.template-document', compact('resumeVersion', 'template'))
            </div>
        </main>
    </body>
</html>
