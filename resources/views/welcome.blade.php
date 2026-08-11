@extends('layouts.app')

@section('title', 'Job board software | JobBoardSoftware')
@section('meta_description', 'JobBoardSoftware is SaaS software waarmee ondernemers, recruiters en niche platforms hun eigen jobboard op een eigen domein kunnen beheren.')

@php
  $features = [
    [
      'icon' => 'ph-buildings',
      'title' => 'Meerdere jobboards',
      'text' => 'Maak per merk, niche of klant een eigen tenant omgeving aan met aparte domeinen.',
    ],
    [
      'icon' => 'ph-globe-hemisphere-west',
      'title' => 'Eigen domein',
      'text' => 'Koppel een domein of subdomein en toon daar de publieke vacaturefrontend.',
    ],
    [
      'icon' => 'ph-sliders-horizontal',
      'title' => 'Centraal beheer',
      'text' => 'Beheer licenties, instellingen en omgevingen vanuit een centrale SaaS portal.',
    ],
  ];

  $workflow = [
    'Maak een SaaS account aan',
    'Start je eerste jobboard omgeving',
    'Koppel je domeinnaam via DNS',
    'Publiceer de jobboard frontend op je eigen domein',
  ];

  $plans = [
    ['name' => 'Starter', 'price' => '49', 'summary' => 'Voor een niche jobboard of MVP.', 'items' => ['1 jobboard', 'Eigen domein', 'Basis beheeromgeving']],
    ['name' => 'Growth', 'price' => '149', 'summary' => 'Voor groeiende communities en bureaus.', 'items' => ['3 jobboards', 'Uitgebreide styling', 'Prioriteit support']],
    ['name' => 'Platform', 'price' => 'Op maat', 'summary' => 'Voor meerdere labels of white label software.', 'items' => ['Onbeperkte tenants', 'Maatwerk integraties', 'Dedicated onboarding']],
  ];
@endphp

@section('content')
<section class="saas-hero" id="product">
  <div class="saas-shell saas-hero__grid">
    <div class="saas-hero__copy">
      <p class="saas-eyebrow">SaaS job board software</p>
      <h1>Job board software voor je eigen vacatureplatform</h1>
      <p class="saas-lead">Verkoop, beheer en publiceer vacaturebanken voor klanten, communities of nichemarkten. De centrale app is je beheeromgeving; de vacaturefrontend draait op het domein van de klant.</p>
      <div class="saas-actions">
        <a class="saas-btn saas-btn--primary" href="{{ route('register.choice') }}">
          <i class="ph ph-rocket-launch"></i>
          Start gratis
        </a>
        <a class="saas-btn saas-btn--ghost" href="{{ route('login.choice') }}">
          <i class="ph ph-sign-in"></i>
          Inloggen
        </a>
      </div>
      <dl class="saas-hero__facts">
        <div>
          <dt>Tenant ready</dt>
          <dd>Elke klant een eigen omgeving</dd>
        </div>
        <div>
          <dt>Custom domain</dt>
          <dd>CNAME naar je SaaS platform</dd>
        </div>
      </dl>
    </div>

    <div class="saas-product-visual" aria-label="Voorbeeld van de SaaS beheeromgeving">
      <div class="saas-product-visual__top">
        <span></span>
        <span></span>
        <span></span>
      </div>
      <div class="saas-product-visual__body">
        <aside class="saas-product-visual__menu">
          <strong>JobBoardSoftware</strong>
          <span class="is-active">Dashboard</span>
          <span>Omgevingen</span>
          <span>Domeinen</span>
          <span>Licentie</span>
        </aside>
        <main class="saas-product-visual__main">
          <div class="saas-product-visual__header">
            <div>
              <span class="saas-kicker">Acme Careers</span>
              <strong>Tenant omgeving</strong>
            </div>
            <span class="saas-status">Trial</span>
          </div>
          <div class="saas-product-visual__stats">
            <span>1 jobboard</span>
            <span>2 domeinen</span>
            <span>SSL pending</span>
          </div>
          <div class="saas-domain-row">
            <i class="ph ph-globe"></i>
            <div>
              <strong>vacatures.acme.nl</strong>
              <span>CNAME naar cname.jobboardsoftware.co</span>
            </div>
          </div>
          <div class="saas-board-preview">
            <span>Publieke frontend</span>
            <strong>Openstaande functies</strong>
            <div class="saas-job-line"></div>
            <div class="saas-job-line saas-job-line--short"></div>
          </div>
        </main>
      </div>
    </div>
  </div>
</section>

