@extends('layouts.tenant')

@section('title', ($tenant->name ?? 'Jobboard').' | Vacatures')
@section('meta_description', 'Vacatures, filters en sollicitaties voor dit jobboard.')

@php
  $settings = $tenant->settings ?? [];
  $brandName = $settings['brand_name'] ?? $tenant->name ?? 'Jobboard';
  $accent = $settings['accent_color'] ?? '#2f5f80';
  $intro = $settings['intro'] ?? 'Bekijk actuele vacatures en solliciteer direct.';
@endphp

@section('content')
<section class="tenant-page" style="--tenant-accent: {{ $accent }};">
  <header class="tenant-nav">
    <a class="tenant-brand" href="{{ route('tenant.home') }}">
      <span>{{ mb_substr($brandName, 0, 1) }}</span>
      <strong>{{ $brandName }}</strong>
    </a>
    <nav>
      <a href="{{ route('tenant.jobs') }}">Vacatures</a>
      <a href="{{ route('tenant.contact') }}">Contact</a>
    </nav>
  </header>

  <main class="tenant-shell">
    <section class="tenant-hero">
      <div>
        <p class="tenant-eyebrow">Vacatureplatform</p>
        <h1>{{ $brandName }}</h1>
        <p>{{ $intro }}</p>
        <div class="tenant-actions">
          <a class="tenant-btn tenant-btn--primary" href="#vacatures">Vacatures bekijken</a>
          <a class="tenant-btn tenant-btn--ghost" href="#contact">Contact</a>
        </div>
      </div>
      <aside class="tenant-card">
        <span>Openstaande functies</span>
        <strong>{{ $jobs->count() }}</strong>
        <p>Plan: {{ ucfirst($tenant->plan ?? 'starter') }}</p>
      </aside>
    </section>

    <section class="tenant-panel" id="vacatures" aria-labelledby="tenant-jobs-title">
      <div class="tenant-panel__head">
        <div>
          <p class="tenant-eyebrow">Vacatures</p>
          <h2 id="tenant-jobs-title">Openstaande functies</h2>
        </div>
      </div>

      <form class="tenant-filter" method="GET" action="{{ route('tenant.jobs') }}">
        <input name="search" value="{{ request('search') }}" placeholder="Zoek op titel, afdeling of locatie">
        <select name="department">
          <option value="">Alle afdelingen</option>
          @foreach($departments as $department)
            <option value="{{ $department }}" @selected(request('department') === $department)>{{ $department }}</option>
          @endforeach
        </select>
        <button type="submit">Zoeken</button>
      </form>

      <div class="tenant-jobs">
        @forelse($jobs as $job)
          <article class="tenant-job">
            <div>
              <h3>{{ $job->title }}</h3>
              <p>{{ $job->department }} - {{ $job->location }} - {{ $job->employment_type }}</p>
              @if($job->intro)
                <span>{{ $job->intro }}</span>
              @endif
            </div>
            <a href="{{ route('tenant.jobs.show', $job) }}">Bekijk vacature</a>
          </article>
        @empty
          <article class="tenant-job tenant-job--empty">
            <div>
              <h3>Geen vacatures gevonden</h3>
              <p>Pas je filters aan of kom later terug.</p>
            </div>
          </article>
        @endforelse
      </div>
    </section>

    <section class="tenant-panel" id="contact" aria-labelledby="tenant-contact-title">
      <p class="tenant-eyebrow">Contact</p>
      <h2 id="tenant-contact-title">Solliciteren of meer weten?</h2>
      <p>Reageer direct op een vacature of neem contact op met het recruitmentteam van {{ $brandName }}.</p>
    </section>
  </main>
</section>
@endsection

@push('styles')
  @include('tenant.partials.styles')
@endpush
