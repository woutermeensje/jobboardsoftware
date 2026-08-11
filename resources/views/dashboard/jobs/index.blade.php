@extends('layouts.app')

@section('title', 'Vacatures beheren | JobBoardSoftware')
@section('meta_description', 'Beheer vacatures voor een tenant jobboard.')

@section('content')
<section class="dash-page">
  <div class="dash-shell">
    <header class="dash-topbar">
      <div>
        <p class="dash-eyebrow">{{ $tenant->name }}</p>
        <h1 class="dash-title">Vacatures beheren</h1>
        <p class="dash-subtitle">Maak vacatures aan, publiceer ze op het klantdomein en volg reacties op.</p>
      </div>
      <aside class="dash-user">
        <strong>{{ $jobs->count() }} vacatures</strong>
        <span>{{ $tenant->slug }}</span>
        <span>{{ ucfirst($tenant->status) }}</span>
      </aside>
    </header>

    @if(session('status'))
      <section class="dash-card dash-card--success"><strong>{{ session('status') }}</strong></section>
    @endif

    <section class="dash-panel">
      <div class="dash-panel__head">
        <div>
          <h2>Vacatures</h2>
          <p>Concepten en gepubliceerde vacatures voor deze omgeving.</p>
        </div>
        <a class="dash-btn dash-btn--primary" href="{{ route('tenant.jobs.create', $tenant) }}">
          <i class="ph ph-plus"></i>
          Nieuwe vacature
        </a>
      </div>

      @if($jobs->isEmpty())
        <div class="dash-empty">
          <h3>Nog geen vacatures</h3>
          <p>Maak je eerste vacature aan om de tenant frontend te vullen.</p>
          <a class="dash-btn dash-btn--primary" href="{{ route('tenant.jobs.create', $tenant) }}">Vacature aanmaken</a>
        </div>
      @else
        <table class="dash-table">
          <thead>
            <tr>
              <th>Vacature</th>
              <th>Status</th>
              <th>Sollicitaties</th>
              <th>Actie</th>
            </tr>
          </thead>
          <tbody>
            @foreach($jobs as $job)
              <tr>
                <td>
                  <span class="dash-cell-title">{{ $job->title }}</span>
                  <span class="dash-cell-meta">{{ $job->department }} - {{ $job->location }} - {{ $job->employment_type }}</span>
                </td>
                <td><span class="dash-status {{ $job->status !== \App\Models\TenantJob::STATUS_PUBLISHED ? 'dash-status--accent' : '' }}">{{ ucfirst($job->status) }}</span></td>
                <td>{{ $job->applications_count }}</td>
                <td>
                  <a class="dash-btn dash-btn--ghost" href="{{ route('tenant.jobs.edit', [$tenant, $job]) }}">Bewerken</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </section>
  </div>
</section>
@endsection

@push('styles')
  @include('dashboard.partials.styles')
  <style>
    .dash-card--success {
      border-color: var(--color-primary-muted);
      background: var(--color-primary-soft);
      color: var(--color-primary-strong);
    }
  </style>
@endpush
