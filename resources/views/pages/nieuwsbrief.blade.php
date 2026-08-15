@extends('layouts.app')

@section('title', 'Newsletter | JobBoardSoftware')
@section('meta_description', 'Sign up for job updates, platform news and relevant tips for job seekers.')

@section('content')
<section class="form-block">
  <div class="form-block__box">
    <h1 class="form-block__title">Subscribe to the newsletter</h1>

    <form class="content-form" method="GET" action="{{ route('register.jobseeker') }}">
      <div class="content-form__grid">
        <div class="content-field">
          <label for="newsletter-firstname">First name</label>
          <input id="newsletter-firstname" name="firstname" type="text" placeholder="First name">
        </div>
        <div class="content-field">
          <label for="newsletter-email">Email address</label>
          <input id="newsletter-email" name="email" type="email" placeholder="you@example.com">
        </div>
      </div>

      <div class="content-actions">
        <button class="content-btn content-btn--primary" type="submit">Create account</button>
      </div>
    </form>
  </div>
</section>
@endsection

@push('styles')
  <style>
    .form-block {
      display: grid;
      justify-content: center;
      padding: 0 24px;
      margin: 56px 0;
    }

    .form-block__box {
      width: min(560px, 100%);
      padding: 30px;
      border: 1px solid var(--color-border);
      border-radius: 8px;
      background: #ffffff;
      box-shadow: var(--shadow-sm);
    }

    .form-block__title {
      margin: 0 0 18px;
      font-size: 24px;
      font-weight: 800;
    }
  </style>
@endpush
