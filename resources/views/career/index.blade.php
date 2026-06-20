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
            border: 1px solid rgba(62, 62, 66, .86);
            border-radius: 18px;
            padding: 16px;
            background: linear-gradient(180deg, rgba(45, 45, 48, .82), rgba(37, 37, 38, .74));
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
            border: 1px solid rgba(62, 62, 66, .88);
            border-radius: 20px;
            background: linear-gradient(180deg, rgba(45, 45, 48, .86), rgba(37, 37, 38, .82));
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
            border: 1px solid rgba(62, 62, 66, .78);
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
