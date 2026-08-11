@extends('layouts.app')

@section('title', 'Werkzoekende | JobBoardSoftware')
@section('meta_description', 'Vind vacatures, stel job alerts in, ontvang de nieuwsbrief en maak een werkzoekende account aan.')

@section('content')
<section class="content-page">
  <div class="content-page__shell">
    <header class="content-hero">
      <div class="content-hero__copy">
        <p class="content-eyebrow">Werkzoekende</p>
        <h1>Vind sneller vacatures die bij je passen.</h1>
        <p class="content-hero__intro">Doorzoek vacatures, bewaar interessante functies en gebruik straks alerts om automatisch op de hoogte te blijven van nieuwe kansen.</p>
        <div class="content-actions">
          <a class="content-btn content-btn--primary" href="{{ route('register.werkzoekende') }}">Account aanmaken</a>
          <a class="content-btn content-btn--ghost" href="{{ route('welcome') }}#vacatures">Vacatures bekijken</a>
        </div>
      </div>
      <aside class="content-visual" aria-label="Werkzoekende overzicht">
        <i class="ph ph-magnifying-glass"></i>
        <strong>Zoeken, volgen en reageren vanuit een eigen omgeving.</strong>
        <span>Een kandidaatgerichte ingang voor de publieke vacaturebank.</span>
      </aside>
    </header>

    <div class="content-grid">
      <article class="content-card">
        <i class="ph ph-bell-ringing"></i>
        <h3>Job alerts</h3>
        <p>Maak meldingen aan voor zoektermen, locaties en functies die relevant zijn voor jouw volgende stap.</p>
        <div class="content-actions">
          <a class="content-btn content-btn--ghost" href="{{ route('pages.job-alerts') }}">Bekijk job alerts</a>
        </div>
      </article>
      <article class="content-card">
        <i class="ph ph-envelope-simple"></i>
        <h3>Nieuwsbrief</h3>
        <p>Ontvang updates met nieuwe vacatures, platformnieuws en praktische tips voor je zoektocht.</p>
        <div class="content-actions">
          <a class="content-btn content-btn--ghost" href="{{ route('pages.nieuwsbrief') }}">Naar nieuwsbrief</a>
        </div>
      </article>
      <article class="content-card">
        <i class="ph ph-user-circle-plus"></i>
        <h3>Account aanmaken</h3>
        <p>Registreer als werkzoekende om je profiel en sollicitatiestappen straks centraal te beheren.</p>
        <div class="content-actions">
          <a class="content-btn content-btn--ghost" href="{{ route('register.werkzoekende') }}">Start registratie</a>
        </div>
      </article>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('pages.partials.page-styles')
@endpush
