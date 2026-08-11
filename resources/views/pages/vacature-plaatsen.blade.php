@extends('layouts.app')

@section('title', 'Vacature plaatsen | JobBoardSoftware')
@section('meta_description', 'Start met het plaatsen van een vacature en maak een werkgeversaccount aan.')

@section('content')
<section class="content-page">
  <div class="content-page__shell">
    <header class="content-hero">
      <div class="content-hero__copy">
        <p class="content-eyebrow">Vacature plaatsen</p>
        <h1>Start een nieuwe vacatureplaatsing.</h1>
        <p class="content-hero__intro">Vul de kern van de vacature in en ga daarna door naar een werkgeversaccount om de plaatsing straks te beheren.</p>
      </div>
      <aside class="content-visual" aria-label="Vacature plaatsen">
        <i class="ph ph-rocket-launch"></i>
        <strong>Van concept naar publicatie</strong>
        <span>Een eerste plaatsingsflow voor werkgevers binnen de SaaS omgeving.</span>
      </aside>
    </header>

    <section class="content-section" aria-labelledby="posting-title">
      <h2 id="posting-title">Vacaturegegevens</h2>
      <form class="content-form" method="GET" action="{{ route('register.werkgever') }}">
        <div class="content-form__grid">
          <div class="content-field">
            <label for="job-title">Functietitel</label>
            <input id="job-title" name="job_title" type="text" placeholder="Bijvoorbeeld Sales Development Representative">
          </div>
          <div class="content-field">
            <label for="company">Bedrijfsnaam</label>
            <input id="company" name="company" type="text" placeholder="Naam van de organisatie">
          </div>
          <div class="content-field">
            <label for="job-location">Locatie</label>
            <input id="job-location" name="location" type="text" placeholder="Stad, regio of remote">
          </div>
          <div class="content-field">
            <label for="job-type">Dienstverband</label>
            <select id="job-type" name="type">
              <option>Fulltime</option>
              <option>Parttime</option>
              <option>Remote</option>
              <option>Freelance</option>
            </select>
          </div>
        </div>
        <div class="content-field">
          <label for="job-description">Korte omschrijving</label>
          <textarea id="job-description" name="description" placeholder="Beschrijf de functie in een paar zinnen."></textarea>
        </div>
        <div class="content-actions">
          <button class="content-btn content-btn--primary" type="submit">Werkgeversaccount aanmaken</button>
          <a class="content-btn content-btn--ghost" href="{{ route('pages.tarieven') }}">Bekijk tarieven</a>
        </div>
      </form>
    </section>
  </div>
</section>
@endsection

@push('styles')
  @include('pages.partials.page-styles')
@endpush
