@extends('layouts.app')

@section('title', ($tenant->name ?? 'Jobboard').' | Vacatures')
@section('meta_description', 'Tenant job board frontend voor gekoppelde klantdomeinen.')

@php
  $settings = $tenant->settings ?? [];
  $brandName = $settings['brand_name'] ?? $tenant->name ?? 'Jobboard';
  $accent = $settings['accent_color'] ?? '#2f5f80';

  $jobs = [
    ['title' => 'Laravel Developer', 'location' => 'Amsterdam', 'type' => 'Fulltime', 'department' => 'Development'],
    ['title' => 'Recruitment Marketeer', 'location' => 'Rotterdam', 'type' => 'Parttime', 'department' => 'Marketing'],
    ['title' => 'Customer Success Manager', 'location' => 'Utrecht', 'type' => 'Hybrid', 'department' => 'Customer Success'],
  ];
@endphp

@section('content')
<section class="tenant-page" style="--tenant-accent: {{ $accent }};">
  <div class="tenant-shell">
    <header class="tenant-hero">
      <div>
        <p class="tenant-eyebrow">Tenant job board</p>
        <h1>{{ $brandName }}</h1>
        <p>Bekijk actuele vacatures, leer het team kennen en reageer direct via het gekoppelde job board van {{ $brandName }}.</p>
        <div class="tenant-actions">
          <a class="tenant-btn tenant-btn--primary" href="{{ route('tenant.jobs') }}">Vacatures bekijken</a>
          <a class="tenant-btn tenant-btn--ghost" href="{{ route('tenant.contact') }}">Contact</a>
        </div>
      </div>
      <aside class="tenant-card">
        <span>Status</span>
        <strong>{{ ucfirst($tenant->status ?? 'trial') }}</strong>
        <p>Plan: {{ ucfirst($tenant->plan ?? 'starter') }}</p>
      </aside>
    </header>

    <section class="tenant-panel" aria-labelledby="tenant-jobs-title">
      <div class="tenant-panel__head">
        <div>
          <p class="tenant-eyebrow">Vacatures</p>
          <h2 id="tenant-jobs-title">Openstaande functies</h2>
        </div>
      </div>

      <div class="tenant-jobs">
        @foreach($jobs as $job)
          <article class="tenant-job">
            <div>
              <h3>{{ $job['title'] }}</h3>
              <p>{{ $job['department'] }} - {{ $job['location'] }} - {{ $job['type'] }}</p>
            </div>
            <a href="{{ route('tenant.contact') }}">Reageren</a>
          </article>
        @endforeach
      </div>
    </section>

    <section class="tenant-panel" id="tenant-contact" aria-labelledby="tenant-contact-title">
      <p class="tenant-eyebrow">Contact</p>
      <h2 id="tenant-contact-title">Solliciteren of meer weten?</h2>
      <p>Stuur je gegevens mee bij je reactie, dan neemt het recruitmentteam contact met je op.</p>
    </section>
  </div>
</section>
@endsection

@push('styles')
<style>
.tenant-page {
  background: var(--color-bg);
  padding: 48px 24px 72px;
}

.tenant-shell {
  width: min(1120px, 100%);
  margin: 0 auto;
  display: grid;
  gap: 20px;
}

.tenant-hero {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 280px;
  gap: 24px;
  align-items: stretch;
}

.tenant-eyebrow {
  margin: 0 0 8px;
  color: var(--tenant-accent);
  font-family: var(--font-ui);
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.tenant-hero h1,
.tenant-panel h2 {
  margin: 0;
  font-weight: 800;
}

.tenant-hero h1 {
  font-size: clamp(34px, 4vw, 54px);
}

.tenant-hero p,
.tenant-panel p,
.tenant-job p {
  color: var(--color-text-muted);
}

.tenant-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 22px;
}

.tenant-btn {
  display: inline-flex;
  min-height: 42px;
  align-items: center;
  justify-content: center;
  padding: 0 16px;
  border: 1px solid transparent;
  border-radius: 6px;
  font-family: var(--font-ui);
  font-weight: 800;
  text-decoration: none;
}

.tenant-btn:hover {
  text-decoration: none;
}

.tenant-btn--primary {
  background: var(--tenant-accent);
  color: #ffffff;
}

.tenant-btn--ghost {
  border-color: var(--color-border-strong);
  background: #ffffff;
  color: var(--tenant-accent);
}

.tenant-card,
.tenant-panel {
  border: 1px solid var(--color-border);
  border-radius: 8px;
  background: #ffffff;
  box-shadow: var(--shadow-sm);
}

.tenant-card {
  display: grid;
  align-content: center;
  padding: 22px;
}

.tenant-card span {
  color: var(--color-text-muted);
  font-size: 13px;
}

.tenant-card strong {
  display: block;
  margin-top: 4px;
  font-family: var(--font-ui);
  font-size: 30px;
}

.tenant-panel {
  padding: 26px;
}

.tenant-panel__head {
  margin-bottom: 16px;
}

.tenant-jobs {
  display: grid;
  gap: 12px;
}

.tenant-job {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 18px;
  padding: 18px;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  background: #fbfdff;
}

.tenant-job h3,
.tenant-job p {
  margin: 0;
}

.tenant-job h3 {
  font-size: 18px;
  font-weight: 800;
}

.tenant-job a {
  color: var(--tenant-accent);
  font-family: var(--font-ui);
  font-weight: 800;
  white-space: nowrap;
}

@media (max-width: 820px) {
  .tenant-hero,
  .tenant-job {
    grid-template-columns: 1fr;
  }

  .tenant-hero,
  .tenant-job {
    display: grid;
  }
}

@media (max-width: 620px) {
  .tenant-page {
    padding: 36px 18px 56px;
  }

  .tenant-panel,
  .tenant-card {
    padding: 22px;
  }
}
</style>
@endpush
