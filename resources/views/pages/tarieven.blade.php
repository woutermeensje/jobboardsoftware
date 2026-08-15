@extends('layouts.app')

@section('title', 'Pricing | JobBoardSoftware')
@section('meta_description', 'Pricing for JobBoardSoftware. Simple, transparent plans for every job board.')

@section('content')
@include('pages.partials.page-styles')

@php
  $plans = \App\Models\BillingPlan::query()
    ->where('is_active', true)
    ->orderBy('monthly_price_cents')
    ->get();
@endphp

<div class="content-page">
  <div class="content-page__shell">

    <section class="content-hero">
      <div class="content-hero__copy">
        <p class="content-eyebrow">Pricing</p>
        <h1>Simple pricing, no surprises</h1>
        <p class="content-hero__intro">
          Start free, upgrade when you grow. Every plan includes hosting, SSL and the full management portal &mdash; no setup fees.
        </p>
      </div>
      <div class="content-visual">
        <i class="ph ph-credit-card"></i>
        <strong>14 day trial</strong>
        <span>Try any plan before you commit.</span>
      </div>
    </section>

    <section class="content-grid">
      @forelse ($plans as $plan)
        <div class="content-card price-card">
          <p class="content-eyebrow">{{ $plan->name }}</p>
          <strong>{{ $plan->formattedMonthlyPrice() }}</strong>
          @if ($plan->description)
            <p>{{ $plan->description }}</p>
          @endif
          @if (! empty($plan->features))
            <ul class="content-list">
              @foreach ($plan->features as $feature)
                <li>{{ $feature }}</li>
              @endforeach
            </ul>
          @endif
          <div class="content-actions">
            <a href="{{ route('register.choice') }}" class="content-btn content-btn--primary">Start free</a>
          </div>
        </div>
      @empty
        <div class="content-card">
          <h3>Plans coming soon</h3>
          <p>Our pricing plans are being finalised &mdash; get in touch for early access.</p>
        </div>
      @endforelse
    </section>

    <section class="content-section">
      <h2>Questions about pricing?</h2>
      <p>Reach out and we'll help you pick the right plan for your job board.</p>
      <div class="content-actions">
        <a href="{{ route('pages.contact') }}" class="content-btn content-btn--ghost">Contact us</a>
      </div>
    </section>

  </div>
</div>
@endsection
