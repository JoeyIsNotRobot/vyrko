@php
    use App\Support\UiText;

    $en = app()->getLocale() === 'en';
    $profile = $user->candidateProfile;
    $sections = [
        'experiences' => [
            'items' => $user->candidateExperiences,
            'label' => __('messages.sections.experiences'),
            'description' => $en ? 'Roles, responsibilities, dates and technical context.' : 'Cargos, responsabilidades, datas e contexto técnico.',
            'add' => __('messages.actions.add_experience'),
        ],
        'achievements' => [
            'items' => $user->candidateAchievements,
            'label' => __('messages.sections.achievements'),
            'description' => $en ? 'Impact stories, metrics and evidence tags.' : 'Histórias de impacto, métricas e tags de evidência.',
            'add' => __('messages.actions.add_achievement'),
        ],
        'skills' => [
            'items' => $user->candidateSkills,
            'label' => __('messages.sections.skills'),
            'description' => $en ? 'Grouped technical, product and behavioral skills.' : 'Habilidades técnicas, produto e comportamentais agrupadas.',
            'add' => __('messages.actions.add_skill'),
        ],
        'projects' => [
            'items' => $user->candidateProjects,
            'label' => __('messages.sections.projects'),
            'description' => $en ? 'Relevant projects, stack and highlights.' : 'Projetos relevantes, stack e destaques.',
            'add' => __('messages.actions.add_project'),
        ],
        'educations' => [
            'items' => $user->candidateEducations,
            'label' => __('messages.sections.educations'),
            'description' => $en ? 'Formal education and complementary programs.' : 'Formação acadêmica e programas complementares.',
            'add' => __('messages.actions.add_education'),
        ],
        'certifications' => [
            'items' => $user->candidateCertifications,
            'label' => __('messages.sections.certifications'),
            'description' => $en ? 'Credentials, issuers and proof links.' : 'Credenciais, emissores e links de comprovação.',
            'add' => __('messages.actions.add_certification'),
        ],
        'languages' => [
            'items' => $user->candidateLanguages,
            'label' => __('messages.sections.languages'),
            'description' => $en ? 'Languages and proficiency levels.' : 'Idiomas e níveis de proficiência.',
            'add' => __('messages.actions.add_language'),
        ],
    ];

    $profileDone = $profile && filled($profile->first_name) && filled($profile->last_name) && (filled($profile->headline) || filled($profile->target_role)) && filled($profile->summary);
    $qualityChecks = [
        ['label' => __('messages.career.profile'), 'done' => (bool) $profileDone],
        ['label' => __('messages.sections.experiences'), 'done' => $user->candidateExperiences->isNotEmpty()],
        ['label' => __('messages.sections.achievements'), 'done' => $user->candidateAchievements->isNotEmpty()],
        ['label' => __('messages.sections.skills'), 'done' => $user->candidateSkills->count() >= 3],
        ['label' => __('messages.sections.projects'), 'done' => $user->candidateProjects->isNotEmpty()],
        ['label' => __('messages.sections.educations'), 'done' => $user->candidateEducations->isNotEmpty()],
        ['label' => __('messages.sections.certifications'), 'done' => $user->candidateCertifications->isNotEmpty()],
        ['label' => __('messages.sections.languages'), 'done' => $user->candidateLanguages->isNotEmpty()],
    ];
    $filledChecks = collect($qualityChecks)->filter(fn (array $check): bool => $check['done'])->count();
    $totalChecks = count($qualityChecks);
    $completion = (int) round(($filledChecks / $totalChecks) * 100);
    $missingChecks = collect($qualityChecks)->reject(fn (array $check): bool => $check['done'])->values();
    $recommendations = $missingChecks->take(3)->map(fn (array $check): string => $en
        ? "Complete {$check['label']} to improve resume specificity."
        : "Preencha {$check['label']} para melhorar a especificidade dos currículos."
    );
@endphp

