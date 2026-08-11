@extends('layouts.app')

@section('title', $title.' | JobBoardSoftware')

@php
  $formTitle = $role === \App\Models\User::ROLE_ADMIN ? 'Admin login' : 'Log in';
@endphp

@section('content')
<section class="auth-page">
  <div class="auth-shell">
    <div class="auth-form-card">
      <div class="auth-form-head">
        <h1>{{ $formTitle }}</h1>
      </div>

      <form method="POST" action="{{ $action }}" class="auth-form">
        @csrf

        <div class="auth-field">
          <label class="auth-label" for="email">Email address</label>
          <input id="email" class="auth-input" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
          @error('email')
            <p class="auth-error">{{ $message }}</p>
          @enderror
        </div>

        <div class="auth-field">
          <label class="auth-label" for="password">Password</label>
          <input id="password" class="auth-input" name="password" type="password" autocomplete="current-password" required>
          @error('password')
            <p class="auth-error">{{ $message }}</p>
          @enderror
        </div>

        <label class="auth-check">
          <input type="checkbox" name="remember" value="1">
          Keep me signed in
        </label>

        <div class="auth-actions">
          <button class="auth-button auth-button--primary" type="submit">
            <i class="ph ph-sign-in"></i>
            Log in
          </button>
          <div class="auth-secondary-actions">
            @if($registerUrl)
              <a class="auth-link" href="{{ $registerUrl }}">Create account</a>
            @endif
            <a class="auth-link" href="{{ route('welcome') }}">Back to website</a>
          </div>
        </div>
      </form>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('auth.partials.styles')
@endpush
