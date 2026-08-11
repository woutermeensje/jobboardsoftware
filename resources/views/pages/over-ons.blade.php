@extends('layouts.app')

@section('title', 'Over Ons | JobBoardSoftware')
@section('meta_description', 'Lees meer over JobBoardSoftware en de visie achter de SaaS vacaturebank software.')

@section('content')
<section class="content-page">
  <div class="content-page__shell">
    <header class="content-hero">
      <div class="content-hero__copy">
        <p class="content-eyebrow">Over Ons</p>
        <h1>Gebouwd voor ondernemers die hun eigen vacaturemarkt willen beheren.</h1>
        <p class="content-hero__intro">JobBoardSoftware groeit uit tot een SaaS-tool waarmee communities, bureaus en brancheplatforms zonder technische omwegen hun eigen job board kunnen lanceren.</p>
      </div>
      <aside class="content-visual" aria-label="Over JobBoardSoftware">
        <i class="ph ph-compass"></i>
        <strong>Van vacaturebank naar platform</strong>
        <span>Een productbasis voor vraag, aanbod, werkgevers en beheer.</span>
      </aside>
    </header>

    <div class="content-grid">
      <article class="content-card">
        <i class="ph ph-users-three"></i>
        <h3>Twee doelgroepen</h3>
        <p>Werkzoekenden zoeken eenvoudig door vacatures, terwijl werkgevers hun aanbod zelfstandig beheren.</p>
      </article>
      <article class="content-card">
        <i class="ph ph-shield-check"></i>
        <h3>Admin controle</h3>
        <p>Admins krijgen grip op gebruikers, vacatures, zichtbaarheid en platforminstellingen.</p>
      </article>
      <article class="content-card">
        <i class="ph ph-puzzle-piece"></i>
        <h3>SaaS-ready</h3>
        <p>De structuur is voorbereid op pakketten, dashboards en uitbreidingen per doelgroep.</p>
      </article>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('pages.partials.page-styles')
@endpush
