@extends('layouts.app')

@section('title', 'Contact | JobBoardSoftware')
@section('meta_description', 'Get in touch with the JobBoardSoftware team.')

@section('content')
@include('pages.partials.page-styles')

<div class="content-page">
  <div class="content-page__shell">

    <section class="content-hero">
      <div class="content-hero__copy">
        <p class="content-eyebrow">Contact</p>
        <h1>Let's talk about your job board</h1>
        <p class="content-hero__intro">
          Questions about the platform, pricing or a custom domain? Send us a message and we'll get back to you.
        </p>
      </div>
      <div class="content-visual">
        <i class="ph ph-envelope-simple"></i>
        <strong>team@jobboardsoftware.co</strong>
        <span>We usually reply within one business day.</span>
      </div>
    </section>

    <section class="content-section">
      @if (session('status'))
        <p class="content-eyebrow" style="color: var(--color-primary-strong);">{{ session('status') }}</p>
      @endif

      <form method="POST" action="{{ route('pages.contact.submit') }}" class="content-form">
        @csrf

        <div class="content-form__grid">
          <div class="content-field">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>
            @error('name')<span style="color:#b91c1c;font-size:13px;">{{ $message }}</span>@enderror
          </div>

          <div class="content-field">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            @error('email')<span style="color:#b91c1c;font-size:13px;">{{ $message }}</span>@enderror
          </div>
        </div>

        <div class="content-field">
          <label for="company_name">Company (optional)</label>
          <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}">
        </div>

        <div class="content-field">
          <label for="message">Message</label>
          <textarea id="message" name="message" required>{{ old('message') }}</textarea>
          @error('message')<span style="color:#b91c1c;font-size:13px;">{{ $message }}</span>@enderror
        </div>

        <div class="content-actions">
          <button type="submit" class="content-btn content-btn--primary">Send message</button>
        </div>
      </form>
    </section>

  </div>
</div>
@endsection
