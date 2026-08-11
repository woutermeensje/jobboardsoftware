@extends('layouts.app')

@section('title', 'Applications | JobBoardSoftware')
@section('meta_description', 'View applications for a tenant job board.')

@section('content')
<section class="dash-page">
  <div class="dash-shell dash-app">
    @include('dashboard.partials.navigation', ['activeTenant' => $tenant])

    <div class="dash-content">
    <header class="dash-topbar">
      <div>
        <p class="dash-eyebrow">{{ $tenant->name }}</p>
        <h1 class="dash-title">Applications</h1>
        <p class="dash-subtitle">All incoming applications from the tenant frontend.</p>
      </div>
      <aside class="dash-user">
        <strong>{{ $applications->count() }} applications</strong>
        <span>{{ $tenant->slug }}</span>
      </aside>
    </header>

    @if(session('status'))
      <section class="dash-card dash-card--success"><strong>{{ session('status') }}</strong></section>
    @endif

    <section class="dash-panel">
      <div class="dash-panel__head">
        <div>
          <h2>Applications</h2>
          <p>Candidates, job and follow-up status.</p>
        </div>
      </div>

      @if($applications->isEmpty())
        <div class="dash-empty">
          <h3>No applications yet</h3>
          <p>Applications will appear here as soon as candidates apply through the customer domain.</p>
        </div>
      @else
        <table class="dash-table">
          <thead>
            <tr>
              <th>Candidate</th>
              <th>Job</th>
              <th>Status</th>
              <th>Update</th>
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