<section class="saas-section" id="features">
  <div class="saas-shell">
    <div class="saas-section__head">
      <p class="saas-eyebrow">Wat je verkoopt</p>
      <h2>Een complete jobboard basis, beheerd vanuit een centrale portal</h2>
      <p>Klanten kopen een licentie bij jou, koppelen hun domein en krijgen hun eigen vacaturefrontend. Jij houdt controle over tenants, domeinen en plannen.</p>
    </div>

    <div class="saas-feature-grid">
      @foreach($features as $feature)
        <article class="saas-feature-card">
          <i class="ph {{ $feature['icon'] }}"></i>
          <h3>{{ $feature['title'] }}</h3>
          <p>{{ $feature['text'] }}</p>
        </article>
      @endforeach
    </div>
  </div>
</section>

<section class="saas-section saas-section--split" id="beheer">
  <div class="saas-shell saas-split">
    <div>
      <p class="saas-eyebrow">Beheeromgeving</p>
      <h2>Gebruikers loggen in om hun jobboard software te beheren</h2>
      <p>De centrale website is geen vacaturebank meer. Dit is de SaaS laag waar klanten hun account aanmaken, een licentie starten en hun jobboard configureren.</p>
      <a class="saas-link" href="{{ route('register.choice') }}">Account aanmaken <i class="ph ph-arrow-right"></i></a>
    </div>

    <ol class="saas-workflow">
      @foreach($workflow as $step)
        <li>
          <span>{{ $loop->iteration }}</span>
          <strong>{{ $step }}</strong>
        </li>
      @endforeach
    </ol>
  </div>
</section>

<section class="saas-section" id="domeinen">
  <div class="saas-shell saas-domain-panel">
    <div>
      <p class="saas-eyebrow">Klantdomeinen</p>
      <h2>De jobboard frontend verschijnt op het domein van de klant</h2>
      <p>Wanneer een klant een domein koppelt, wijst DNS naar de centrale applicatie. De tenancy laag bepaalt vervolgens welke tenant en welke jobboard frontend geladen moet worden.</p>
    </div>
    <div class="saas-dns-table" aria-label="DNS voorbeeld">
      <div>
        <span>Type</span>
        <strong>CNAME</strong>
      </div>
      <div>
        <span>Naam</span>
        <strong>vacatures.klant.nl</strong>
      </div>
      <div>
        <span>Waarde</span>
        <strong>cname.jobboardsoftware.co</strong>
      </div>
    </div>
  </div>
</section>

<section class="saas-section" id="pricing">
  <div class="saas-shell">
    <div class="saas-section__head">
      <p class="saas-eyebrow">Tarieven</p>
      <h2>Start klein en schaal door naar meerdere jobboards</h2>
      <p>De betaalflow kan hierna worden gekoppeld aan Laravel Cashier/Stripe. De basis van accounts, tenants en domeinen staat klaar.</p>
    </div>

    <div class="saas-pricing-grid">
      @foreach($plans as $plan)
        <article class="saas-plan">
          <h3>{{ $plan['name'] }}</h3>
          <p>{{ $plan['summary'] }}</p>
          <strong class="saas-plan__price">{{ is_numeric($plan['price']) ? 'EUR '.$plan['price'].' / mnd' : $plan['price'] }}</strong>
          <ul>
            @foreach($plan['items'] as $item)
              <li><i class="ph ph-check"></i>{{ $item }}</li>
            @endforeach
          </ul>
        </article>
      @endforeach
    </div>
  </div>
</section>

<section class="saas-cta" id="jobboard">
  <div class="saas-shell saas-cta__inner">
    <div>
      <p class="saas-eyebrow">Klaar om te starten?</p>
      <h2>Maak een account aan en bouw je eerste jobboard omgeving.</h2>
    </div>
    <div class="saas-actions">
      <a class="saas-btn saas-btn--primary" href="{{ route('register.choice') }}">Account aanmaken</a>
      <a class="saas-btn saas-btn--ghost" href="{{ route('pages.contact') }}">Demo plannen</a>
    </div>
  </div>
</section>
@endsection

@push('styles')
<style>
.saas-shell {
  width: min(100% - 48px, 1240px);
  margin: 0 auto;
}

.saas-hero {
  padding: 74px 0 42px;
}

.saas-hero__grid {
  display: grid;
  grid-template-columns: minmax(0, 0.95fr) minmax(420px, 1.05fr);
  gap: 56px;
  align-items: center;
}

