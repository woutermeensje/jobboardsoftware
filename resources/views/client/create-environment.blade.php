@extends('layouts.app')

@section('title', 'Create environment | Client dashboard')
@section('meta_description', 'Create a new job board environment.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('client.partials.navigation')
@endsection

@section('content')
      @if($errors->any())
        <section class="dash-card dash-card--danger">
          <strong>Environment could not be created.</strong>
          <ul class="dash-message-list">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </section>
      @endif

      <div class="dash-form-layout">
        <main class="dash-form-layout__main">
          <section class="dash-panel">
            <div class="dash-panel__head">
              <div>
                <h2>Create environment</h2>
                <p>Start a new job board environment for your account.</p>
              </div>
            </div>

            <form class="domain-form" method="POST" action="{{ route('client.environments.store') }}">
              @csrf

              <div class="domain-form__grid domain-form__grid--single">
                <label class="domain-field">
                  <span>Job board name</span>
                  <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Acme Careers"
                    autocomplete="off"
                    required
                  >
                </label>

                <label class="domain-field">
                  <span>Subdomain</span>
                  <input
                    type="text"
                    name="subdomain"
                    value="{{ old('subdomain') }}"
                    placeholder="acme-careers"
                    autocomplete="off"
                    pattern="[a-z0-9]([a-z0-9\-]*[a-z0-9])?"
                    maxlength="63"
                    required
                  >
                  <span class="input-description">Your job board will be available at <strong>{{ old('subdomain') ?: 'yoursubdomain' }}.{{ $baseDomain }}</strong>. Use only lowercase letters, numbers and hyphens.</span>
                </label>
              </div>

              <div class="dash-actions dash-actions--spaced">
                <button class="dash-btn dash-btn--primary" type="submit">
                  <i class="ph ph-plus" aria-hidden="true"></i>
                  Create environment
                </button>
              </div>
            </form>
          </section>
        </main>

        <aside class="dash-form-layout__aside">
          <section class="dash-card dash-form-side">
            <h2>Environment setup</h2>
            <p>Your job board goes live immediately on the subdomain you choose.</p>
            <ul>
              <li>Use only lowercase letters, numbers and hyphens for the subdomain.</li>
              <li>You can connect your own custom domain afterwards from the Domains page.</li>
              <li>Job types, sectors and packages can be configured once the environment is created.</li>
            </ul>
          </section>
        </aside>
      </div>
@endsection