<div class="career-summary-grid">
    <x-career.summary-card :title="__('messages.career.profile')" :count="$profile ? '1/1' : '0/1'" :status="$profile ? 'filled' : 'empty'" :meta="$profile?->headline ?: $profile?->target_role" target="profile" />
    <x-career.summary-card :title="__('messages.sections.experiences')" :count="$user->candidateExperiences->count()" :status="$user->candidateExperiences->isNotEmpty() ? 'filled' : 'empty'" :meta="$en ? 'Professional history' : 'Histórico profissional'" target="experiences" />
    <x-career.summary-card :title="__('messages.sections.skills')" :count="$user->candidateSkills->count()" :status="$user->candidateSkills->isNotEmpty() ? 'filled' : 'empty'" :meta="$en ? 'Grouped by category' : 'Agrupadas por categoria'" target="skills" />
    <x-career.summary-card :title="__('messages.sections.projects')" :count="$user->candidateProjects->count()" :status="$user->candidateProjects->isNotEmpty() ? 'filled' : 'empty'" :meta="$en ? 'Proof of execution' : 'Provas de execução'" target="projects" />
    <x-career.summary-card :title="__('messages.sections.educations')" :count="$user->candidateEducations->count()" :status="$user->candidateEducations->isNotEmpty() ? 'filled' : 'empty'" :meta="$en ? 'Academic base' : 'Base acadêmica'" target="educations" />
    <x-career.summary-card :title="__('messages.sections.certifications')" :count="$user->candidateCertifications->count()" :status="$user->candidateCertifications->isNotEmpty() ? 'filled' : 'empty'" :meta="$en ? 'Credentials' : 'Credenciais'" target="certifications" />
    <x-career.summary-card :title="__('messages.sections.languages')" :count="$user->candidateLanguages->count()" :status="$user->candidateLanguages->isNotEmpty() ? 'filled' : 'empty'" :meta="$en ? 'Communication range' : 'Alcance de comunicação'" target="languages" />
</div>

