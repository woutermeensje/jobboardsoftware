@extends('layouts.app')

@section('title', 'Inloggen | JobBoardSoftware')

@section('content')
<section class="auth-page">
  <div class="auth-shell">
    <div class="auth-head">
      <p class="auth-eyebrow">Inloggen</p>
      <h1>Kies je omgeving</h1>
      <p>Log in als werkzoekende, werkgever of admin. Elke rol krijgt een eigen dashboard en eigen toegang.</p>
    </div>

    <div class="auth-choice-grid">
      <a class="auth-choice-card" href="{{ route('login.werkzoekende') }}">
        <i class="ph ph-user-circle"></i>
        <h2>Werkzoekende</h2>
        <p>Voor kandidaten die vacatures willen bewaren en straks sollicitaties beheren.</p>
      </a>

      <a class="auth-choice-card" href="{{ route('login.werkgever') }}">
        <i class="ph ph-buildings"></i>
        <h2>Werkgever</h2>
        <p>Voor bedrijven die vacatures plaatsen en kandidaten opvolgen.</p>
      </a>

      <a class="auth-choice-card" href="{{ route('admin.login') }}">
        <i class="ph ph-shield-check"></i>
        <h2>Admin</h2>
        <p>Voor platformbeheer, gebruikersbeheer en vacaturemoderatie.</p>
      </a>

      <a class="auth-choice-card" href="{{ route('register.choice') }}">
        <i class="ph ph-user-plus"></i>
        <h2>Nog geen account?</h2>
        <p>Maak een werkzoekende- of werkgeversaccount aan.</p>
      </a>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('auth.partials.styles')
@endpush
