@extends('layouts.app')

@section('title', 'Features | JobBoardSoftware')
@section('meta_description', 'Everything you need to launch and run your own job board: a public jobboard website, a management portal, custom domains and applicant tracking.')

@section('content')
@include('pages.partials.page-styles')

<div class="content-page">
  <div class="content-page__shell">

    <section class="content-hero">
      <div class="content-hero__copy">
        <p class="content-eyebrow">Features</p>
        <h1>Everything you need to run a job board</h1>
        <p class="content-hero__intro">
          JobBoardSoftware gives you a public jobboard website for your candidates, a management portal for your team, and the infrastructure underneath &mdash; custom domains, SSL, hosting and applicant tracking &mdash; without writing a single line of code.
        </p>
        <div class="content-actions">
          <a href="{{ route('register.choice') }}" class="content-btn content-btn--primary">Start free</a>
          <a href="{{ route('pages.tarieven') }}" class="content-btn content-btn--ghost">View pricing</a>
        </div>
      </div>
      <div class="content-visual">
        <i class="ph ph-briefcase"></i>
        <strong>Launch in minutes</strong>
        <span>Create your environment, add your jobs, connect your domain.</span>
      </div>
    </section>

    <section class="content-section" id="jobboard">
      <p class="content-eyebrow">Jobboard website</p>
      <h2>A branded job board for your candidates</h2>
      <p>Every environment gets its own public website where candidates browse and apply for open roles &mdash; no account required for them to apply.</p>
      <div class="content-grid">
        <div class="content-card">
          <i class="ph ph-globe-hemisphere-west"></i>
          <h3>Your own subdomain</h3>
          <p>Every job board starts live on a free {{ '{yourname}' }}.jobboardsoftware.co subdomain.</p>
        </div>
        <div class="content-card">
          <i class="ph ph-palette"></i>
          <h3>Simple branding</h3>
          <p>Set your brand name, accent color and intro text so the site matches your organisation.</p>
        </div>
        <div class="content-card">
          <i class="ph ph-file-text"></i>
          <h3>Applications with CV upload</h3>
          <p>Candidates apply directly with name, contact details, motivation and an optional CV.</p>
        </div>
      </div>
    </section>

    <section class="content-section" id="management">
      <p class="content-eyebrow">Management portal</p>
      <h2>Run everything from one workspace</h2>
      <p>The management portal is where you post jobs, review applications and manage your job board environments.</p>
      <div class="content-grid">
        <div class="content-card">
          <i class="ph ph-briefcase"></i>
          <h3>Job management</h3>
          <p>Create, publish and close vacancies with department, location and employment type.</p>
        </div>
        <div class="content-card">
          <i class="ph ph-users-three"></i>
          <h3>Applicant tracking</h3>
          <p>Follow every candidate through new, reviewed, hired and rejected stages.</p>
        </div>
        <div class="content-card">
          <i class="ph ph-buildings"></i>
          <h3>Multiple environments</h3>
          <p>Run more than one job board &mdash; per brand, region or business unit &mdash; from a single account.</p>
        </div>
      </div>
    </section>

    <section class="content-section" id="domains">
      <p class="content-eyebrow">Custom domain</p>
      <h2>Bring your own domain</h2>
      <p>Connect a domain you already own and go live on it with automatic DNS verification and SSL.</p>
      <ul class="content-list">
        <li>Point a CNAME record to connect your domain in minutes.</li>
        <li>One-click DNS verification once your records are live.</li>
        <li>SSL is activated automatically once your domain is verified.</li>
      </ul>
    </section>

  </div>
</div>
@endsection
