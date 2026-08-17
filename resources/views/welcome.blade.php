@extends('layouts.app')

@section('title', 'Starting a job board! | Jobboardsoftware.co')
@section('meta_description', 'Start and grow your own job board with Jobboardsoftware.co: SEO-ready job pages, employer accounts, applications, and extra revenue models.')

@push('styles')
  @vite('resources/css/landingpage.css')
@endpush

@section('content')
<div class="welcome-page">
  <section class="welcome-hero" aria-labelledby="welcome-hero-title">
    <div class="welcome-hero__shape" aria-hidden="true"></div>

    <div class="welcome-hero__inner">
      <div class="welcome-hero__content">
        <h1 id="welcome-hero-title" class="welcome-hero__title">
          Launch a professional job board in minutes.
        </h1>

        <p class="welcome-hero__intro description-text">
          Create a professional job board with SEO-ready pages, employer accounts and paid posting packages.
        </p>

        <div class="welcome-hero__actions" aria-label="Primary actions">
          <a class="btn btn-primary" href="{{ route('register.choice') }}">Start free</a>
          <a class="btn btn-ghost" href="{{ route('pages.tarieven') }}">View pricing</a>
        </div>

        <ul class="landing-benefits" aria-label="Included features">
          <li class="landing-benefits__item">
            <i class="ph ph-check landing-benefits__icon" aria-hidden="true"></i>
            <span class="landing-benefits__text">Optimized for SEO/GEO</span>
          </li>
          <li class="landing-benefits__item">
            <i class="ph ph-check landing-benefits__icon" aria-hidden="true"></i>
            <span class="landing-benefits__text">Online within 5 minutes</span>
          </li>
          <li class="landing-benefits__item">
            <i class="ph ph-check landing-benefits__icon" aria-hidden="true"></i>
            <span class="landing-benefits__text">Multiple revenue models</span>
          </li>
          <li class="landing-benefits__item">
            <i class="ph ph-check landing-benefits__icon" aria-hidden="true"></i>
            <span class="landing-benefits__text">Custom designs</span>
          </li>
        </ul>
      </div>
    </div>
  </section>

  <section class="welcome-copy" aria-labelledby="welcome-copy-title">
    <div class="welcome-copy__inner rich-text">
      <h2 id="welcome-copy-title">Job board software</h2>

      <p>
        Jobboardsoftware.co is a software application that lets you easily build and manage your own job board. The tool was built in 2026 by Wouter Meens, the owner of several job boards. Drawing on five years of experience running job boards, it was originally built for personal use &mdash; because at the time, no alternatives existed that offered the functionality you really need to make a job board successful.
      </p>

      <p>
        Jobboardsoftware sets itself apart with a strong focus on SEO/GEO traffic, easy creation and optimisation of category pages, a professional login environment for both employers and job seekers, and the ability to add extra revenue models to your job board &mdash; such as selling sponsored marketing blocks, access to the CV database, and backlinks in blog posts and articles.
      </p>

      <h2>Why use job board software?</h2>

      <p>
        There are several use cases for companies and organizations using job board software. Think of organizations in specific industries that work with members or communities and want to create an extra source of revenue by selling job posts on a subdomain. It can also be a solo entrepreneur who wants to build a side project to make some extra money, an addition to an existing blog or website with lots of traffic, or a solution for recruitment agencies that do not want to build a job board from scratch.
      </p>

      <h2>Your next step?</h2>

      <p>
        Curious about our software? Or would you like advice on how to start and run a job board? Set up an introduction call with one of our team members to discover the benefits of working with <a href="{{ route('welcome') }}">Jobboardsoftware.co</a>.
      </p>
    </div>
  </section>
</div>
@endsection
