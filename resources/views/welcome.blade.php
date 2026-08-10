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
    <section class="jobs-intro" aria-labelledby="jobs-intro-title">
      <div>
        <p class="jobs-intro__eyebrow">SaaS job board software</p>
        <h1 id="jobs-intro-title">Een vacaturebank die al aanvoelt als een echt platform.</h1>
      </div>
      <p class="jobs-intro__text">Deze eerste versie bootst de publieke header, footer en zoekervaring van het Studentinhuren platform na, maar zet de inhoud alvast richting white-label job board software.</p>
    </section>

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
              <article class="card job-promo-card" id="werkgevers">
                <div class="job-promo-card__icon"><i class="ph ph-rocket-launch"></i></div>
                <div>
                  <h2>Publiceer vacatures met een eigen portal</h2>
                  <p>Werkgevers krijgen straks self-service plaatsingen, beheer van reacties en branded bedrijfspagina's.</p>
                </div>
                <a href="#contact" class="btn btn-primary">Demo aanvragen</a>
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
                  <a class="btn btn-primary" href="#contact">Sollicitatie starten</a>
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

    <section class="platform-band" id="workflow" aria-labelledby="workflow-title">
      <div>
        <p class="jobs-intro__eyebrow">Workflow</p>
        <h2 id="workflow-title">Van vacature tot reactie in een herkenbare platformflow.</h2>
      </div>
      <div class="platform-steps">
        <article>
          <span>1</span>
          <h3>Publiceer</h3>
          <p>Werkgevers plaatsen straks vacatures met categorieen, type, locatie en publicatiestatus.</p>
        </article>
        <article>
          <span>2</span>
          <h3>Vindbaar</h3>
          <p>De publieke vacaturebank geeft bezoekers dezelfde filterervaring als een volwassen job board.</p>
        </article>
        <article>
          <span>3</span>
          <h3>Beheer</h3>
          <p>Reacties, kandidaten en analytics kunnen hierna achter een SaaS dashboard worden gezet.</p>
        </article>
      </div>
    </section>

    <section class="feature-grid" id="features" aria-label="Features">
      <article>
        <i class="ph ph-funnel"></i>
        <h3>Filters en zoekopdracht</h3>
        <p>Keyword, locatie, categorie, type, actieve filterchips en resetlinks.</p>
      </article>
      <article>
        <i class="ph ph-layout"></i>
        <h3>Publieke layout</h3>
        <p>Sticky header, dropdowns, mobiele navigatie en footer in de stijl van het referentieproject.</p>
      </article>
      <article>
        <i class="ph ph-buildings"></i>
        <h3>SaaS-ready inhoud</h3>
        <p>Vacaturekaarten en CTA's zijn alvast richting werkgevers, portals en demo-aanvragen geschreven.</p>
      </article>
    </section>

    <section class="pricing-band" id="pricing" aria-labelledby="pricing-title">
      <div>
        <p class="jobs-intro__eyebrow">Prijzen</p>
        <h2 id="pricing-title">Pakketten voor een eigen vacaturebank.</h2>
        <p>Kies straks tussen self-service vacatureplaatsingen, employer portals of een volledig white-label platform.</p>
      </div>
      <div class="pricing-options">
        <article>
          <span>Starter</span>
          <strong>Voor niche vacaturebanken</strong>
          <p>Vacatures, filters, basis bedrijfspagina's en reacties.</p>
        </article>
        <article>
          <span>Platform</span>
          <strong>Voor SaaS exploitatie</strong>
          <p>Werkgeversaccounts, pakketten, dashboards en analytics.</p>
        </article>
      </div>
    </section>

    <section class="contact-band" id="contact" aria-labelledby="contact-title">
      <div>
        <p class="jobs-intro__eyebrow">Volgende stap</p>
        <h2 id="contact-title">Klaar om hier een echte multi-tenant job board SaaS van te maken.</h2>
        <p>De voorkant staat. De logische volgende laag is werkgevers, vacatures, bedrijfspagina's en reacties als database-gedreven modules.</p>
      </div>
      <a class="btn btn-primary" href="mailto:{{ config('mail.from.address', 'support@jobboardsoftware.test') }}">Contact opnemen</a>
    </section>

    <section class="login-anchor" id="login" aria-label="Inloggen">
      <p>Inloggen wordt aangesloten zodra de accountrollen voor eigenaar, werkgever en kandidaat zijn ingericht.</p>
    </section>
  </div>
</section>
@endsection

@push('styles')
<style>
.public-page {
  padding: 0 24px 72px;
}

