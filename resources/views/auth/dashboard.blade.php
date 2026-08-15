@extends($layout ?? 'layouts.app')

@php
  $brandTitle = $brandName ?? 'JobBoardSoftware';
@endphp

@section('title', 'Dashboard | '.$brandTitle)

@php
  $roleLabels = [
    \App\Models\User::ROLE_WERKZOEKENDE => 'Job seeker',
    \App\Models\User::ROLE_WERKGEVER => 'Employer',
    \App\Models\User::ROLE_TENANT_OWNER => 'SaaS user',
    \App\Models\User::ROLE_ADMIN => 'Admin',
  ];

  $roleTitle = $roleLabels[$user->role] ?? ucfirst($user->role);
  $backUrl = $backUrl ?? route('welcome');
  $backLabel = $backLabel ?? 'View website';
  $logoutUrl = $logoutUrl ?? route('logout');
@endphp

@section('content')
<section class="auth-page">
  <div class="auth-shell">
    <div class="auth-head">
      <p class="auth-eyebrow">{{ $roleTitle }}</p>
      <h1>Welcome, {{ $user->name }}</h1>
      <p>This is the first version of your {{ strtolower($roleTitle) }} portal. The functional modules can be expanded from here.</p>
    </div>

    <div class="auth-dashboard-card">
      <h2>{{ $roleTitle }} dashboard</h2>
      <div class="auth-dashboard-meta">
        <span>{{ $user->email }}</span>
        <span>{{ $roleTitle }}</span>
        @if($user->company_name)
          <span>{{ $user->company_name }}</span>
        @endif
      </div>

      <div class="auth-dashboard-actions">
        <a class="btn btn-primary" href="{{ $backUrl }}">{{ $backLabel }}</a>
        <form method="POST" action="{{ $logoutUrl }}">
          @csrf
          <button class="btn btn-ghost" type="submit">Log out</button>
        </form>
      </div>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('auth.partials.styles')
@endpush