.saas-eyebrow {
  margin: 0 0 12px;
  color: var(--color-accent-strong);
  font-family: var(--font-ui);
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.saas-hero h1,
.saas-section h2,
.saas-cta h2 {
  margin: 0;
  color: var(--color-text);
  letter-spacing: 0;
}

.saas-hero h1 {
  max-width: 760px;
  font-size: clamp(42px, 6vw, 72px);
  line-height: 0.98;
}

.saas-lead {
  max-width: 680px;
  margin: 22px 0 0;
  color: var(--color-text-muted);
  font-size: 19px;
  line-height: 1.7;
}

.saas-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-top: 28px;
}

.saas-btn {
  min-height: 48px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 0 18px;
  border-radius: 8px;
  border: 1px solid transparent;
  font-family: var(--font-ui);
  font-size: 14px;
  font-weight: 800;
  text-decoration: none;
}

.saas-btn:hover {
  text-decoration: none;
}

.saas-btn--primary {
  background: var(--color-primary-strong);
  color: #ffffff;
  box-shadow: 0 12px 22px rgba(47, 95, 128, 0.18);
}

.saas-btn--ghost {
  background: #ffffff;
  border-color: var(--color-border-strong);
  color: var(--color-primary-strong);
}

.saas-hero__facts {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  max-width: 560px;
  margin: 34px 0 0;
}

.saas-hero__facts div {
  padding: 16px;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.72);
}

.saas-hero__facts dt {
  color: var(--color-text);
  font-family: var(--font-ui);
  font-size: 14px;
  font-weight: 800;
}

.saas-hero__facts dd {
  margin: 4px 0 0;
  color: var(--color-text-muted);
  font-size: 13px;
}

.saas-product-visual {
  overflow: hidden;
  border: 1px solid var(--color-border-strong);
  border-radius: 8px;
  background: #ffffff;
  box-shadow: var(--shadow-md);
}

.saas-product-visual__top {
  height: 42px;
  display: flex;
  align-items: center;
  gap: 7px;
  padding: 0 16px;
  border-bottom: 1px solid var(--color-border);
  background: #f9fbfc;
}

.saas-product-visual__top span {
  width: 10px;
  height: 10px;
  border-radius: 999px;
  background: var(--color-border-strong);
}

.saas-product-visual__body {
  display: grid;
  grid-template-columns: 170px minmax(0, 1fr);
  min-height: 390px;
}

.saas-product-visual__menu {
  display: grid;
  align-content: start;
  gap: 8px;
  padding: 20px;
  border-right: 1px solid var(--color-border);
  background: #f4f8fa;
}

.saas-product-visual__menu strong {
  margin-bottom: 12px;
  color: var(--color-primary-strong);
  font-family: var(--font-ui);
  font-size: 13px;
}

.saas-product-visual__menu span {
  padding: 9px 10px;
  border-radius: 8px;
  color: var(--color-text-muted);
  font-size: 13px;
}

.saas-product-visual__menu .is-active {
  background: #ffffff;
  color: var(--color-primary-strong);
  font-weight: 800;
}

.saas-product-visual__main {
  display: grid;
  align-content: start;
  gap: 16px;
  padding: 22px;
}

.saas-product-visual__header,
.saas-domain-row,
.saas-board-preview {
  border: 1px solid var(--color-border);
  border-radius: 8px;
  background: #ffffff;
}

.saas-product-visual__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 18px;
}

.saas-product-visual__header strong,
.saas-domain-row strong,
.saas-board-preview strong {
  display: block;
  color: var(--color-text);
  font-family: var(--font-ui);
}

.saas-kicker,
.saas-domain-row span,
.saas-board-preview span {
  color: var(--color-text-muted);
  font-size: 13px;
}

.saas-status {
  padding: 6px 10px;
  border-radius: 999px;
  background: var(--color-accent-soft);
  color: var(--color-accent-strong);
  font-family: var(--font-ui);
  font-size: 12px;
  font-weight: 800;
}

.saas-product-visual__stats {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
}

.saas-product-visual__stats span {
  min-height: 64px;
  display: grid;
  place-items: center;
  padding: 12px;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  color: var(--color-text-muted);
  font-family: var(--font-ui);
  font-size: 13px;
  font-weight: 700;
  text-align: center;
}

.saas-domain-row {
  display: flex;
  gap: 12px;
  padding: 16px;
}

.saas-domain-row i {
  color: var(--color-primary-strong);
  font-size: 24px;
}

.saas-board-preview {
  display: grid;
  gap: 10px;
  padding: 16px;
  background: #fbfdff;
}

.saas-job-line {
  height: 12px;
  border-radius: 999px;
  background: var(--color-border);
}

.saas-job-line--short {
  width: 66%;
}

.saas-section {
  padding: 64px 0;
}

.saas-section__head {
  max-width: 780px;
  margin-bottom: 28px;
}

