@extends('layouts.app')

@section('title', 'My company page | '.$brandName)
@section('meta_description', 'Manage your company profile.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('tenant.dashboards.partials.employer-navigation')
@endsection

@section('content')
    @if(session('status'))
      <section class="dash-card dash-card--success"><strong>{{ session('status') }}</strong></section>
    @endif

    <section class="dash-panel" aria-labelledby="company-title">
      <div class="dash-panel__head">
        <div>
          <h2 id="company-title">Company profile</h2>
          <p>Your employer account is connected to {{ $brandName }}.</p>
        </div>
      </div>

      <ul class="dash-list">
        <li>
          <div>
            <strong>{{ $user->company_name ?: $user->name }}</strong>
            <span>{{ $user->email }}</span>
          </div>
          <span>Employer</span>
        </li>
      </ul>
    </section>
@endsection
