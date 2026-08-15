@extends('layouts.app')

@section('title', 'Employer | JobBoardSoftware')
@section('meta_description', 'Post jobs, manage applications and create an employer account in JobBoardSoftware.')

@section('content')
<section class="content-page">
  <div class="content-page__shell">
    <header class="content-hero">
      <div class="content-hero__copy">
        <p class="content-eyebrow">Employer</p>
        <h1>Publish jobs from your own employer portal.</h1>
        <p class="content-hero__intro">Employers get a self-service portal to post jobs, manage applications and improve role visibility.</p>
        <div class="content-actions">
          <a class="content-btn content-btn--primary" href="{{ route('pages.vacature-plaatsen') }}">Post a job</a>
          <a class="content-btn content-btn--ghost" href="{{ route('register.employer') }}">Create account</a>
        </div>
      </div>
      <aside class="content-visual" aria-label="Employer overview">
        <i class="ph ph-buildings"></i>
        <strong>Employer self-service</strong>
        <span>A foundation for postings, roles, company pages and candidate management.</span>
      </aside>
    </header>

    <div class="content-grid">
      <article class="content-card">
        <i class="ph ph-briefcase"></i>
        <h3>Post a job</h3>
        <p>Create jobs with role information, location, category and employment type.</p>
      </article>
      <article class="content-card">
        <i class="ph ph-currency-eur"></i>
        <h3>Pricing</h3>
        <p>Choose from packages for single postings, subscriptions or custom portals.</p>
      </article>
      <article class="content-card">
        <i class="ph ph-user-circle-plus"></i>
        <h3>Create account</h3>
        <p>Register an employer to manage jobs and applications in one place.</p>
      </article>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('pages.partials.page-styles')
@endpush
