@extends('layouts.app')

@section('title', 'Customers | JobBoardSoftware')
@section('meta_description', 'Job boards running on JobBoardSoftware.')

@section('content')
@include('pages.partials.page-styles')

<div class="content-page">
  <div class="content-page__shell">

    <section class="content-hero">
      <div class="content-hero__copy">
        <p class="content-eyebrow">Customers</p>
        <h1>Job boards running on JobBoardSoftware</h1>
        <p class="content-hero__intro">
          From recruitment agencies to niche community platforms &mdash; here's a look at who's building on JobBoardSoftware.
        </p>
        <div class="content-actions">
          <a href="{{ route('register.choice') }}" class="content-btn content-btn--primary">Start your job board</a>
        </div>
      </div>
      <div class="content-visual">
        <i class="ph ph-users-three"></i>
        <strong>Coming soon</strong>
        <span>Customer stories are on their way.</span>
      </div>
    </section>

    {{-- PLACEHOLDER: replace with real customer logos once available. --}}
    <section class="content-section">
      <p class="content-eyebrow">Trusted by</p>
      <h2>Logos coming soon</h2>
      <p>This is where the logos of job boards running on JobBoardSoftware will appear.</p>
      <div class="content-grid">
        <div class="content-card"><p style="text-align:center;">Your logo here</p></div>
        <div class="content-card"><p style="text-align:center;">Your logo here</p></div>
        <div class="content-card"><p style="text-align:center;">Your logo here</p></div>
      </div>
    </section>

    {{-- PLACEHOLDER: replace with a real testimonial once we have one. --}}
    <section class="content-section">
      <p class="content-eyebrow">In their words</p>
      <h2>Customer stories coming soon</h2>
      <div class="content-card">
        <p style="font-style: italic;">"A customer quote will go here once we have one to share."</p>
      </div>
    </section>

    <section class="content-section">
      <h2>Want to be one of our first customers?</h2>
      <p>Launch your job board today and we'll help you get set up.</p>
      <div class="content-actions">
        <a href="{{ route('register.choice') }}" class="content-btn content-btn--primary">Start free</a>
        <a href="{{ route('pages.contact') }}" class="content-btn content-btn--ghost">Contact us</a>
      </div>
    </section>

  </div>
</div>
@endsection
