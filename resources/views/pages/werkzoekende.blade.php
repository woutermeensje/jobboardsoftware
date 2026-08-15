@extends('layouts.app')

@section('title', 'Job seeker | JobBoardSoftware')
@section('meta_description', 'Find jobs, set up job alerts, receive the newsletter and create a job seeker account.')

@section('content')
<section class="content-page">
  <div class="content-page__shell">
    <header class="content-hero">
      <div class="content-hero__copy">
        <p class="content-eyebrow">Job seeker</p>
        <h1>Find relevant jobs faster.</h1>
        <p class="content-hero__intro">Search jobs, save interesting roles and use alerts to stay up to date on new opportunities automatically.</p>
        <div class="content-actions">
          <a class="content-btn content-btn--primary" href="{{ route('register.jobseeker') }}">Create account</a>
          <a class="content-btn content-btn--ghost" href="{{ route('welcome') }}#jobs">View jobs</a>
        </div>
      </div>
      <aside class="content-visual" aria-label="Job seeker overview">
        <i class="ph ph-magnifying-glass"></i>
        <strong>Search, follow and apply from your own portal.</strong>
        <span>A candidate-focused entry point for the public job board.</span>
      </aside>
    </header>

    <div class="content-grid">
      <article class="content-card">
        <i class="ph ph-bell-ringing"></i>
        <h3>Job alerts</h3>
        <p>Create alerts for search terms, locations and roles that match your next step.</p>
        <div class="content-actions">
          <a class="content-btn content-btn--ghost" href="{{ route('pages.job-alerts') }}">View job alerts</a>
        </div>
      </article>
      <article class="content-card">
        <i class="ph ph-envelope-simple"></i>
        <h3>Newsletter</h3>
        <p>Receive updates with new jobs, platform news and practical tips for your search.</p>
        <div class="content-actions">
          <a class="content-btn content-btn--ghost" href="{{ route('pages.nieuwsbrief') }}">Go to newsletter</a>
        </div>
      </article>
      <article class="content-card">
        <i class="ph ph-user-circle-plus"></i>
        <h3>Create account</h3>
        <p>Register as a job seeker to manage your profile and application steps in one place.</p>
        <div class="content-actions">
          <a class="content-btn content-btn--ghost" href="{{ route('register.jobseeker') }}">Start registration</a>
        </div>
      </article>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('pages.partials.page-styles')
@endpush
