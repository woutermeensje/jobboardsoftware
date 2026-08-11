@extends('layouts.app')

@section('title', 'Post a job | JobBoardSoftware')
@section('meta_description', 'Start posting a job and create an employer account.')

@section('content')
<section class="content-page">
  <div class="content-page__shell">
    <header class="content-hero">
      <div class="content-hero__copy">
        <p class="content-eyebrow">Post a job</p>
        <h1>Start a new job posting.</h1>
        <p class="content-hero__intro">Fill in the essentials of the role and continue to an employer account to manage the posting later.</p>
      </div>
      <aside class="content-visual" aria-label="Post a job">
        <i class="ph ph-rocket-launch"></i>
        <strong>From draft to publication</strong>
        <span>An initial posting flow for employers inside the SaaS portal.</span>
      </aside>
    </header>

    <section class="content-section" aria-labelledby="posting-title">
      <h2 id="posting-title">Job details</h2>
      <form class="content-form" method="GET" action="{{ route('register.werkgever') }}">
        <div class="content-form__grid">
          <div class="content-field">
            <label for="job-title">Job title</label>
            <input id="job-title" name="job_title" type="text" placeholder="For example: Sales Development Representative">
          </div>
          <div class="content-field">
            <label for="company">Company name</label>
            <input id="company" name="company" type="text" placeholder="Organization name">
          </div>
          <div class="content-field">
            <label for="job-location">Location</label>
            <input id="job-location" name="location" type="text" placeholder="City, region or remote">
          </div>
          <div class="content-field">
            <label for="job-type">Employment type</label>
            <select id="job-type" name="type">
              <option>Fulltime</option>
              <option>Parttime</option>
              <option>Remote</option>
              <option>Freelance</option>
            </select>
          </div>
        </div>
        <div class="content-field">
          <label for="job-description">Short description</label>
          <textarea id="job-description" name="description" placeholder="Describe the role in a few sentences."></textarea>
        </div>
        <div class="content-actions">
          <button class="content-btn content-btn--primary" type="submit">Create employer account</button>
          <a class="content-btn content-btn--ghost" href="{{ route('pages.tarieven') }}">View pricing</a>
        </div>
      </form>
    </section>
  </div>
</section>
@endsection

@push('styles')
  @include('pages.partials.page-styles')
@endpush
