@extends('layouts.app')

@section('title', 'Dashboard | JobBoardSoftware')

@php
  $roleLabels = [
    \App\Models\User::ROLE_WERKZOEKENDE => 'Werkzoekende',
    \App\Models\User::ROLE_WERKGEVER => 'Werkgever',
    \App\Models\User::ROLE_ADMIN => 'Admin',
  ];

  $roleTitle = $roleLabels[$user->role] ?? ucfirst($user->role);
@endphp

@section('content')
<section class="auth-page">
  <div class="auth-shell">
    <div class="auth-head">
      <p class="auth-eyebrow">{{ $roleTitle }}</p>
      <h1>Welkom, {{ $user->name }}</h1>
      <p>Dit is de eerste versie van je {{ strtolower($roleTitle) }} omgeving. De functionele modules kunnen hierna per rol worden uitgebreid.</p>
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
        <a class="btn btn-primary" href="{{ route('welcome') }}">Vacaturebank bekijken</a>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button class="btn btn-ghost" type="submit">Uitloggen</button>
        </form>
      </div>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('auth.partials.styles')
@endpush
