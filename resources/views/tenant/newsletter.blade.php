@extends('layouts.tenant')

@section('title', 'Newsletter | '.$brandName)
@section('meta_description', 'Subscribe to the newsletter for the latest updates from '.$brandName.'.')

@section('content')
  <main class="tenant-shell">
    @if(session('status'))
      <section class="tenant-alert">{{ session('status') }}</section>
    @endif

    @if($errors->any())
      <section class="tenant-alert tenant-alert--danger">
        <strong>Subscription could not be saved.</strong>
        <ul class="tenant-message-list">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </section>
    @endif

    <section class="tenant-post-job">
      <div class="tenant-post-job__content">
        <article class="tenant-panel tenant-post-job__main">
          <form method="POST" action="{{ route('tenant.newsletter.store') }}" class="tenant-form">
            @csrf

            <div class="tenant-panel__head tenant-form-header">
              <h2 class="tenant-form-title">Subscribe to our newsletter</h2>
              <p class="tenant-form-intro">Stay up to date with the latest news and vacancies from {{ $brandName }}.</p>
            </div>

            <label>
              First name
              <input name="first_name" value="{{ old('first_name') }}" required autofocus>
              @error('first_name')<span class="tenant-form__error">{{ $message }}</span>@enderror
            </label>

            <label>
              Email address
              <input name="email" type="email" value="{{ old('email') }}" required>
              @error('email')<span class="tenant-form__error">{{ $message }}</span>@enderror
            </label>

            <button class="tenant-btn tenant-btn--primary" type="submit">Subscribe</button>
          </form>
        </article>
      </div>
    </section>
  </main>
@endsection