.public-page__inner {
  width: min(1140px, 100%);
  margin: 0 auto;
}

.jobs-intro {
  display: grid;
  grid-template-columns: minmax(0, 0.9fr) minmax(280px, 0.55fr);
  gap: 32px;
  align-items: end;
  padding: 54px 0 24px;
}

.jobs-intro__eyebrow {
  margin: 0 0 10px;
  color: var(--color-primary-strong);
  font-family: var(--font-ui);
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.jobs-intro h1,
.jobs-intro h2,
.platform-band h2,
.contact-band h2 {
  margin: 0;
  font-size: clamp(30px, 4vw, 48px);
  font-weight: 800;
  letter-spacing: 0;
}

.jobs-intro__text,
.contact-band p {
  margin: 0;
  color: var(--color-text-muted);
  font-size: 15px;
  line-height: 1.7;
}

.jobs-filter {
  padding: 26px 0 0;
}

.filter-header {
  width: min(940px, 100%);
  padding: 0 0 16px;
}

.filter-header__row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.filter-header h2 {
  margin: 0;
  font-family: 'Work Sans', var(--font-ui);
  font-size: 30px;
  line-height: 1.1;
  font-weight: 700;
}

.filter-header p {
  margin: 9px 0 0;
  font-size: 15px;
  color: rgba(39, 49, 58, 0.62);
}

.filter-row {
  display: grid;
  grid-template-columns: 1fr;
  align-items: center;
  gap: 12px;
  width: min(900px, 100%);
}

.search-basic {
  display: grid;
  grid-template-columns: minmax(320px, 1fr) minmax(220px, 0.62fr);
  gap: 12px;
  width: 100%;
  min-width: 0;
}

.search_location,
.search_keywords {
  position: relative;
  min-width: 0;
}

.search-basic input[type="text"] {
  width: 100%;
  height: 48px;
  padding: 0 12px 0 38px;
  font-size: 15px;
  border: 1px solid #dedede;
  border-radius: 8px;
  background-color: #ffffff;
  color: var(--color-text);
  transition: border-color .2s ease, box-shadow .2s ease;
}

.search-basic input[type="text"]:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px var(--color-primary-soft);
}

.search_keywords input::placeholder,
.search_location input::placeholder {
  color: #7c8790;
  font-size: 15px;
  font-style: italic;
}

.search_keywords::before,
.search_location::before {
  content: '';
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 16px;
  height: 16px;
  background-repeat: no-repeat;
  background-size: contain;
  pointer-events: none;
}

.search_keywords::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%233f7296' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E");
}

.search_location::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%233f7296' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12 2a7 7 0 0 1 7 7c0 5.25-7 13-7 13S5 14.25 5 9a7 7 0 0 1 7-7z'/%3E%3Ccircle cx='12' cy='9' r='2.5'/%3E%3C/svg%3E");
}

.active-filters {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin: 14px 0 0;
}

.active-filter {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #ffffff;
  border: 1px solid #dedede;
  border-radius: 999px;
  padding: 8px 12px;
  font-family: var(--font-ui);
  font-size: 14px;
  color: #333333;
  font-weight: 700;
}

.active-filter--alt {
  background: var(--color-accent-soft);
  border-color: rgba(217, 154, 91, 0.3);
}

.active-filter:hover {
  text-decoration: none;
  border-color: var(--color-primary);
}

.jobs-filter__fallback {
  display: flex;
  gap: 12px;
  padding: 16px 0 0;
}

.jobs-section-divider {
  height: 1px;
  background: var(--color-border);
  margin: 38px 0 34px;
}

.jobs-results-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 14px;
}

.jobs-results-count,
.jobs-results-mode {
  margin: 0;
  font-size: 14px;
  color: var(--color-text-muted);
}

.jobs-results-mode {
  color: var(--color-text-soft);
}

.jobs-reset-link {
  color: var(--color-primary);
  font-family: var(--font-ui);
  font-weight: 700;
}

.jobs-content-layout {
  display: grid;
  grid-template-columns: 300px minmax(0, 1fr);
  gap: 24px;
  align-items: start;
}

.jobs-sidebar {
  position: sticky;
  top: 112px;
}

