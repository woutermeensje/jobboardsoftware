@extends('layouts.app')

@section('title', 'JobBoardSoftware Alternatives | Compare Job Board Platforms')
@section('meta_description', 'Comparing job board software platforms? See how JobBoardSoftware compares on pricing, custom domains and setup time.')

@section('content')
@include('pages.partials.page-styles')

{{--
  PLACEHOLDER PAGE: built for structure only, no competitor names or
  comparison claims filled in yet. Replace the rows below with real,
  factual comparisons before publishing.
--}}

<div class="content-page">
  <div class="content-page__shell">

    <section class="content-hero">
      <div class="content-hero__copy">
        <p class="content-eyebrow">Alternatives</p>
        <h1>JobBoardSoftware vs. other job board platforms</h1>
        <p class="content-hero__intro">
          Comparing job board software? Here's how JobBoardSoftware stacks up &mdash; this page is still being written, check back soon for a full comparison.
        </p>
        <div class="content-actions">
          <a href="{{ route('register.choice') }}" class="content-btn content-btn--primary">Start free</a>
        </div>
      </div>
      <div class="content-visual">
        <i class="ph ph-scales"></i>
        <strong>Comparison coming soon</strong>
        <span>We're putting together an honest, detailed comparison.</span>
      </div>
    </section>

    <section class="content-section">
      <p class="content-eyebrow">What to expect</p>
      <h2>What we'll compare</h2>
      <div class="content-grid content-grid--two">
        <div class="content-card">
          <i class="ph ph-currency-eur"></i>
          <h3>Pricing</h3>
          <p>Placeholder &mdash; how plans and pricing compare.</p>
        </div>
        <div class="content-card">
          <i class="ph ph-globe-hemisphere-west"></i>
          <h3>Custom domains</h3>
          <p>Placeholder &mdash; how easy it is to bring your own domain.</p>
        </div>
        <div class="content-card">
          <i class="ph ph-rocket-launch"></i>
          <h3>Setup time</h3>
          <p>Placeholder &mdash; how long it takes to go live.</p>
        </div>
        <div class="content-card">
          <i class="ph ph-headset"></i>
          <h3>Support</h3>
          <p>Placeholder &mdash; what kind of support is included.</p>
        </div>
      </div>
    </section>

    <section class="content-section">
      <h2>See it for yourself</h2>
      <p>The best way to compare is to try it &mdash; start a free job board and see how fast you can go live.</p>
      <div class="content-actions">
        <a href="{{ route('register.choice') }}" class="content-btn content-btn--primary">Start free</a>
        <a href="{{ route('pages.contact') }}" class="content-btn content-btn--ghost">Contact us</a>
      </div>
    </section>

  </div>
</div>
@endsection
