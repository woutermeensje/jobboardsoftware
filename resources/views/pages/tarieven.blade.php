@extends('layouts.app')

@section('title', 'Tarieven | JobBoardSoftware')
@section('meta_description', 'Bekijk de pakketten voor vacatureplaatsingen, groeiende platforms en maatwerk portals.')

@section('content')
<section class="content-page">
  <div class="content-page__shell">
    <header class="content-hero">
      <div class="content-hero__copy">
        <p class="content-eyebrow">Tarieven</p>
        <h1>Pakketten voor elk type vacatureplatform.</h1>
        <p class="content-hero__intro">Begin met losse plaatsingen of bouw door naar een schaalbare vacaturebank met werkgeversaccounts, beheer en rapportages.</p>
      </div>
      <aside class="content-visual" aria-label="Tarieven overzicht">
        <i class="ph ph-currency-eur"></i>
        <strong>Heldere pakketten</strong>
        <span>Voor starters, groeiende platforms en maatwerk portals.</span>
      </aside>
    </header>

    <div class="content-grid">
      <article class="content-card price-card">
        <i class="ph ph-seedling"></i>
        <h3>Starter</h3>
        <p>Voor niche vacaturebanken die snel willen starten met zichtbare plaatsingen.</p>
        <strong>Op aanvraag</strong>
      </article>
      <article class="content-card price-card">
        <i class="ph ph-chart-line-up"></i>
        <h3>Growth</h3>
        <p>Voor platforms die werkgevers, reacties en campagnes actiever willen beheren.</p>
        <strong>Op aanvraag</strong>
      </article>
      <article class="content-card price-card">
        <i class="ph ph-buildings"></i>
        <h3>Enterprise</h3>
        <p>Voor organisaties met maatwerk wensen, rollen, integraties en support.</p>
        <strong>Op aanvraag</strong>
      </article>
    </div>

    <section class="content-section" aria-labelledby="pricing-next-title">
      <h2 id="pricing-next-title">Welk pakket past bij je platform?</h2>
      <p>Plan een korte verkenning of maak direct een werkgeversaccount aan om de plaatsingsflow te starten.</p>
      <div class="content-actions">
        <a class="content-btn content-btn--primary" href="{{ route('pages.contact') }}">Contact opnemen</a>
        <a class="content-btn content-btn--ghost" href="{{ route('register.werkgever') }}">Account aanmaken</a>
      </div>
    </section>
  </div>
</section>
@endsection

@push('styles')
  @include('pages.partials.page-styles')
@endpush