.sidebar-filter-form {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.sidebar-filter-group,
.card {
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  box-shadow: var(--shadow-sm);
}

.sidebar-filter-group {
  overflow: hidden;
}

.sidebar-filter-group__title {
  margin: 0;
  padding: 13px 16px;
  border-bottom: 1px solid var(--color-border);
  font-family: var(--font-ui);
  font-size: 14px;
  font-weight: 800;
}

.sidebar-filter-group__body {
  padding: 8px 0;
}

.sidebar-filter-option {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 16px;
  cursor: pointer;
}

.sidebar-filter-option:hover {
  background: var(--color-primary-soft);
}

.sidebar-filter-option input[type="checkbox"] {
  width: 16px;
  height: 16px;
  flex-shrink: 0;
  accent-color: var(--color-primary);
  cursor: pointer;
}

.sidebar-filter-option__label {
  flex: 1;
  min-width: 0;
  color: var(--color-text);
  font-size: 13px;
  font-weight: 400;
}

.sidebar-filter-count {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 22px;
  padding: 1px 8px;
  border-radius: 999px;
  background: var(--color-primary-soft);
  color: var(--color-primary-strong);
  font-size: 11px;
  font-weight: 700;
}

.sidebar-filter-reset {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 4px;
  color: var(--color-text-muted);
  font-size: 13px;
}

.jobs-results {
  display: grid;
  gap: 12px;
}

.job-card {
  overflow: hidden;
}

.job-card[open] {
  border-color: var(--color-primary-muted);
}

.job-card__summary {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 20px 24px;
  cursor: pointer;
  list-style: none;
  transition: background .15s ease;
}

.job-card__summary::-webkit-details-marker {
  display: none;
}

.job-card__summary:hover,
.job-card[open] .job-card__summary {
  background: var(--color-primary-soft);
}

.job-card__main {
  min-width: 0;
  flex: 1;
}

.job-card__title {
  margin: 0;
  font-family: var(--font-ui);
  font-size: 20px;
  font-weight: 800;
  line-height: 1.3;
}

.job-card__meta {
  margin: 6px 0 0;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 4px 16px;
}

.job-card__meta span {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  color: var(--color-text-muted);
  font-size: 14px;
}

.job-card__meta i,
.job-panel__details i {
  color: var(--color-primary);
  font-size: 15px;
}

.job-card__excerpt {
  margin: 8px 0 0;
  color: var(--color-text);
  font-size: 14px;
  font-weight: 300;
}

.job-card__tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 10px;
}

.job-tag {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  padding: 4px 10px;
  border: 1px solid var(--color-primary-muted);
  background: var(--color-primary-soft);
  color: var(--color-primary-strong);
  font-size: 12px;
  font-weight: 700;
}

.job-tag--alt {
  border-color: rgba(217, 154, 91, 0.32);
  background: var(--color-accent-soft);
  color: var(--color-accent-strong);
}

.job-card__chevron {
  flex-shrink: 0;
  width: 9px;
  height: 9px;
  border-right: 2px solid rgba(0, 0, 0, 0.25);
  border-top: 2px solid rgba(0, 0, 0, 0.25);
  transform: rotate(45deg);
  transition: border-color .15s ease, transform .2s ease;
}

.job-card[open] .job-card__chevron {
  transform: rotate(135deg);
  border-color: var(--color-primary);
}

.job-panel {
  border-top: 1px solid var(--color-border);
  padding: 24px;
  display: grid;
  gap: 18px;
}

.job-panel p {
  margin: 0;
  color: var(--color-text);
  font-size: 15px;
}

.job-panel__details {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.job-panel__details span {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 12px;
  border: 1px solid var(--color-primary-muted);
  border-radius: 999px;
  background: var(--color-primary-soft);
  font-size: 13px;
  font-weight: 600;
}

.job-panel__list {
  margin: 0;
  padding: 0;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  list-style: none;
}

.job-panel__list li {
  margin: 0;
  padding: 6px 10px;
  border-radius: 6px;
  background: #f4f7fa;
  color: var(--color-text-muted);
  font-size: 13px;
}

.job-panel__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  min-height: 42px;
  padding: 0 16px;
  border: 1px solid transparent;
  border-radius: 6px;
  font-family: var(--font-ui);
  font-size: 15px;
  font-weight: 700;
  line-height: 1.2;
  text-decoration: none;
  cursor: pointer;
}

.btn:hover {
  text-decoration: none;
}

.btn-primary {
  color: #ffffff;
  background: var(--color-primary-strong);
  border-color: var(--color-primary-strong);
}

.btn-primary:hover {
  background: #274f6b;
  border-color: #274f6b;
}

.btn-ghost {
  color: var(--color-primary-strong);
  background: #ffffff;
  border-color: var(--color-border-strong);
}

.btn-ghost:hover,
.job-save-button.is-saved {
  background: var(--color-primary-soft);
  border-color: var(--color-primary);
}

