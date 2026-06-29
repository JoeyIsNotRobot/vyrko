@extends('layouts.app')

@push('styles')
    <style>
        .career-page .page-title {
            max-width: 780px;
        }

        .career-hero {
            align-items: center;
            margin-bottom: 18px;
            padding: 16px 0 4px;
        }

        .career-hero h1 {
            font-size: clamp(28px, 3.4vw, 48px);
        }

        .career-hero p {
            margin: 10px 0 0;
        }

        .career-summary-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .career-summary-card {
            display: grid;
            gap: 10px;
            min-height: 128px;
            border: 1px solid rgba(51, 65, 85, .88);
            border-radius: 18px;
            padding: 16px;
            background: linear-gradient(180deg, rgba(30, 41, 59, .82), rgba(15, 23, 42, .74));
            cursor: pointer;
            transition: border-color .16s ease, background .16s ease;
            content-visibility: auto;
            contain-intrinsic-size: 128px;
        }

        .career-summary-card:hover,
        .career-summary-card:focus-visible {
            border-color: rgba(0, 122, 204, .72);
            outline: none;
        }

        .career-summary-card strong {
            display: block;
            margin-top: 5px;
            color: #fff;
            font-family: var(--font-mono);
            font-size: 28px;
            font-weight: 600;
            line-height: 1;
        }

        .career-summary-card p {
            margin: 0;
            font-size: 13px;
        }

        .career-summary-label {
            color: var(--color-muted);
            font-weight: 850;
        }

        .career-management-grid {
            display: grid;
            grid-template-columns: 230px minmax(0, 1fr) 330px;
            gap: 18px;
            align-items: start;
        }

        .career-sidebar,
        .career-quality-column {
            position: sticky;
            top: 92px;
        }

        .career-sidebar,
        .career-quality-card,
        .career-section-card {
            border: 1px solid rgba(51, 65, 85, .88);
            border-radius: 20px;
            background: linear-gradient(180deg, rgba(45, 55, 72, .88), rgba(37, 47, 63, .86));
            box-shadow: var(--shadow-panel);
        }

        .career-section-card,
        .career-quality-card {
            content-visibility: auto;
            contain-intrinsic-size: 420px;
        }

        .career-sidebar {
            padding: 10px;
        }

        .career-tab {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            width: 100%;
            border: 0;
            border-radius: 13px;
            padding: 11px 12px;
            color: var(--color-muted);
            background: transparent;
            font: inherit;
            font-size: 14px;
            font-weight: 800;
            text-align: left;
            cursor: pointer;
        }

        .career-tab:hover,
        .career-tab.is-active {
            color: #fff;
            background: rgba(0, 122, 204, .16);
        }

        .career-tab small {
            color: #8bd8ff;
            font-family: var(--font-mono);
        }

        .career-mobile-nav {
            display: none;
        }

        .career-main {
            min-width: 0;
        }

        .career-section {
            display: none;
        }

        .career-section.is-active {
            display: grid;
            gap: 14px;
        }

        .career-section-card {
            padding: clamp(16px, 2vw, 22px);
        }

        .career-section-header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .career-list {
            display: grid;
            gap: 12px;
        }

        .career-item {
            border: 1px solid rgba(51, 65, 85, .74);
            border-radius: 16px;
            padding: 14px;
            background: rgba(255, 255, 255, .035);
        }

        .career-item-title {
            color: #fff;
            font-weight: 850;
        }

        .career-item-meta {
            margin: 4px 0 0;
            color: var(--color-muted);
            font-size: 13px;
        }

        .career-item .btn.danger {
            min-height: 36px;
            padding: 8px 10px;
            color: #ffb7b7;
            background: rgba(244, 71, 71, .08);
            border-color: rgba(244, 71, 71, .28);
            box-shadow: none;
        }

        .career-create-panel {
            border: 1px solid rgba(0, 122, 204, .34);
            border-radius: 18px;
            background: rgba(0, 122, 204, .08);
            overflow: hidden;
        }

        .career-create-panel summary {
            cursor: pointer;
            padding: 14px 16px;
            color: #8bd8ff;
            font-weight: 850;
        }

        .career-create-panel[open] summary {
            border-bottom: 1px solid rgba(0, 122, 204, .24);
        }

        .career-create-body {
            padding: 16px;
        }

        .career-skill-group {
            display: grid;
            gap: 10px;
            border: 1px solid rgba(62, 62, 66, .72);
            border-radius: 18px;
            padding: 14px;
            background: rgba(15, 17, 23, .28);
        }

        .career-quality-card {
            padding: 18px;
        }

        .career-quality-column {
            display: grid;
            gap: 14px;
        }

        .career-check-list {
            display: grid;
            gap: 9px;
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .career-check-list li {
            display: flex;
            gap: 8px;
            align-items: center;
            color: var(--color-muted);
            font-size: 13px;
        }

        .career-check-dot {
            width: 9px;
            height: 9px;
            flex: 0 0 auto;
            border-radius: 999px;
            background: var(--color-success);
        }

        .career-check-dot.missing {
            background: var(--color-warning);
        }

        @media (max-width: 1220px) {
            .career-summary-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .career-management-grid {
                grid-template-columns: 220px minmax(0, 1fr);
            }

            .career-quality-column {
                grid-column: 1 / -1;
                position: static;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 860px) {
            .career-summary-grid,
            .career-quality-column,
            .career-management-grid {
                grid-template-columns: 1fr;
            }

            .career-sidebar {
                display: none;
            }

            .career-mobile-nav {
                display: block;
            }

            .career-section-header,
            .career-item > .actions {
                display: grid;
            }

            .career-hero {
                align-items: flex-start;
            }
        }

        .import-card {
            border: 1px solid rgba(51, 65, 85, .88);
            border-radius: 20px;
            background: linear-gradient(180deg, rgba(45, 55, 72, .88), rgba(37, 47, 63, .86));
            box-shadow: var(--shadow-panel);
            padding: 24px;
            margin-bottom: 20px;
        }

        .import-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            cursor: pointer;
            user-select: none;
        }

        .import-card-toggle {
            background: none;
            border: 1px solid rgba(0, 122, 204, .35);
            border-radius: 8px;
            color: #8bd8ff;
            font-size: 13px;
            font-weight: 700;
            padding: 6px 12px;
            cursor: pointer;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .import-card-toggle:hover {
            background: rgba(0, 122, 204, .12);
        }

        .inventory-tip {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            background: rgba(0, 100, 200, .08);
            border: 1px solid rgba(0, 122, 204, .28);
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 20px;
            font-size: 13px;
            color: var(--color-muted);
        }

        .inventory-tip strong {
            color: #8bd8ff;
        }

        .import-drop-zone {
            min-height: 120px;
            border: 1px dashed #334155;
            border-radius: 16px;
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            cursor: pointer;
            transition: border-color .16s ease, background .16s ease;
        }

        .import-drop-zone:hover,
        .import-drop-zone.drag-over {
            border-color: rgba(37, 99, 235, .5);
            background: rgba(37, 99, 235, .05);
        }

        .import-drop-zone.drag-active {
            border: 2px dashed #2563EB;
            background: rgba(37, 99, 235, .10);
        }

        .import-drop-zone.has-file {
            border: 1px solid #2563EB;
            background: rgba(37, 99, 235, .08);
        }

        .import-drop-zone.has-error {
            border: 1px solid #EF4444;
            background: rgba(239, 68, 68, .08);
        }

        .import-format-chips {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .import-format-chip {
            background: rgba(45, 55, 72, .8);
            border: 1px solid #475569;
            border-radius: 999px;
            padding: 2px 12px;
            font-size: 13px;
            color: var(--color-muted, #94A3B8);
        }

        .import-success-banner {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            background: rgba(16, 185, 129, .1);
            border: 1px solid rgba(16, 185, 129, .3);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .import-upload-icon {
            color: var(--color-muted, #94A3B8);
            flex-shrink: 0;
        }

        .import-badge {
            font-size: 11px;
            font-weight: 700;
            color: #93C5FD;
            background: rgba(59, 130, 246, .1);
            border: 1px solid rgba(59, 130, 246, .2);
            border-radius: 999px;
            padding: 2px 8px;
            text-transform: uppercase;
            letter-spacing: .06em;
            vertical-align: middle;
            margin-left: 6px;
        }
    </style>
@endpush

@section('content')
    @php($en = app()->getLocale() === 'en')

    <div class="career-page">
        <x-ui.page-header
            :eyebrow="__('messages.career.eyebrow')"
            :title="__('messages.career.title')"
            :subtitle="__('messages.career.subtitle')"
        >
            <x-slot:actions>
                <a class="btn secondary" href="{{ route('dashboard') }}">{{ $en ? 'Dashboard' : 'Dashboard' }}</a>
                <a class="btn" href="{{ route('career.profile.edit') }}">{{ __('messages.career.edit_profile') }}</a>
            </x-slot:actions>
        </x-ui.page-header>

        <div id="career-status" class="alert ok" hidden></div>
        <div id="career-errors" class="alert error" hidden></div>

        <div
            class="import-card"
            x-data="{
                open: false,
                file: null,
                dragOver: false,
                fileError: null,
                importSuccess: false,
                setFile(f) {
                    const allowed = ['pdf', 'docx', 'txt'];
                    const ext = f.name.split('.').pop().toLowerCase();
                    if (!allowed.includes(ext)) {
                        this.fileError = 'Formato não suportado. Use um arquivo PDF, DOCX ou TXT.';
                        this.file = null;
                        return;
                    }
                    this.fileError = null;
                    this.file = f;
                    const dt = new DataTransfer();
                    dt.items.add(f);
                    $refs.fileInput.files = dt.files;
                },
                handleDrop(e) {
                    e.preventDefault();
                    this.dragOver = false;
                    const f = e.dataTransfer?.files?.[0];
                    if (f) this.setFile(f);
                }
            }"
            @import:success.window="importSuccess = true; open = false; setTimeout(() => importSuccess = false, 8000)"
        >
            <div class="import-card-header" @click="open = !open">
                <div>
                    <p class="eyebrow" style="font-size:13px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--color-muted);">IMPORTAR CURRÍCULO</p>
                    <h2 style="font-size:clamp(18px,2vw,24px);font-weight:700;color:var(--color-text,#F1F5F9);margin:4px 0 0;">Importe seu currículo</h2>
                </div>
                <button type="button" class="import-card-toggle" @click.stop="open = !open" :aria-expanded="open">
                    <span x-text="open ? '▲ Recolher' : '▼ Expandir'"></span>
                </button>
            </div>

            <div x-show="importSuccess" x-transition style="margin-top:16px">
                <div class="import-success-banner" role="status" aria-live="polite">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="9 12 11 14 15 10"></polyline>
                    </svg>
                    <div>
                        <strong style="color:#10b981;display:block;margin-bottom:4px;">Importação concluída</strong>
                        <p style="margin:0;font-size:14px;color:var(--color-muted);">Seu inventário foi atualizado. Revise os campos abaixo.</p>
                    </div>
                </div>
            </div>

            <div x-show="open" x-transition style="margin-top:16px">
                <p style="font-size:15px;color:var(--color-muted);margin:0 0 16px;">Suba PDF, DOCX ou TXT e a IA preenche o inventário campo a campo.</p>

                <form data-import-form data-career-ajax method="POST" action="{{ route('career.import') }}" enctype="multipart/form-data">
                    @csrf
                    <label
                        class="import-drop-zone"
                        :class="{ 'drag-active': dragOver, 'has-file': file && !fileError, 'has-error': fileError }"
                        @click.self="$refs.fileInput.click()"
                        @dragover.prevent="dragOver = true"
                        @dragleave="dragOver = false"
                        @drop="handleDrop($event)"
                        role="button"
                        aria-label="Área de upload — arraste ou clique para selecionar"
                    >
                        <svg class="import-upload-icon" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>

                        <span x-show="!file && !fileError" style="font-size:16px;color:var(--color-muted);">Arraste seu arquivo aqui ou clique para selecionar</span>
                        <span x-show="dragOver" style="font-size:16px;color:#8bd8ff;">Solte o arquivo aqui</span>
                        <span
                            x-show="file && !fileError"
                            x-text="file ? file.name + ' · ' + (file.size > 1048576 ? (file.size/1048576).toFixed(1)+'MB' : Math.round(file.size/1024)+'KB') : ''"
                            style="font-size:15px;color:var(--color-text,#F1F5F9);"
                        ></span>
                        <span x-show="fileError" x-text="fileError" style="color:#EF4444;font-size:13px;" role="alert"></span>

                        <input
                            x-ref="fileInput"
                            type="file"
                            name="resume"
                            accept=".pdf,.docx,.txt"
                            class="sr-only"
                            aria-label="Selecionar arquivo de currículo"
                            @change="setFile($event.target.files[0])"
                            required
                        >
                    </label>

                    <div class="import-format-chips">
                        <span class="import-format-chip">PDF</span>
                        <span class="import-format-chip">DOCX</span>
                        <span class="import-format-chip">TXT</span>
                    </div>

                    <button type="submit" class="btn" style="margin-top:16px;min-height:44px;" :disabled="!file || !!fileError">Importar currículo</button>
                </form>
            </div>
        </div>

        <div class="inventory-tip">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#8bd8ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span><strong>Dica:</strong> O inventário deve ser mais completo do que qualquer currículo que você já teve. Quanto mais contexto sobre sua trajetória — projetos, conquistas, tecnologias, responsabilidades — maiores as chances de o Vyrko gerar um currículo preciso e relevante para cada vaga.</span>
        </div>

        <section id="career-items">
            @include('career.partials.inventory-list', ['user' => $user])
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            let activeSection = 'profile';

            const root = () => document.getElementById('career-items');

            const activate = (section) => {
                const shell = root();
                if (!shell) return;

                const targetPanel = shell.querySelector(`[data-career-panel="${section}"]`);
                activeSection = targetPanel ? section : 'profile';

                shell.querySelectorAll('[data-career-panel]').forEach((panel) => {
                    panel.classList.toggle('is-active', panel.dataset.careerPanel === activeSection);
                });

                shell.querySelectorAll('[data-career-tab]').forEach((tab) => {
                    tab.classList.toggle('is-active', tab.dataset.careerTab === activeSection);
                });

                const select = shell.querySelector('[data-career-select]');
                if (select) select.value = activeSection;
            };

            document.addEventListener('click', (event) => {
                const tab = event.target.closest('[data-career-tab]');
                if (tab) {
                    event.preventDefault();
                    activate(tab.dataset.careerTab);
                }

                const opener = event.target.closest('[data-open-create]');
                if (opener) {
                    const panel = document.getElementById(`career-create-${opener.dataset.openCreate}`);
                    if (panel) {
                        panel.open = true;
                        panel.scrollIntoView({behavior: 'smooth', block: 'nearest'});
                    }
                }
            });

            document.addEventListener('keydown', (event) => {
                if (!['Enter', ' '].includes(event.key)) return;
                const tab = event.target.closest('[data-career-tab]');
                if (!tab) return;

                event.preventDefault();
                activate(tab.dataset.careerTab);
            });

            document.addEventListener('change', (event) => {
                const select = event.target.closest('[data-career-select]');
                if (select) activate(select.value);
            });

            document.addEventListener('career:updated', () => activate(activeSection));
            document.addEventListener('DOMContentLoaded', () => activate(activeSection));
            activate(activeSection);
        })();
    </script>
@endpush
