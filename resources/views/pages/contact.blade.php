@extends('layouts.app')

@section('title', 'Contact | JobBoardSoftware')
@section('meta_description', 'Neem contact op over JobBoardSoftware, SaaS jobboard software, licenties of een eigen domein.')

@section('content')
<section class="content-page">
  <div class="content-page__shell">
    <header class="content-hero">
      <div class="content-hero__copy">
        <p class="content-eyebrow">Contact</p>
        <h1>Bespreek je SaaS jobboard plannen.</h1>
        <p class="content-hero__intro">Wil je jobboard software aanbieden, een klantdomein koppelen of de beheeromgeving verder uitbouwen? Stuur een bericht en plan een korte verkenning.</p>
      </div>
      <aside class="content-visual" aria-label="Contactgegevens">
        <i class="ph ph-chat-circle-text"></i>
        <strong>wouter@inhuren.com</strong>
        <span>Voor demo's, productvragen en platformadvies.</span>
      </aside>
    </header>

    <section class="content-section" aria-labelledby="contact-form-title">
      <h2 id="contact-form-title">Stuur een bericht</h2>
      <form class="content-form" method="GET" action="mailto:wouter@inhuren.com">
        <div class="content-form__grid">
          <div class="content-field">
            <label for="contact-name">Naam</label>
            <input id="contact-name" name="name" type="text" placeholder="Je naam">
          </div>
          <div class="content-field">
            <label for="contact-email">E-mailadres</label>
            <input id="contact-email" name="email" type="email" placeholder="jij@example.com">
          </div>
        </div>
        <div class="content-field">
          <label for="contact-message">Bericht</label>
          <textarea id="contact-message" name="body" placeholder="Waar wil je over sparren?"></textarea>
        </div>
        <div class="content-actions">
          <button class="content-btn content-btn--primary" type="submit">Contact opnemen</button>
          <a class="content-btn content-btn--ghost" href="{{ route('pages.tarieven') }}">Bekijk tarieven</a>
        </div>
      </form>
    </section>
  </div>
</section>
@endsection

@push('styles')
  @include('pages.partials.page-styles')
@endpush