.job-promo-card {
  display: flex;
  align-items: center;
  gap: 18px;
  padding: 24px;
  background: var(--color-primary-strong);
  color: #ffffff;
  border-color: transparent;
}

.job-promo-card__icon {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.12);
  flex-shrink: 0;
}

.job-promo-card__icon i {
  font-size: 26px;
}

.job-promo-card h2,
.job-promo-card p {
  color: #ffffff;
}

.job-promo-card h2 {
  margin: 0 0 4px;
  font-size: 18px;
}

.job-promo-card p {
  margin: 0;
  font-size: 14px;
  opacity: 0.84;
}

.job-promo-card .btn {
  margin-left: auto;
  background: #ffffff;
  border-color: #ffffff;
  color: var(--color-primary-strong);
  flex-shrink: 0;
}

.jobs-empty {
  padding: 24px;
  display: grid;
  gap: 10px;
}

.jobs-empty h2,
.jobs-empty p {
  margin: 0;
}

.platform-band,
.contact-band {
  margin-top: 56px;
  padding: 34px;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  background: #ffffff;
  box-shadow: var(--shadow-sm);
}

.platform-band {
  display: grid;
  gap: 24px;
}

.platform-band h2,
.contact-band h2 {
  font-size: clamp(26px, 3vw, 36px);
}

.platform-steps {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 16px;
}

.platform-steps article,
.feature-grid article {
  padding: 20px;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  background: #fbfdff;
}

.platform-steps span {
  display: inline-flex;
  width: 32px;
  height: 32px;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  background: var(--color-accent-soft);
  color: var(--color-accent-strong);
  font-family: var(--font-ui);
  font-weight: 800;
}

.platform-steps h3,
.feature-grid h3 {
  margin: 14px 0 7px;
  font-size: 17px;
}

.platform-steps p,
.feature-grid p {
  margin: 0;
  color: var(--color-text-muted);
  font-size: 14px;
}

.feature-grid {
  margin-top: 18px;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 16px;
}

.feature-grid i {
  display: inline-flex;
  width: 42px;
  height: 42px;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  background: var(--color-primary-soft);
  color: var(--color-primary-strong);
  font-size: 23px;
}

.pricing-band {
  margin-top: 18px;
  display: grid;
  grid-template-columns: minmax(0, 0.8fr) minmax(320px, 0.7fr);
  gap: 24px;
  align-items: start;
  padding: 34px 0 0;
}

.pricing-band h2 {
  margin: 0;
  font-size: clamp(26px, 3vw, 36px);
  font-weight: 800;
}

.pricing-band p {
  margin: 10px 0 0;
  color: var(--color-text-muted);
  font-size: 15px;
}

.pricing-options {
  display: grid;
  gap: 12px;
}

.pricing-options article {
  padding: 20px;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  background: #ffffff;
  box-shadow: var(--shadow-sm);
}

.pricing-options span {
  display: inline-flex;
  margin-bottom: 10px;
  padding: 4px 10px;
  border-radius: 999px;
  background: var(--color-accent-soft);
  color: var(--color-accent-strong);
  font-family: var(--font-ui);
  font-size: 12px;
  font-weight: 800;
}

.pricing-options strong {
  display: block;
  color: var(--color-text);
  font-family: var(--font-ui);
  font-size: 17px;
}

.pricing-options p {
  margin: 7px 0 0;
  font-size: 14px;
}

.contact-band {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
}

.contact-band > div {
  max-width: 760px;
}

.login-anchor {
  min-height: 1px;
  color: transparent;
}

@media (max-width: 980px) {
  .jobs-intro,
  .jobs-content-layout,
  .pricing-band,
  .platform-steps,
  .feature-grid {
    grid-template-columns: 1fr;
  }

  .jobs-sidebar {
    position: static;
    order: 2;
  }

  .jobs-results-section {
    order: 1;
  }

  .jobs-content-layout {
    display: flex;
    flex-direction: column;
  }

  .contact-band,
  .job-promo-card {
    align-items: flex-start;
    flex-direction: column;
  }

  .job-promo-card .btn {
    margin-left: 0;
  }
}

@media (max-width: 720px) {
  .public-page {
    padding: 0 18px 56px;
  }

  .jobs-intro {
    padding-top: 38px;
  }

  .search-basic {
    grid-template-columns: 1fr;
  }

  .filter-header__row,
  .jobs-results-head {
    align-items: flex-start;
    flex-direction: column;
  }

  .job-card__summary {
    padding: 18px;
  }

  .job-panel,
  .platform-band,
  .contact-band {
    padding: 20px;
  }
}
</style>
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
