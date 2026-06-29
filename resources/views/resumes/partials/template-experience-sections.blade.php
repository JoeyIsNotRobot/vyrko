@if ($experiences !== [])
    <section class="resume-section">
        <h2>{{ $en ? 'Experience' : 'Experiências' }}</h2>
        @foreach ($experiences as $experience)
            <div class="resume-item">
                <h3>{{ $experience['role'] ?? '' }}</h3>
                <p class="resume-meta">{{ $experience['company'] ?? '' }} · {{ $experience['period'] ?? '' }}</p>
                @if (! empty($experience['bullets']))
                    <ul>
                        @foreach ($experience['bullets'] as $bullet)
                            <li>{{ is_array($bullet) ? ($bullet['text'] ?? '') : $bullet }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    </section>
@endif

@if ($projects !== [])
    <section class="resume-section">
        <h2>{{ $en ? 'Projects' : 'Projetos' }}</h2>
        @foreach ($projects as $project)
            <div class="resume-item">
                <h3>{{ $project['name'] ?? '' }}</h3>
                <p class="resume-meta">{{ $project['role'] ?? '' }}</p>
                <p>{{ $project['description'] ?? '' }}</p>
                @if (! empty($project['technologies']))
                    <p><strong>{{ $en ? 'Stack' : 'Stack' }}:</strong> {{ implode(', ', $project['technologies']) }}</p>
                @endif
            </div>
        @endforeach
    </section>
@endif

@if ($educations !== [])
    <section class="resume-section">
        <h2>{{ $en ? 'Education' : 'Formação' }}</h2>
        @foreach ($educations as $education)
            <p><strong>{{ $education['degree'] ?? '' }}</strong> · {{ $education['field'] ?? '' }} · {{ $education['institution'] ?? '' }}</p>
        @endforeach
    </section>
@endif
