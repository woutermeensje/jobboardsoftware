@extends('layouts.app')

@section('title', 'Nieuwsbrief | JobBoardSoftware')
@section('meta_description', 'Schrijf je in voor vacature-updates, platformnieuws en relevante tips voor werkzoekenden.')

@section('content')
<section class="form-block">
  <div class="form-block__box">
    <h1 class="form-block__title">Inschrijven nieuwsbrief</h1>

    <form class="content-form" method="GET" action="{{ route('register.werkzoekende') }}">
      <div class="content-form__grid">
        <div class="content-field">
          <label for="newsletter-firstname">Voornaam</label>
          <input id="newsletter-firstname" name="firstname" type="text" placeholder="Voornaam">
        </div>
        <div class="content-field">
          <label for="newsletter-email">E-mailadres</label>
          <input id="newsletter-email" name="email" type="email" placeholder="jij@example.com">
        </div>
      </div>

      <div class="content-actions">
        <button class="content-btn content-btn--primary" type="submit">Account aanmaken</button>
      </div>
    </form>
  </div>
</section>
@endsection

@push('styles')
  <style>
    .form-block {
      display: grid;
      justify-content: center;
      padding: 0 24px;
      margin: 56px 0;
    }

    .form-block__box {
      width: min(560px, 100%);
      padding: 30px;
      border: 1px solid var(--color-border);
      border-radius: 8px;
      background: #ffffff;
      box-shadow: var(--shadow-sm);
    }

    .form-block__title {
      margin: 0 0 18px;
      font-size: 24px;
      font-weight: 800;
    }
  </style>
@endpush
