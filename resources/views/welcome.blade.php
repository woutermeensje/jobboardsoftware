@extends('layouts.app')

@section('title', 'JobBoardSoftware | SaaS vacaturebank software')
@section('meta_description', 'Een eerste SaaS job board voorkant met zoekfunctie, filters, vacaturekaarten, header en footer gebaseerd op het Studentinhuren platform.')

@php
  $jobs = collect([
    [
      'id' => 1,
      'title' => 'Laravel Developer',
      'company' => 'BrightApps',
      'location' => 'Amsterdam',
      'category' => 'Development',
      'types' => ['Fulltime', 'Remote'],
      'salary' => 'EUR 4.200 - 5.800',
      'posted' => 'Vandaag',
      'description' => 'Bouw mee aan een multi-tenant SaaS platform met dashboards, vacaturefeeds en employer self-service.',
      'highlights' => ['Laravel', 'Vue', 'SaaS architectuur'],
    ],
    [
      'id' => 2,
      'title' => 'Recruitment Marketeer',
      'company' => 'TalentFlow',
      'location' => 'Rotterdam',
      'category' => 'Marketing',
      'types' => ['Parttime', 'Hybrid'],
      'salary' => 'EUR 3.100 - 4.200',
      'posted' => 'Gisteren',
      'description' => 'Optimaliseer vacaturecampagnes, candidate journeys en branded job pages voor niche werkgevers.',
      'highlights' => ['Vacature SEO', 'Campagnes', 'Analytics'],
    ],
    [
      'id' => 3,
      'title' => 'Customer Success Manager',
      'company' => 'HirePilot',
      'location' => 'Utrecht',
      'category' => 'Customer Success',
      'types' => ['Fulltime', 'Hybrid'],
      'salary' => 'EUR 3.600 - 4.900',
      'posted' => '2 dagen geleden',
      'description' => 'Help werkgevers live te gaan met hun job board, onboard teams en vertaal feedback naar productverbetering.',
      'highlights' => ['Onboarding', 'B2B SaaS', 'Retentie'],
    ],
    [
      'id' => 4,
      'title' => 'Sales Development Representative',
      'company' => 'BoardWorks',
      'location' => 'Den Haag',
      'category' => 'Sales',
      'types' => ['Fulltime'],
      'salary' => 'EUR 2.900 - 3.800',
      'posted' => '3 dagen geleden',
      'description' => 'Benader recruitment agencies, brancheorganisaties en communities die hun eigen vacaturebank willen lanceren.',
      'highlights' => ['Prospecting', 'CRM', 'Demo afspraken'],
    ],
    [
      'id' => 5,
      'title' => 'Operations Specialist',
      'company' => 'WorkGrid',
      'location' => 'Eindhoven',
      'category' => 'Operations',
      'types' => ['Parttime'],
      'salary' => 'EUR 2.800 - 3.600',
      'posted' => 'Deze week',
      'description' => 'Beheer vacaturekwaliteit, klantprocessen, imports en supportflows binnen een groeiend platformteam.',
      'highlights' => ['Proces', 'Support', 'Datakwaliteit'],
    ],
    [
      'id' => 6,
      'title' => 'Freelance UX Designer',
      'company' => 'NicheJobs',
      'location' => 'Remote',
      'category' => 'Design',
      'types' => ['Freelance', 'Remote'],
      'salary' => 'EUR 75 - 95 per uur',
      'posted' => 'Deze week',
      'description' => 'Ontwerp employer portals, candidate flows en conversiegerichte vacaturedetailpagina\'s.',
      'highlights' => ['Figma', 'UX research', 'Design systems'],
    ],
  ]);

  $categories = $jobs->pluck('category')->unique()->sort()->values();
  $types = $jobs->flatMap(fn ($job) => $job['types'])->unique()->sort()->values();

  $toArray = fn ($value) => array_values(array_filter(is_array($value) ? $value : (array) $value, fn ($item) => trim((string) $item) !== ''));

  $search = trim((string) request('search', ''));
  $location = trim((string) request('location', ''));
  $selectedCategories = $toArray(request('category', []));
  $selectedTypes = $toArray(request('type', []));

  $filteredJobs = $jobs->filter(function ($job) use ($search, $location, $selectedCategories, $selectedTypes) {
    $searchNeedle = strtolower($search);
    $locationNeedle = strtolower($location);
    $searchHaystack = strtolower($job['title'].' '.$job['company'].' '.$job['category'].' '.$job['description'].' '.implode(' ', $job['highlights']));
    $locationHaystack = strtolower($job['location']);

    if ($searchNeedle !== '' && !str_contains($searchHaystack, $searchNeedle)) {
      return false;
    }

    if ($locationNeedle !== '' && !str_contains($locationHaystack, $locationNeedle)) {
      return false;
    }

    if ($selectedCategories !== [] && !in_array($job['category'], $selectedCategories, true)) {
      return false;
    }

    if ($selectedTypes !== [] && count(array_intersect($job['types'], $selectedTypes)) === 0) {
      return false;
    }

    return true;
  })->values();

  $categoryCounts = $categories->mapWithKeys(fn ($category) => [
    $category => $jobs->where('category', $category)->count(),
  ]);

  $typeCounts = $types->mapWithKeys(fn ($type) => [
    $type => $jobs->filter(fn ($job) => in_array($type, $job['types'], true))->count(),
  ]);

  $hasActiveFilters = $search !== '' || $location !== '' || $selectedCategories !== [] || $selectedTypes !== [];
  $currentQuery = request()->query();
  $baseUrl = route('welcome');
  $makeUrl = fn ($query) => $baseUrl.(count($query) ? '?'.http_build_query($query) : '');
  $removeSingleUrl = function ($key, $value = null) use ($currentQuery, $makeUrl) {
    $next = $currentQuery;

    if ($value === null) {
      unset($next[$key]);
      return $makeUrl($next);
    }

    $values = array_values(array_filter((array) ($next[$key] ?? []), fn ($item) => (string) $item !== (string) $value));

    if ($values === []) {
      unset($next[$key]);
    } else {
      $next[$key] = $values;
    }

    return $makeUrl($next);
  };
