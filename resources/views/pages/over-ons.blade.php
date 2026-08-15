@extends('layouts.app')

@section('title', 'About us | JobBoardSoftware')
@section('meta_description', 'JobBoardSoftware is SaaS software for launching and running your own job board, with a public jobboard website, a management portal and custom domains.')

@section('content')
@include('pages.partials.page-styles')

<div class="content-page">
  <div class="content-page__shell">

    <section class="content-hero">
      <div class="content-hero__copy">
        <p class="content-eyebrow">About us</p>
        <h1>We build the job board, you run it</h1>
        <p class="content-hero__intro">
          JobBoardSoftware exists so that anyone &mdash; a recruitment agency, a niche community, a regional employer network &mdash; can launch a professional job board without a development team.
        </p>
      </div>
      <div class="content-visual">
        <i class="ph ph-rocket-launch"></i>
        <strong>Built for job boards</strong>
        <span>Hosting, domains and applicant tracking, ready to go.</span>
      </div>
    </section>

    <section class="content-section">
      <p class="content-eyebrow">What we do</p>
      <h2>One platform for your whole job board</h2>
      <p>
        We handle the parts every job board needs &mdash; a public website for candidates, a management portal for your team, DNS and SSL for your own domain &mdash; so you can focus on the jobs and the people who apply for them.
      </p>
    </section>

    <section class="content-section">
      <p class="content-eyebrow">Who it's for</p>
      <h2>Built for teams launching their own job board</h2>
      <div class="content-grid content-grid--two">
        <div class="content-card">
          <i class="ph ph-buildings"></i>
          <h3>Recruitment &amp; staffing agencies</h3>
          <p>Give every client or brand its own branded job board on your own infrastructure.</p>
        </div>
        <div class="content-card">
          <i class="ph ph-users-three"></i>
          <h3>Niche &amp; community platforms</h3>
          <p>Launch a focused job board for an industry, region or community in minutes.</p>
        </div>
      </div>
    </section>

    <section class="content-section">
      <h2>Want to know more?</h2>
      <p>We're happy to walk you through the platform and answer any questions.</p>
      <div class="content-actions">
        <a href="{{ route('pages.contact') }}" class="content-btn content-btn--primary">Get in touch</a>
        <a href="{{ route('register.choice') }}" class="content-btn content-btn--ghost">Start free</a>
      </div>
    </section>

  </div>
</div>
@endsection