.saas-section__head h2,
.saas-section--split h2,
.saas-domain-panel h2,
.saas-cta h2 {
  font-size: clamp(30px, 4vw, 46px);
  line-height: 1.08;
}

.saas-section__head p,
.saas-section--split p,
.saas-domain-panel p {
  margin: 14px 0 0;
  color: var(--color-text-muted);
  font-size: 17px;
}

.saas-feature-grid,
.saas-pricing-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 18px;
}

.saas-feature-card,
.saas-plan {
  padding: 24px;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  background: #ffffff;
}

.saas-feature-card i {
  width: 42px;
  height: 42px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  background: var(--color-primary-soft);
  color: var(--color-primary-strong);
  font-size: 24px;
}

.saas-feature-card h3,
.saas-plan h3 {
  margin: 16px 0 8px;
  color: var(--color-text);
  font-size: 22px;
}

.saas-feature-card p,
.saas-plan p {
  margin: 0;
  color: var(--color-text-muted);
}

.saas-section--split {
  background: #ffffff;
  border-block: 1px solid var(--color-border);
}

.saas-split,
.saas-domain-panel,
.saas-cta__inner {
  display: grid;
  grid-template-columns: minmax(0, 0.9fr) minmax(360px, 1fr);
  gap: 36px;
  align-items: center;
}

.saas-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-top: 22px;
  color: var(--color-primary-strong);
  font-family: var(--font-ui);
  font-weight: 800;
}

.saas-workflow {
  display: grid;
  gap: 12px;
  margin: 0;
  padding: 0;
  list-style: none;
}

.saas-workflow li {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  background: #fbfdff;
}

.saas-workflow span {
  width: 34px;
  height: 34px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  background: var(--color-primary-strong);
  color: #ffffff;
  font-family: var(--font-ui);
  font-size: 13px;
  font-weight: 800;
}

.saas-workflow strong {
  color: var(--color-text);
  font-family: var(--font-ui);
}

.saas-domain-panel {
  padding: 30px;
  border: 1px solid var(--color-border-strong);
  border-radius: 8px;
  background: #ffffff;
}

.saas-dns-table {
  display: grid;
  gap: 10px;
}

.saas-dns-table div {
  display: grid;
  grid-template-columns: 90px minmax(0, 1fr);
  gap: 14px;
  padding: 14px 16px;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  background: #fbfdff;
}

.saas-dns-table span {
  color: var(--color-text-muted);
  font-size: 13px;
}

.saas-dns-table strong {
  color: var(--color-primary-strong);
  font-family: var(--font-ui);
  overflow-wrap: anywhere;
}

.saas-plan__price {
  display: block;
  margin-top: 18px;
  color: var(--color-primary-strong);
  font-family: var(--font-ui);
  font-size: 25px;
}

.saas-plan ul {
  display: grid;
  gap: 9px;
  margin: 18px 0 0;
  padding: 0;
  list-style: none;
}

.saas-plan li {
  display: flex;
  gap: 8px;
  color: var(--color-text-muted);
  font-size: 14px;
}

.saas-plan li i {
  color: var(--color-primary-strong);
  font-size: 18px;
}

.saas-cta {
  padding: 64px 0 78px;
}

.saas-cta__inner {
  padding: 32px;
  border: 1px solid var(--color-border-strong);
  border-radius: 8px;
  background: #ffffff;
}

.saas-cta .saas-actions {
  justify-content: flex-end;
  margin-top: 0;
}

@media (max-width: 980px) {
  .saas-hero__grid,
  .saas-split,
  .saas-domain-panel,
  .saas-cta__inner {
    grid-template-columns: 1fr;
  }

  .saas-product-visual__body {
    grid-template-columns: 1fr;
  }

  .saas-product-visual__menu {
    grid-template-columns: repeat(4, minmax(0, 1fr));
    border-right: 0;
    border-bottom: 1px solid var(--color-border);
  }

  .saas-product-visual__menu strong {
    grid-column: 1 / -1;
  }

  .saas-feature-grid,
  .saas-pricing-grid {
    grid-template-columns: 1fr;
  }

  .saas-cta .saas-actions {
    justify-content: flex-start;
  }
}

@media (max-width: 640px) {
  .saas-shell {
    width: min(100% - 32px, 1240px);
  }

  .saas-hero {
    padding-top: 48px;
  }

  .saas-hero h1 {
    font-size: 42px;
  }

  .saas-hero__facts,
  .saas-product-visual__stats,
  .saas-product-visual__menu {
    grid-template-columns: 1fr;
  }

  .saas-actions,
  .saas-btn {
    width: 100%;
  }

  .saas-dns-table div {
    grid-template-columns: 1fr;
  }
}
</style>
@endpush