<div class="career-management-grid">
    <aside class="career-sidebar" aria-label="{{ $en ? 'Inventory sections' : 'Seções do inventário' }}">
        <button class="career-tab is-active" type="button" data-career-tab="profile">{{ __('messages.career.profile') }} <small>{{ $profile ? '1' : '0' }}</small></button>
        @foreach ($sections as $section => $config)
            <button class="career-tab" type="button" data-career-tab="{{ $section }}">{{ $config['label'] }} <small>{{ $config['items']->count() }}</small></button>
        @endforeach
    </aside>

    <div class="career-mobile-nav">
        <label>{{ $en ? 'Inventory section' : 'Seção do inventário' }}</label>
        <select data-career-select>
            <option value="profile">{{ __('messages.career.profile') }}</option>
            @foreach ($sections as $section => $config)
                <option value="{{ $section }}">{{ $config['label'] }}</option>
            @endforeach
        </select>
    </div>

    <main class="career-main">
        <section class="career-section is-active" data-career-panel="profile">
            <article class="career-section-card">
                <div class="career-section-header">
                    <div>
                        <p class="eyebrow">{{ __('messages.career.profile') }}</p>
                        <h2>{{ $en ? 'Candidate identity and positioning' : 'Identidade e posicionamento do candidato' }}</h2>
                        <p>{{ $en ? 'This feeds the resume header, summary and target role.' : 'Esses dados alimentam o cabeçalho, resumo e cargo alvo do currículo.' }}</p>
                    </div>
                    <a class="btn" href="{{ route('career.profile.edit') }}">{{ __('messages.career.edit_profile') }}</a>
                </div>

                @if ($profile)
                    <div class="grid grid-2">
                        <div class="career-item">
                            <p class="career-item-meta">{{ $en ? 'Name' : 'Nome' }}</p>
                            <div class="career-item-title">{{ $profile->first_name }} {{ $profile->last_name }}</div>
                        </div>
                        <div class="career-item">
                            <p class="career-item-meta">Headline</p>
                            <div class="career-item-title">{{ $profile->headline ?: $profile->target_role ?: '—' }}</div>
                        </div>
                        <div class="career-item">
                            <p class="career-item-meta">{{ $en ? 'Location' : 'Localização' }}</p>
                            <div class="career-item-title">{{ collect([$profile->location_city, $profile->location_state, $profile->location_country])->filter()->implode(', ') ?: '—' }}</div>
                        </div>
                        <div class="career-item">
                            <p class="career-item-meta">{{ $en ? 'Contact' : 'Contato' }}</p>
                            <div class="career-item-title">{{ $profile->email ?: $user->email }}</div>
                        </div>
                    </div>
                    @if ($profile->summary)
                        <div class="career-item" style="margin-top:12px">
                            <p class="career-item-meta">{{ $en ? 'Base summary' : 'Resumo base' }}</p>
                            <p>{{ $profile->summary }}</p>
                        </div>
                    @endif
                @else
                    <x-ui.empty-state
                        :title="$en ? 'Profile not completed yet' : 'Perfil ainda não preenchido'"
                        :description="$en ? 'Start with your identity, headline and base summary so generated resumes have a strong foundation.' : 'Comece por identidade, headline e resumo base para que os currículos gerados tenham uma fundação forte.'"
                        :cta-href="route('career.profile.edit')"
                        :cta-label="__('messages.career.edit_profile')"
                        :example="$en ? 'Example: Backend Software Engineer · Laravel · SaaS.' : 'Exemplo: Engenheiro Backend · Laravel · SaaS.'"
                    />
                @endif
            </article>
        </section>

        @foreach ($sections as $section => $config)
            <section class="career-section" data-career-panel="{{ $section }}">
                <article class="career-section-card">
                    <div class="career-section-header">
                        <div>
                            <p class="eyebrow">{{ $config['items']->count() }} {{ $en ? 'items' : 'itens' }}</p>
                            <h2>{{ $config['label'] }}</h2>
                            <p>{{ $config['description'] }}</p>
                        </div>
                        <button class="btn" type="button" data-open-create="{{ $section }}">{{ $config['add'] }}</button>
                    </div>

                    <div class="career-list">
                        @if ($section === 'skills' && $config['items']->isNotEmpty())
                            @foreach ($config['items']->groupBy('category') as $category => $skills)
                                <div class="career-skill-group">
                                    <div class="actions" style="justify-content:space-between">
                                        <h3>{{ UiText::label('skill_categories', $category) }}</h3>
                                        <span class="badge">{{ $skills->count() }}</span>
                                    </div>
                                    @foreach ($skills as $item)
                                        @include('career.partials.inventory-item', ['section' => $section, 'item' => $item, 'user' => $user])
                                    @endforeach
                                </div>
                            @endforeach
                        @else
                            @forelse ($config['items'] as $item)
                                @include('career.partials.inventory-item', ['section' => $section, 'item' => $item, 'user' => $user])
                            @empty
                                <div class="empty-state">
                                    <div>
                                        <h3>{{ $en ? "No {$config['label']} yet" : "Nenhum item em {$config['label']} ainda" }}</h3>
                                        <p>{{ $config['description'] }}</p>
                                        <p class="empty-example">{{ $en ? 'Add the first item to improve evidence quality.' : 'Adicione o primeiro item para melhorar a qualidade das evidências.' }}</p>
                                    </div>
                                    <button class="btn" type="button" data-open-create="{{ $section }}">{{ $config['add'] }}</button>
                                </div>
                            @endforelse
                        @endif
                    </div>
                </article>

                <details class="career-create-panel" id="career-create-{{ $section }}">
                    <summary>{{ $config['add'] }}</summary>
                    <div class="career-create-body">
                        @include('career.partials.forms.create', ['section' => $section, 'user' => $user])
                    </div>
                </details>
            </section>
        @endforeach
    </main>

    <aside class="career-quality-column">
        <article class="career-quality-card stack">
            <div>
                <p class="eyebrow">{{ $en ? 'Inventory quality' : 'Qualidade do Inventário' }}</p>
                <h2>{{ $completion }}%</h2>
                <p>{{ $en ? "{$filledChecks} of {$totalChecks} core blocks completed." : "{$filledChecks} de {$totalChecks} blocos principais preenchidos." }}</p>
            </div>
            <div class="progress"><span style="width: {{ $completion }}%"></span></div>

            <div>
                <h3>{{ $en ? 'Completed' : 'Preenchidos' }}</h3>
                <ul class="career-check-list">
                    @foreach (collect($qualityChecks)->filter(fn (array $check): bool => $check['done']) as $check)
                        <li><span class="career-check-dot"></span>{{ $check['label'] }}</li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h3>{{ $en ? 'Missing data' : 'Dados faltantes' }}</h3>
                <ul class="career-check-list">
                    @forelse ($missingChecks as $check)
                        <li><span class="career-check-dot missing"></span>{{ $check['label'] }}</li>
                    @empty
                        <li><span class="career-check-dot"></span>{{ $en ? 'No critical gaps.' : 'Nenhum gap crítico.' }}</li>
                    @endforelse
                </ul>
            </div>

            <div>
                <h3>{{ $en ? 'Quick recommendations' : 'Recomendações rápidas' }}</h3>
                <ul>
                    @forelse ($recommendations as $recommendation)
                        <li>{{ $recommendation }}</li>
                    @empty
                        <li>{{ $en ? 'Keep achievements updated with measurable impact.' : 'Mantenha conquistas atualizadas com impacto mensurável.' }}</li>
                    @endforelse
                </ul>
            </div>
        </article>

    </aside>
</div>
