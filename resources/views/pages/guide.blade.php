@extends('layouts.app')

@section('title', 'Guide | JobBoardSoftware')
@section('meta_description', 'Guides about starting, building and growing a job board.')

@section('content')
@include('pages.partials.page-styles')

<div class="content-page">
  <div class="content-page__shell">
    <section class="features-grid" aria-label="Guides">
      <a class="features-card" href="{{ route('pages.how-to-start-a-job-board') }}">
        <i class="ph ph-map-trifold features-card__icon" aria-hidden="true"></i>
        <h2 class="features-card__title">How to start</h2>
        <p class="features-card__description">A practical guide to starting, building and growing a job board.</p>
      </a>
    </section>
  </div>
</div>
@endsection
