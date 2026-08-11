@extends('layouts.app')

@section('title', 'Werkzoekende dashboard | JobBoardSoftware')
@section('meta_description', 'Werkzoekendedashboard voor aanbevolen vacatures, sollicitaties, job alerts en bewaarde vacatures.')

@php
  $stats = [
    ['label' => 'Nieuwe matches', 'value' => '12'],
    ['label' => 'Bewaarde vacatures', 'value' => '7'],
    ['label' => 'Sollicitaties', 'value' => '4'],
    ['label' => 'Actieve alerts', 'value' => '3'],
  ];

  $matches = [
    ['title' => 'Laravel Developer', 'company' => 'BrightApps', 'meta' => 'Amsterdam - Fulltime', 'match' => '92% match'],
    ['title' => 'Freelance UX Designer', 'company' => 'NicheJobs', 'meta' => 'Remote - Freelance', 'match' => '86% match'],
    ['title' => 'Operations Specialist', 'company' => 'WorkGrid', 'meta' => 'Eindhoven - Parttime', 'match' => '78% match'],
  ];

  $applications = [
    ['title' => 'Customer Success Manager', 'company' => 'HirePilot', 'status' => 'Gesprek gepland', 'updated' => 'Vandaag'],
    ['title' => 'Recruitment Marketeer', 'company' => 'TalentFlow', 'status' => 'In behandeling', 'updated' => 'Gisteren'],
    ['title' => 'Sales Development Representative', 'company' => 'BoardWorks', 'status' => 'Verzonden', 'updated' => '3 dagen geleden'],
  ];
@endphp

@section('content')
<section class="dash-page">
  <div class="dash-shell">
    <header class="dash-topbar">
      <div>
        <p class="dash-eyebrow">Werkzoekende omgeving</p>
        <h1 class="dash-title">Welkom terug, {{ $user->name }}</h1>
        <p class="dash-subtitle">Volg je sollicitaties, bekijk aanbevolen vacatures en beheer job alerts vanuit je persoonlijke dashboard.</p>
      </div>
      <aside class="dash-user" aria-label="Ingelogde werkzoekende">
        <strong>{{ $user->name }}</strong>
        <span>{{ $user->email }}</span>
        <span>Rol: Werkzoekende</span>
      </aside>
    </header>

    <div class="dash-stats">
      @foreach($stats as $stat)
        <article class="dash-stat">
          <span>{{ $stat['label'] }}</span>
          <strong>{{ $stat['value'] }}</strong>
        </article>
      @endforeach
    </div>

    <div class="dash-layout">
      <main class="dash-main">
        <section class="dash-panel" aria-labelledby="candidate-matches-title">
          <div class="dash-panel__head">
            <div>
              <h2 id="candidate-matches-title">Aanbevolen vacatures</h2>
              <p>Vacatures die passen bij je profiel, locatie en voorkeuren.</p>
            </div>
            <a class="dash-btn dash-btn--primary" href="{{ route('welcome') }}#vacatures">
              <i class="ph ph-magnifying-glass"></i>
              Vacatures zoeken
            </a>
          </div>

          <table class="dash-table">
            <thead>
              <tr>
                <th>Vacature</th>
                <th>Werkgever</th>
                <th>Match</th>
                <th>Actie</th>
              </tr>
            </thead>
            <tbody>
              @foreach($matches as $match)
                <tr>
                  <td>
                    <span class="dash-cell-title">{{ $match['title'] }}</span>
                    <span class="dash-cell-meta">{{ $match['meta'] }}</span>
                  </td>
                  <td>{{ $match['company'] }}</td>
                  <td><span class="dash-status">{{ $match['match'] }}</span></td>
                  <td><a class="dash-btn dash-btn--ghost" href="{{ route('welcome') }}#vacatures">Bekijken</a></td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </section>

        <section class="dash-panel" aria-labelledby="candidate-applications-title">
          <div class="dash-panel__head">
            <div>
              <h2 id="candidate-applications-title">Sollicitaties</h2>
              <p>Statussen en laatste activiteit van je sollicitaties.</p>
            </div>
          </div>

          <table class="dash-table">
            <thead>
              <tr>
                <th>Vacature</th>
                <th>Status</th>
                <th>Bijgewerkt</th>
              </tr>
            </thead>
            <tbody>
              @foreach($applications as $application)
                <tr>
                  <td>
                    <span class="dash-cell-title">{{ $application['title'] }}</span>
                    <span class="dash-cell-meta">{{ $application['company'] }}</span>
                  </td>
                  <td><span class="dash-status dash-status--accent">{{ $application['status'] }}</span></td>
                  <td>{{ $application['updated'] }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </section>
      </main>

      <aside class="dash-sidebar">
        <section class="dash-card">
          <h2>Snelle acties</h2>
          <p>Ga direct naar de belangrijkste werkzoekende flows.</p>
          <div class="dash-actions dash-actions--spaced">
            <a class="dash-btn dash-btn--primary" href="{{ route('pages.job-alerts') }}">Job alert maken</a>
            <a class="dash-btn dash-btn--ghost" href="{{ route('pages.nieuwsbrief') }}">Nieuwsbrief</a>
          </div>
        </section>

        <section class="dash-card">
          <h2>Profiel voortgang</h2>
          <p>Een vollediger profiel maakt betere matches mogelijk.</p>
          <div class="dash-progress" aria-label="Profiel voortgang">
            <div class="dash-progress__track"><span class="dash-progress__bar dash-progress__bar--candidate"></span></div>
            <span class="dash-cell-meta">74% compleet</span>
          </div>
          <ul class="dash-checklist">
            <li><i class="ph ph-check-circle"></i>Account aangemaakt</li>
            <li><i class="ph ph-check-circle"></i>E-mailadres bevestigd</li>
            <li><i class="ph ph-circle"></i>CV uploaden</li>
            <li><i class="ph ph-circle"></i>Voorkeurslocaties toevoegen</li>
          </ul>
        </section>

        <section class="dash-card">
          <h2>Actieve job alerts</h2>
          <ul class="dash-list">
            <li>
              <div>
                <strong>Laravel Developer</strong>
                <span>Amsterdam + remote</span>
              </div>
              <span>Dagelijks</span>
            </li>
            <li>
              <div>
                <strong>UX Designer</strong>
                <span>Freelance opdrachten</span>
              </div>
              <span>Wekelijks</span>
            </li>
          </ul>
        </section>

        <form method="POST" action="{{ route('logout') }}" class="dash-card">
          @csrf
          <h2>Sessie</h2>
          <p>Je bent ingelogd als werkzoekende.</p>
          <div class="dash-actions dash-actions--spaced">
            <button class="dash-btn dash-btn--ghost" type="submit">Uitloggen</button>
          </div>
        </form>
      </aside>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('dashboard.partials.styles')
@endpush
