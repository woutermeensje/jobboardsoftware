@extends('layouts.app')

@section('title', 'Sollicitaties | JobBoardSoftware')
@section('meta_description', 'Bekijk sollicitaties voor een tenant jobboard.')

@section('content')
<section class="dash-page">
  <div class="dash-shell">
    <header class="dash-topbar">
      <div>
        <p class="dash-eyebrow">{{ $tenant->name }}</p>
        <h1 class="dash-title">Sollicitaties</h1>
        <p class="dash-subtitle">Alle binnengekomen reacties via de tenant frontend.</p>
      </div>
      <aside class="dash-user">
        <strong>{{ $applications->count() }} reacties</strong>
        <span>{{ $tenant->slug }}</span>
      </aside>
    </header>

    @if(session('status'))
      <section class="dash-card dash-card--success"><strong>{{ session('status') }}</strong></section>
    @endif

    <section class="dash-panel">
      <div class="dash-panel__head">
        <div>
          <h2>Reacties</h2>
          <p>Kandidaten, vacature en opvolgstatus.</p>
        </div>
      </div>

      @if($applications->isEmpty())
        <div class="dash-empty">
          <h3>Nog geen sollicitaties</h3>
          <p>Sollicitaties verschijnen hier zodra kandidaten reageren via het klantdomein.</p>
        </div>
      @else
        <table class="dash-table">
          <thead>
            <tr>
              <th>Kandidaat</th>
              <th>Vacature</th>
              <th>Status</th>
              <th>Bijwerken</th>
            </tr>
          </thead>
          <tbody>
            @foreach($applications as $application)
              <tr>
                <td>
                  <span class="dash-cell-title">{{ $application->name }}</span>
                  <span class="dash-cell-meta">{{ $application->email }}</span>
                </td>
                <td>{{ $application->job?->title }}</td>
                <td><span class="dash-status dash-status--accent">{{ ucfirst($application->status) }}</span></td>
                <td>
                  <form method="POST" action="{{ route('tenant.applications.update', [$tenant, $application]) }}">
                    @csrf
                    @method('PATCH')
                    <select class="form-control" name="status" onchange="this.form.submit()">
                      @foreach([\App\Models\JobApplication::STATUS_NEW, \App\Models\JobApplication::STATUS_REVIEWED, \App\Models\JobApplication::STATUS_REJECTED, \App\Models\JobApplication::STATUS_HIRED] as $status)
                        <option value="{{ $status }}" @selected($application->status === $status)>{{ ucfirst($status) }}</option>
                      @endforeach
                    </select>
                  </form>
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
