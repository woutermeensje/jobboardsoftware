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
