@extends('layouts.app')

@section('title', 'Aanmelden | JobBoardSoftware')

@section('content')
<section class="auth-page">
  <div class="auth-shell">
    <div class="auth-head">
      <p class="auth-eyebrow">Aanmelden</p>
      <h1>Maak een account aan</h1>
      <p>Kies de rol die past bij je gebruik van het platform. Admin accounts worden handmatig aangemaakt.</p>
    </div>

    <div class="auth-choice-grid">
      <a class="auth-choice-card" href="{{ route('register.werkzoekende') }}">
        <i class="ph ph-user-circle-plus"></i>
        <h2>Werkzoekende</h2>
        <p>Maak een kandidaatprofiel aan voor vacatures, favorieten en sollicitaties.</p>
      </a>

      <a class="auth-choice-card" href="{{ route('register.werkgever') }}">
        <i class="ph ph-briefcase"></i>
        <h2>Werkgever</h2>
        <p>Maak een werkgeversaccount aan voor vacatureplaatsingen en reacties.</p>
      </a>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('auth.partials.styles')
@endpush
