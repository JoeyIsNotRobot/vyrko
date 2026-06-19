@php
    $content = $resumeVersion->content ?? [];
    $header = $content['header'] ?? [];
    $skills = $content['skills'] ?? [];
    $experiences = $content['experiences'] ?? [];
    $projects = $content['projects'] ?? [];
    $educations = $content['education'] ?? [];
    $certifications = $content['certifications'] ?? [];
    $languages = $content['languages'] ?? [];
    $summary = $content['summary'] ?? '';
    $template = $template ?? 'ats-classic';
    $en = app()->getLocale() === 'en';
@endphp

<article class="resume-document {{ $template }}">
    <header class="resume-header">
        <h1>{{ $header['name'] ?? $resumeVersion->user?->name ?? 'Resume' }}</h1>
        @if (! empty($header['headline']))
            <p class="resume-headline">{{ $header['headline'] }}</p>
        @endif
        <div class="resume-contact">
            @foreach (array_filter([$header['location'] ?? null, $header['email'] ?? null, $header['phone'] ?? null]) as $contact)
                <span>{{ $contact }}</span>
            @endforeach
            @foreach (($header['links'] ?? []) as $link)
                <span>{{ $link }}</span>
            @endforeach
        </div>
    </header>

    @if ($template === 'tech-compact')
        <div class="resume-tech-layout">
            <aside class="resume-sidebar">
                @if ($skills !== [])
                    <section class="resume-section">
                        <h2>{{ __('messages.sections.skills') }}</h2>
                        <div class="resume-skills">
                            @foreach ($skills as $group)
                                <div class="resume-skill-row">
                                    <strong>{{ $group['category'] ?? ($en ? 'Skills' : 'Habilidades') }}</strong>
                                    <p>{{ implode(', ', $group['items'] ?? []) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($languages !== [])
                    <section class="resume-section">
                        <h2>{{ __('messages.sections.languages') }}</h2>
                        @foreach ($languages as $language)
                            <p><strong>{{ $language['language'] ?? '' }}</strong><br>{{ $language['proficiency'] ?? '' }}</p>
                        @endforeach
                    </section>
                @endif

                @if ($certifications !== [])
                    <section class="resume-section">
                        <h2>{{ __('messages.sections.certifications') }}</h2>
                        @foreach ($certifications as $certification)
                            <p><strong>{{ $certification['name'] ?? '' }}</strong><br>{{ $certification['issuer'] ?? '' }}</p>
                        @endforeach
                    </section>
                @endif
            </aside>

            <main>
                @if ($summary)
                    <section class="resume-section">
                        <h2>{{ $en ? 'Professional Summary' : 'Resumo profissional' }}</h2>
                        <p>{{ $summary }}</p>
                    </section>
                @endif

                @include('resumes.partials.template-experience-sections', compact('experiences', 'projects', 'educations', 'en'))
            </main>
        </div>
    @else
        @if ($summary)
            <section class="resume-section">
                <h2>{{ $en ? 'Professional Summary' : 'Resumo profissional' }}</h2>
                <p>{{ $summary }}</p>
            </section>
        @endif

        @if ($skills !== [])
            <section class="resume-section">
                <h2>{{ __('messages.sections.skills') }}</h2>
                @if ($template === 'international-clean')
                    <div class="resume-pill-list">
                        @foreach ($skills as $group)
                            @foreach (($group['items'] ?? []) as $skill)
                                <span class="resume-pill">{{ $skill }}</span>
                            @endforeach
                        @endforeach
                    </div>
                @else
                    <div class="resume-skills">
                        @foreach ($skills as $group)
                            <p class="resume-skill-row"><strong>{{ $group['category'] ?? ($en ? 'Skills' : 'Habilidades') }}:</strong> {{ implode(', ', $group['items'] ?? []) }}</p>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif

        @include('resumes.partials.template-experience-sections', compact('experiences', 'projects', 'educations', 'en'))

        @if ($certifications !== [] || $languages !== [])
            <section class="resume-section">
                <h2>{{ $en ? 'Additional Information' : 'Informações adicionais' }}</h2>
                <div class="resume-grid">
                    @if ($certifications !== [])
                        <div>
                            <h3>{{ __('messages.sections.certifications') }}</h3>
                            @foreach ($certifications as $certification)
                                <p>{{ $certification['name'] ?? '' }} · {{ $certification['issuer'] ?? '' }}</p>
                            @endforeach
                        </div>
                    @endif
                    @if ($languages !== [])
                        <div>
                            <h3>{{ __('messages.sections.languages') }}</h3>
                            @foreach ($languages as $language)
                                <p>{{ $language['language'] ?? '' }} · {{ $language['proficiency'] ?? '' }}</p>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        @endif
    @endif
</article>
