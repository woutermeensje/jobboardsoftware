@extends('layouts.app')

@section('title', 'Features | JobBoardSoftware')
@section('meta_description', 'Everything you need to launch and run your own job board: a public jobboard website, a management portal, custom domains and applicant tracking.')

@section('content')
@include('pages.partials.page-styles')

@php
  $features = [
    [
      'title' => 'Different use cases',
      'icon' => 'ph-squares-four',
      'description' => 'Build a niche job board, internal careers hub, recruitment marketplace or community job platform from one flexible setup.',
    ],
    [
      'title' => 'Multiple industries',
      'icon' => 'ph-buildings',
      'description' => 'Use the platform for healthcare, tech, sustainability, education, hospitality, local hiring and other specialist markets.',
    ],
    [
      'title' => 'GEO',
      'icon' => 'ph-sparkle',
      'description' => 'Structure your job board so pages are easier to understand for generative search engines and AI-driven discovery.',
    ],
    [
      'title' => 'SEO',
      'icon' => 'ph-magnifying-glass',
      'description' => 'Create indexable job, category and landing pages with clean URLs, focused content and scalable organic growth in mind.',
    ],
    [
      'title' => 'Landingpage designs',
      'icon' => 'ph-layout',
      'description' => 'Launch dedicated landing pages for locations, sectors, audiences or campaigns with designs that match your brand.',
    ],
    [
      'title' => 'Job marketing',
      'icon' => 'ph-megaphone',
      'description' => 'Promote vacancies with packages, featured placements and extra visibility options for employers.',
    ],
    [
      'title' => 'Blog options',
      'icon' => 'ph-article',
      'description' => 'Publish guides, market updates and career content to support SEO, build trust and attract the right audience.',
    ],
  ];
@endphp

<div class="content-page">
  <div class="content-page__shell">
    <section class="features-grid" aria-label="Features">
      @foreach ($features as $feature)
        <article class="features-card">
          <i class="ph {{ $feature['icon'] }} features-card__icon" aria-hidden="true"></i>
          <h2 class="features-card__title">{{ $feature['title'] }}</h2>
          <p class="features-card__description">{{ $feature['description'] }}</p>
        </article>
      @endforeach
    </section>
  </div>
</div>
@endsection
