@extends('layouts.app')

@section('title', 'Tarieven | JobBoardSoftware')
@section('meta_description', 'Bekijk de SaaS pakketten voor job board software, eigen domeinen en tenant beheer.')

@section('content')
<section class="content-page">
  <div class="content-page__shell">
    <header class="content-hero">
      <div class="content-hero__copy">
        <p class="content-eyebrow">Tarieven</p>
        <h1>SaaS pakketten voor elk type jobboard.</h1>
        <p class="content-hero__intro">Start met een eigen vacatureplatform op klantdomein en schaal door naar meerdere tenants, domeinen en licenties.</p>
      </div>
      <aside class="content-visual" aria-label="Tarieven overzicht">
        <i class="ph ph-currency-eur"></i>
        <strong>Heldere pakketten</strong>
        <span>Voor starters, groeiende platforms en white label software.</span>
      </aside>
    </header>

    <div class="content-grid">
      <article class="content-card price-card">
        <i class="ph ph-seedling"></i>
        <h3>Starter</h3>
        <p>Voor een niche jobboard of MVP met een eigen domein.</p>
        <strong>EUR 49 / mnd</strong>
      </article>
      <article class="content-card price-card">
        <i class="ph ph-chart-line-up"></i>
        <h3>Growth</h3>
        <p>Voor bureaus en communities die meerdere jobboards willen beheren.</p>
        <strong>EUR 149 / mnd</strong>
      </article>
      <article class="content-card price-card">
        <i class="ph ph-buildings"></i>
        <h3>Platform</h3>
        <p>Voor white label software, maatwerk integraties en grotere volumes.</p>
        <strong>Op maat</strong>
      </article>
    </div>

    <section class="content-section" aria-labelledby="pricing-next-title">
      <h2 id="pricing-next-title">Welk pakket past bij je SaaS gebruik?</h2>
      <p>Plan een korte verkenning of maak direct een SaaS account aan om je eerste jobboard omgeving te starten.</p>
      <div class="content-actions">
        <a class="content-btn content-btn--primary" href="{{ route('pages.contact') }}">Contact opnemen</a>
        <a class="content-btn content-btn--ghost" href="{{ route('register.choice') }}">Account aanmaken</a>
      </div>
    </section>
  </div>
</section>
@endsection

@push('styles')
  @include('pages.partials.page-styles')
@endpush
