@extends('layouts.app')

@section('title', 'My account | '.$brandName)
@section('meta_description', 'Manage your account details.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('tenant.dashboards.partials.employer-navigation')
@endsection

@section('content')
    @if(session('status'))
      <section class="dash-card dash-card--success"><strong>{{ session('status') }}</strong></section>
    @endif

    <section class="dash-panel" aria-labelledby="account-title">
      <div class="dash-panel__head">
        <div>
          <h2 id="account-title">My account</h2>
          <p>Your account details.</p>
        </div>
      </div>

      <ul class="dash-list">
        <li>
          <div>
            <strong>{{ $user->name }}</strong>
            <span>{{ $user->email }}</span>
          </div>
          <span>Employer</span>
        </li>
      </ul>
    </section>
@endsection
