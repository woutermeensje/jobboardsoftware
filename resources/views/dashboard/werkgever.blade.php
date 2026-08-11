@extends('layouts.app')

@section('title', 'Werkgever dashboard | JobBoardSoftware')
@section('meta_description', 'Werkgeversdashboard voor vacatures, reacties, kandidaten en plaatsingen.')

@php
  $stats = [
    ['label' => 'Open vacatures', 'value' => '6'],
    ['label' => 'Nieuwe reacties', 'value' => '18'],
    ['label' => 'Concepten', 'value' => '3'],
    ['label' => 'Views deze week', 'value' => '1.284'],
  ];

  $jobs = [
    ['title' => 'Laravel Developer', 'meta' => 'Amsterdam - Fulltime', 'status' => 'Gepubliceerd', 'responses' => '8 reacties', 'updated' => 'Vandaag'],
    ['title' => 'Recruitment Marketeer', 'meta' => 'Rotterdam - Parttime', 'status' => 'Concept', 'responses' => 'Nog niet live', 'updated' => 'Gisteren'],
    ['title' => 'Customer Success Manager', 'meta' => 'Utrecht - Hybrid', 'status' => 'Screening', 'responses' => '5 reacties', 'updated' => '2 dagen geleden'],
  ];

  $candidates = [
    ['name' => 'Sanne de Vries', 'role' => 'Laravel Developer', 'stage' => 'Nieuw'],
    ['name' => 'Milan Bakker', 'role' => 'Customer Success Manager', 'stage' => 'Interview'],
    ['name' => 'Nora Jansen', 'role' => 'Recruitment Marketeer', 'stage' => 'Shortlist'],
  ];
@endphp

@section('content')
<section class="dash-page">
  <div class="dash-shell">
    <header class="dash-topbar">
      <div>
        <p class="dash-eyebrow">Werkgever omgeving</p>
        <h1 class="dash-title">Welkom terug, {{ $user->name }}</h1>
        <p class="dash-subtitle">Beheer vacatures, volg reacties en bereid nieuwe plaatsingen voor vanuit een centraal werkgeversdashboard.</p>
      </div>
      <aside class="dash-user" aria-label="Ingelogde werkgever">
        <strong>{{ $user->company_name ?: 'Werkgeversaccount' }}</strong>
        <span>{{ $user->email }}</span>
        <span>Rol: Werkgever</span>
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
        <section class="dash-panel" aria-labelledby="employer-jobs-title">
          <div class="dash-panel__head">
            <div>
              <h2 id="employer-jobs-title">Vacatures</h2>
              <p>Publicatiestatus, reacties en laatste activiteit.</p>
            </div>
            <a class="dash-btn dash-btn--primary" href="{{ route('pages.vacature-plaatsen') }}">
              <i class="ph ph-plus"></i>
              Vacature plaatsen
            </a>
          </div>

          <table class="dash-table">
            <thead>
              <tr>
                <th>Vacature</th>
                <th>Status</th>
                <th>Reacties</th>
                <th>Bijgewerkt</th>
              </tr>
            </thead>
            <tbody>
              @foreach($jobs as $job)
                <tr>
                  <td>
                    <span class="dash-cell-title">{{ $job['title'] }}</span>
                    <span class="dash-cell-meta">{{ $job['meta'] }}</span>
                  </td>
                  <td>
                    <span class="dash-status {{ $job['status'] === 'Concept' ? 'dash-status--muted' : ($job['status'] === 'Screening' ? 'dash-status--accent' : '') }}">{{ $job['status'] }}</span>
                  </td>
                  <td>{{ $job['responses'] }}</td>
                  <td>{{ $job['updated'] }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </section>

        <section class="dash-panel" aria-labelledby="employer-candidates-title">
          <div class="dash-panel__head">
            <div>
              <h2 id="employer-candidates-title">Recente kandidaten</h2>
              <p>Nieuwe reacties en opvolging per vacature.</p>
            </div>
          </div>

          <table class="dash-table">
            <thead>
              <tr>
                <th>Kandidaat</th>
                <th>Vacature</th>
                <th>Fase</th>
              </tr>
            </thead>
            <tbody>
              @foreach($candidates as $candidate)
                <tr>
                  <td><span class="dash-cell-title">{{ $candidate['name'] }}</span></td>
                  <td>{{ $candidate['role'] }}</td>
                  <td><span class="dash-status dash-status--accent">{{ $candidate['stage'] }}</span></td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </section>
      </main>

      <aside class="dash-sidebar">
        <section class="dash-card">
          <h2>Snelle acties</h2>
          <p>Start de belangrijkste werkgeversflows.</p>
          <div class="dash-actions dash-actions--spaced">
            <a class="dash-btn dash-btn--primary" href="{{ route('pages.vacature-plaatsen') }}">Vacature plaatsen</a>
            <a class="dash-btn dash-btn--ghost" href="{{ route('pages.tarieven') }}">Tarieven</a>
          </div>
        </section>

        <section class="dash-card">
          <h2>Account voortgang</h2>
          <p>Maak het werkgeversprofiel publicatieklaar.</p>
          <div class="dash-progress" aria-label="Account voortgang">
            <div class="dash-progress__track"><span class="dash-progress__bar dash-progress__bar--employer"></span></div>
            <span class="dash-cell-meta">68% compleet</span>
          </div>
          <ul class="dash-checklist">
            <li><i class="ph ph-check-circle"></i>Bedrijfsgegevens ingevuld</li>
            <li><i class="ph ph-check-circle"></i>Eerste vacature voorbereid</li>
            <li><i class="ph ph-circle"></i>Bedrijfspagina publiceren</li>
          </ul>
        </section>

        <section class="dash-card">
          <h2>Planning</h2>
          <ul class="dash-list">
            <li>
              <div>
                <strong>2 interviews</strong>
                <span>Deze week gepland</span>
              </div>
              <span>Week 33</span>
            </li>
            <li>
              <div>
                <strong>3 vacatures</strong>
                <span>Controle nodig voor publicatie</span>
              </div>
              <span>Actie</span>
            </li>
          </ul>
        </section>

        <form method="POST" action="{{ route('logout') }}" class="dash-card">
          @csrf
          <h2>Sessie</h2>
          <p>Je bent ingelogd als werkgever.</p>
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
