@extends('layouts.app')

@section('title', 'Werkgever | JobBoardSoftware')
@section('meta_description', 'Plaats vacatures, beheer reacties en maak een werkgeversaccount aan binnen JobBoardSoftware.')

@section('content')
<section class="content-page">
  <div class="content-page__shell">
    <header class="content-hero">
      <div class="content-hero__copy">
        <p class="content-eyebrow">Werkgever</p>
        <h1>Publiceer vacatures vanuit een eigen werkgeversomgeving.</h1>
        <p class="content-hero__intro">Voor werkgevers komt er een self-service omgeving om vacatures te plaatsen, reacties te beheren en de zichtbaarheid van functies te verbeteren.</p>
        <div class="content-actions">
          <a class="content-btn content-btn--primary" href="{{ route('pages.vacature-plaatsen') }}">Vacature plaatsen</a>
          <a class="content-btn content-btn--ghost" href="{{ route('register.werkgever') }}">Account aanmaken</a>
        </div>
      </div>
      <aside class="content-visual" aria-label="Werkgever overzicht">
        <i class="ph ph-buildings"></i>
        <strong>Employer self-service</strong>
        <span>Een basis voor plaatsingen, rollen, bedrijfspagina's en kandidatenbeheer.</span>
      </aside>
    </header>

    <div class="content-grid">
      <article class="content-card">
        <i class="ph ph-briefcase"></i>
        <h3>Vacature plaatsen</h3>
        <p>Maak vacatures aan met functie-informatie, locatie, categorie en type dienstverband.</p>
      </article>
      <article class="content-card">
        <i class="ph ph-currency-eur"></i>
        <h3>Tarieven</h3>
        <p>Kies straks uit pakketten voor losse plaatsingen, abonnementen of maatwerk portals.</p>
      </article>
      <article class="content-card">
        <i class="ph ph-user-circle-plus"></i>
        <h3>Account aanmaken</h3>
        <p>Registreer een werkgever om vacatures en reacties centraal te beheren.</p>
      </article>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('pages.partials.page-styles')
@endpush