@endphp

@section('content')
<section class="public-page jobs-index-page" id="vacatures">
  <div class="public-page__inner jobs-page-shell">
    <section id="filters" class="jobs-filter-wrap" aria-label="Filters">
      <form class="jobs-filter jobs-search-form" method="get" action="{{ route('welcome') }}" data-auto-submit-form>
        <div class="filter-header">
          <div class="filter-header__row">
            <h2>Doorzoek alle vacatures</h2>
            @if($hasActiveFilters)
              <a class="jobs-reset-link" href="{{ route('welcome') }}">Wis alle filters</a>
            @endif
          </div>
          <p>Zoek op functie, werkgever, sector of locatie. De lijst werkt nu al zonder databasekoppeling.</p>
        </div>

        <div class="filter-row">
          <div class="search-basic">
            <div class="search_keywords">
              <input
                id="search"
                name="search"
                type="text"
                placeholder="Functienaam, sector of onderwerp..."
                value="{{ $search }}"
                data-auto-submit="input"
              >
            </div>

            <div class="search_location">
              <input
                id="location"
                name="location"
                type="text"
                placeholder="Stad, regio of remote"
                value="{{ $location }}"
                data-auto-submit="input"
              >
            </div>
          </div>

          @foreach($selectedCategories as $category)
            <input type="hidden" name="category[]" value="{{ $category }}">
          @endforeach

          @foreach($selectedTypes as $type)
            <input type="hidden" name="type[]" value="{{ $type }}">
          @endforeach
        </div>

        @if($hasActiveFilters)
          <div class="active-filters" aria-live="polite">
            @if($search !== '')
              <a class="active-filter" href="{{ $removeSingleUrl('search') }}">
                <span>Zoeken: {{ $search }}</span>
                <span aria-hidden="true">&times;</span>
              </a>
            @endif
            @if($location !== '')
              <a class="active-filter" href="{{ $removeSingleUrl('location') }}">
                <span>Locatie: {{ $location }}</span>
                <span aria-hidden="true">&times;</span>
              </a>
            @endif
            @foreach($selectedCategories as $category)
              <a class="active-filter" href="{{ $removeSingleUrl('category', $category) }}">
                <span>{{ $category }}</span>
                <span aria-hidden="true">&times;</span>
              </a>
            @endforeach
            @foreach($selectedTypes as $type)
              <a class="active-filter active-filter--alt" href="{{ $removeSingleUrl('type', $type) }}">
                <span>{{ $type }}</span>
                <span aria-hidden="true">&times;</span>
              </a>
            @endforeach
          </div>
        @endif

        <noscript>
          <div class="jobs-filter__fallback">
            <button class="btn btn-primary" type="submit">Filters toepassen</button>
            <a class="btn btn-ghost" href="{{ route('welcome') }}">Reset</a>
          </div>
        </noscript>
      </form>
    </section>

    <div class="jobs-section-divider"></div>

    <div class="jobs-results-head">
      <p class="jobs-results-count">
        {{ $filteredJobs->count() }} {{ $filteredJobs->count() === 1 ? 'vacature' : 'vacatures' }} gevonden
      </p>
      <p class="jobs-results-mode">Demo data - klaar om later op database vacatures aan te sluiten</p>
    </div>

    <div class="jobs-content-layout">
      <aside class="jobs-sidebar">
        <form method="GET" action="{{ route('welcome') }}" class="sidebar-filter-form">
          @if($search !== '')
            <input type="hidden" name="search" value="{{ $search }}">
          @endif
          @if($location !== '')
            <input type="hidden" name="location" value="{{ $location }}">
          @endif

          <div class="sidebar-filter-group">
            <h3 class="sidebar-filter-group__title">Categorie</h3>
            <div class="sidebar-filter-group__body">
              @foreach($categories as $category)
                <label class="sidebar-filter-option">
                  <input
                    type="checkbox"
                    name="category[]"
                    value="{{ $category }}"
                    @checked(in_array($category, $selectedCategories, true))
                    data-auto-submit-checkbox
                  >
                  <span class="sidebar-filter-option__label">{{ $category }}</span>
                  <span class="sidebar-filter-count">{{ $categoryCounts[$category] }}</span>
                </label>
              @endforeach
            </div>
          </div>

          <div class="sidebar-filter-group">
            <h3 class="sidebar-filter-group__title">Type vacature</h3>
            <div class="sidebar-filter-group__body">
              @foreach($types as $type)
                <label class="sidebar-filter-option">
                  <input
                    type="checkbox"
                    name="type[]"
                    value="{{ $type }}"
                    @checked(in_array($type, $selectedTypes, true))
                    data-auto-submit-checkbox
                  >
                  <span class="sidebar-filter-option__label">{{ $type }}</span>
                  <span class="sidebar-filter-count">{{ $typeCounts[$type] }}</span>
                </label>
              @endforeach
            </div>
          </div>

          @if($hasActiveFilters)
            <a href="{{ route('welcome') }}" class="sidebar-filter-reset">
              <i class="ph ph-x"></i> Filters wissen
            </a>
          @endif
        </form>
      </aside>

      <section class="jobs-results-section" aria-label="Vacature resultaten">
        <div class="jobs-results">
          @forelse($filteredJobs as $job)
            @if($loop->iteration === 4)
              <article class="card job-promo-card">
                <div class="job-promo-card__icon"><i class="ph ph-rocket-launch"></i></div>
                <div>
                  <h2>Publiceer vacatures met een eigen portal</h2>
                  <p>Werkgevers krijgen straks self-service plaatsingen, beheer van reacties en branded bedrijfspagina's.</p>
                </div>
                <a href="{{ route('pages.contact') }}" class="btn btn-primary">Demo aanvragen</a>
              </article>
            @endif

            <details class="card job-card">
              <summary class="job-card__summary">
                <div class="job-card__main">
                  <h2 class="job-card__title">{{ $job['title'] }}</h2>
                  <div class="job-card__meta">
                    <span><i class="ph ph-buildings"></i>{{ $job['company'] }}</span>
                    <span><i class="ph ph-map-pin"></i>{{ $job['location'] }}</span>
                    <span><i class="ph ph-calendar-blank"></i>{{ $job['posted'] }}</span>
                  </div>
                  <p class="job-card__excerpt">{{ $job['description'] }}</p>
                  <div class="job-card__tags">
                    <span class="job-tag">{{ $job['category'] }}</span>
                    @foreach($job['types'] as $type)
                      <span class="job-tag job-tag--alt">{{ $type }}</span>
                    @endforeach
                  </div>
                </div>
                <span class="job-card__chevron" aria-hidden="true"></span>
              </summary>

              <div class="job-panel">
                <div class="job-panel__details">
                  <span><i class="ph ph-currency-eur"></i>{{ $job['salary'] }}</span>
                  <span><i class="ph ph-briefcase"></i>{{ implode(', ', $job['types']) }}</span>
                  <span><i class="ph ph-squares-four"></i>{{ $job['category'] }}</span>
                </div>
                <p>{{ $job['description'] }}</p>
                <ul class="job-panel__list">
                  @foreach($job['highlights'] as $highlight)
                    <li>{{ $highlight }}</li>
                  @endforeach
                </ul>
                <div class="job-panel__actions">
                  <a class="btn btn-primary" href="{{ route('pages.contact') }}">Sollicitatie starten</a>
                  <button class="btn btn-ghost job-save-button" type="button" data-save-job>
                    <i class="ph ph-bookmark-simple"></i>
                    <span>Bewaren</span>
                  </button>
                </div>
              </div>
            </details>
          @empty
            <article class="card jobs-empty">
              <h2>Geen vacatures gevonden</h2>
              <p>Pas je filters aan of reset de zoekopdracht om meer resultaten te zien.</p>
              <a class="btn btn-primary" href="{{ route('welcome') }}">Filters resetten</a>
            </article>
          @endforelse
        </div>
      </section>
    </div>

    <section class="login-band" id="login" aria-label="Inloggen">
      <div>
        <p class="jobs-intro__eyebrow">Inloggen</p>
        <h2>Direct naar je omgeving</h2>
      </div>
      <div class="login-band__actions">
        <a class="btn btn-ghost" href="{{ route('login.werkzoekende') }}">Werkzoekende</a>
        <a class="btn btn-ghost" href="{{ route('login.werkgever') }}">Werkgever</a>
        <a class="btn btn-primary" href="{{ route('admin.login') }}">Admin</a>
      </div>
    </section>
  </div>
</section>
@endsection

@push('styles')
@if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
  @vite(['resources/css/jobs.css'])
@endif
@endpush

@push('scripts')
<script>
(function () {
  const forms = document.querySelectorAll('[data-auto-submit-form]');

  forms.forEach(function (form) {
    let timer = null;

    form.querySelectorAll('[data-auto-submit="input"]').forEach(function (input) {
      input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
          form.submit();
        }, 450);
      });
    });
  });

  document.querySelectorAll('[data-auto-submit-checkbox]').forEach(function (checkbox) {
    checkbox.addEventListener('change', function () {
      checkbox.closest('form').submit();
    });
  });

  document.querySelectorAll('[data-save-job]').forEach(function (button) {
    button.addEventListener('click', function () {
      const label = button.querySelector('span');
      const saved = button.classList.toggle('is-saved');
      if (label) {
        label.textContent = saved ? 'Bewaard' : 'Bewaren';
      }
      button.setAttribute('aria-pressed', saved ? 'true' : 'false');
    });
  });
})();
</script>
@endpush
