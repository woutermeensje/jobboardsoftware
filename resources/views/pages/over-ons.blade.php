@extends('layouts.app')

@section('title', 'Over Ons | JobBoardSoftware')
@section('meta_description', 'Lees meer over JobBoardSoftware en de visie achter de SaaS vacaturebank software.')

@section('content')
<section class="content-page">
  <div class="content-page__shell">
    <header class="content-hero">
      <div class="content-hero__copy">
        <p class="content-eyebrow">Over Ons</p>
        <h1>Gebouwd voor ondernemers die jobboard software willen verkopen of gebruiken.</h1>
        <p class="content-hero__intro">JobBoardSoftware groeit uit tot een SaaS-tool waarmee communities, bureaus en brancheplatforms zonder technische omwegen hun eigen job board kunnen lanceren.</p>
      </div>
      <aside class="content-visual" aria-label="Over JobBoardSoftware">
        <i class="ph ph-compass"></i>
        <strong>Van centrale app naar tenant platform</strong>
        <span>Een productbasis voor licenties, domeinen en jobboard frontends.</span>
      </aside>
    </header>

    <div class="content-grid">
      <article class="content-card">
        <i class="ph ph-users-three"></i>
        <h3>SaaS gebruikers</h3>
        <p>Klanten maken centraal een account aan om hun licentie, jobboard en domeinen te beheren.</p>
      </article>
      <article class="content-card">
        <i class="ph ph-shield-check"></i>
        <h3>Tenant controle</h3>
        <p>Elke klantomgeving kan een eigen domein krijgen, terwijl de centrale app de juiste tenant laadt.</p>
      </article>
      <article class="content-card">
        <i class="ph ph-puzzle-piece"></i>
        <h3>SaaS-ready</h3>
        <p>De structuur is voorbereid op pakketten, dashboards, domeinvalidatie en billing.</p>
      </article>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('pages.partials.page-styles')
@endpush
