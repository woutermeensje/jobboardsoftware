@extends('layouts.app')

@section('title', 'Pricing | JobBoardSoftware')
@section('meta_description', 'Pricing for JobBoardSoftware. Simple, transparent plans for every job board.')

@section('content')
@include('pages.partials.page-styles')

@php
  $fakeBenefits = [
    'Placeholder benefit one',
    'Placeholder benefit two',
    'Placeholder benefit three',
    'Placeholder benefit four',
    'Placeholder benefit five',
    'Placeholder benefit six',
    'Placeholder benefit seven',
    'Placeholder benefit eight',
    'Placeholder benefit nine',
    'Placeholder benefit ten',
  ];

  $plans = [
    [
      'name' => 'Free plan',
      'price' => '14-day free trial',
      'description' => 'Try the platform and explore the core job board tools.',
      'features' => $fakeBenefits,
    ],
    [
      'name' => 'Standard plan',
      'price' => '€149 per month',
      'description' => 'For job boards that are ready to publish and grow.',
      'features' => $fakeBenefits,
    ],
    [
      'name' => 'Pro plan',
      'price' => '€249 per month',
      'description' => 'For professional job boards with advanced growth needs.',
      'features' => $fakeBenefits,
    ],
  ];
@endphp

<div class="content-page">
  <div class="content-page__shell">

    <section class="pricing-grid" aria-label="Pricing plans">
      @foreach ($plans as $plan)
        <article class="pricing-card">
          <div class="pricing-card__header">
            <h2 class="pricing-card__name">{{ $plan['name'] }}</h2>
            <p class="pricing-card__price">{{ $plan['price'] }}</p>
            <p class="pricing-card__description">{{ $plan['description'] }}</p>
          </div>

          <ul class="pricing-card__benefits">
            @foreach ($plan['features'] as $feature)
              <li>
                <i class="ph ph-check" aria-hidden="true"></i>
                <span>{{ $feature }}</span>
              </li>
            @endforeach
          </ul>

          <div class="pricing-card__actions">
            <a href="{{ route('register.choice') }}" class="btn btn-primary">Start free</a>
          </div>
        </article>
      @endforeach
    </section>

  </div>
</div>
@endsection
