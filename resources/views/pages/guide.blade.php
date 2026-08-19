@extends('layouts.app')

@section('title', 'Guide | JobBoardSoftware')
@section('meta_description', 'Guides about starting, building and growing a job board.')

@section('content')
@include('pages.partials.page-styles')

@php
  $guides = [
    [
      'title' => 'How to start',
      'description' => 'A practical guide to starting, building and growing a job board.',
      'icon' => 'ph-map-trifold',
      'url' => route('pages.how-to-start-a-job-board'),
    ],
    [
      'title' => 'Choosing a niche for your job board',
      'description' => 'Industry, region, skill, profession and other angles for positioning your job board.',
      'icon' => 'ph-crosshair',
      'url' => route('pages.guides.choosing-a-niche-for-your-job-board'),
    ],
    [
      'title' => 'Building an audience and generating traffic',
      'description' => 'Ideas for attracting the right candidates and growing repeat visits over time.',
      'icon' => 'ph-megaphone',
      'url' => route('pages.guides.building-an-audience-and-generating-traffic'),
    ],
    [
      'title' => 'Generating recurring customers',
      'description' => 'How to turn employers into repeat buyers and build more predictable revenue.',
      'icon' => 'ph-repeat',
      'url' => route('pages.guides.generating-recurring-customers'),
    ],
    [
      'title' => 'Run your job board as an agency!',
      'description' => 'Why running your job board like an agency can unlock more revenue and stronger client relationships.',
      'icon' => 'ph-briefcase',
      'url' => route('pages.guides.run-your-job-board-as-an-agency'),
    ],
    [
      'title' => 'Give deals to your customers',
      'description' => 'Strategies for offering discounts and bundles that keep employers coming back.',
      'icon' => 'ph-percent',
      'url' => route('pages.guides.give-deals-to-your-customers'),
    ],
    [
      'title' => 'How to price your job postings?',
      'description' => 'How to price single postings, bundles and subscriptions for your job board.',
      'icon' => 'ph-currency-dollar',
      'url' => route('pages.guides.how-to-price-your-job-postings'),
    ],
    [
      'title' => 'How to do SEO/GEO for your job board?',
      'description' => 'How to optimize your job board for search engines and AI-driven search.',
      'icon' => 'ph-magnifying-glass',
      'url' => route('pages.guides.how-to-do-seo-geo-for-your-job-board'),
    ],
    [
      'title' => 'The importance of job category pages',
      'description' => 'Why category pages matter for SEO, navigation and conversion.',
      'icon' => 'ph-squares-four',
      'url' => route('pages.guides.the-importance-of-job-category-pages'),
    ],
    [
      'title' => 'How to get the right traffic?',
      'description' => 'How to attract the traffic that actually converts into paying customers.',
      'icon' => 'ph-users-three',
      'url' => route('pages.guides.how-to-get-the-right-traffic'),
    ],
    [
      'title' => 'Why should you acquire a job board?',
      'description' => 'The reasons to consider acquiring an existing job board instead of starting from scratch.',
      'icon' => 'ph-lightbulb',
      'url' => route('pages.guides.why-should-you-acquire-a-job-board'),
    ],
    [
      'title' => 'How to acquire a job board?',
      'description' => 'A practical guide to finding, evaluating and acquiring a job board.',
      'icon' => 'ph-handshake',
      'url' => route('pages.guides.how-to-acquire-a-job-board'),
    ],
    [
      'title' => 'Choosing a job board name',
      'description' => 'How to pick a name that fits your niche and works for branding and SEO.',
      'icon' => 'ph-tag',
      'url' => route('pages.guides.choosing-a-job-board-name'),
    ],
  ];
@endphp

<div class="content-page">
  <div class="content-page__shell">
    <section class="features-grid" aria-label="Guides">
      @foreach($guides as $guide)
        <a class="features-card" href="{{ $guide['url'] }}">
          <i class="ph {{ $guide['icon'] }} features-card__icon" aria-hidden="true"></i>
          <h2 class="features-card__title">{{ $guide['title'] }}</h2>
          <p class="features-card__description">{{ $guide['description'] }}</p>
        </a>
      @endforeach
    </section>

    <section class="content-section" aria-labelledby="job-board-guide-heading">
      <h1 id="job-board-guide-heading">Our guide to start a job board</h1>
      <p>On this page, you can find different steps you can take to start and launch a job board. From deciding on your niche to getting your first job posts, doing outreach and sales, and growing with organic traffic.</p>
    </section>
  </div>
</div>
@endsection
