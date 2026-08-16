@extends('layouts.app')

@section('title', 'Add packages | Client dashboard')
@section('meta_description', 'Add job posting packages for job board environments.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('client.partials.navigation')
@endsection

@php
  $selectedTenantId = (string) old('tenant_id', $tenants->first()?->id);
@endphp

@section('content')
      @if($errors->any())
        <section class="dash-card dash-card--danger">
          <strong>Package could not be added.</strong>
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
                <h2>Add packages</h2>
                <p>Create a package employers can choose from when they submit a job.</p>
              </div>
              <a class="dash-link" href="{{ route('client.packages.index') }}">Back to packages</a>
            </div>

            @if(! $packageTableReady)
              <div class="dash-empty">
                <h3>Package setup is not ready yet</h3>
                <p>Run the latest database migrations before adding packages.</p>
              </div>
            @elseif($tenants->isEmpty())
              <div class="dash-empty">
                <h3>No environments yet</h3>
                <p>Create an environment before adding packages.</p>
                <div class="dash-actions">
                  <a class="dash-link" href="{{ route('client.environments.create') }}">Create environment</a>
                </div>
              </div>
            @else
              <form class="domain-form" method="POST" action="{{ route('client.packages.store') }}">
                @csrf

                <div class="domain-form__grid">
                  <label class="domain-field">
                    <span>Environment</span>
                    <select name="tenant_id" required>
                      @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}" @selected($selectedTenantId === (string) $tenant->id)>
                          {{ $tenant->name }} ({{ $tenant->slug }})
                        </option>
                      @endforeach
                    </select>
                  </label>

                  <label class="domain-field">
                    <span>Package name</span>
                    <input
                      type="text"
                      name="name"
                      value="{{ old('name') }}"
                      placeholder="For example: Featured job"
                      autocomplete="off"
                      required
                    >
                  </label>
                </div>

                <div class="domain-form__grid">
                  <label class="domain-field">
                    <span>Price</span>
                    <input
                      type="number"
                      name="price"
                      value="{{ old('price') }}"
                      min="0"
                      step="0.01"
                      placeholder="99.00"
                      required
                    >
                  </label>

                  <label class="domain-field">
                    <span>Currency</span>
                    <select name="currency" required>
                      @foreach(['EUR', 'USD', 'GBP'] as $currency)
                        <option value="{{ $currency }}" @selected(old('currency', 'EUR') === $currency)>{{ $currency }}</option>
                      @endforeach
                    </select>
                  </label>
                </div>

                <div class="domain-form__grid domain-form__grid--single">
                  <label class="domain-field">
                    <span>Days online</span>
                    <input
                      type="number"
                      name="online_days"
                      value="{{ old('online_days') }}"
                      min="1"
                      step="1"
                      placeholder="30"
                      required
                    >
                  </label>
                </div>

                <div class="dash-actions dash-actions--spaced">
                  <button class="dash-btn dash-btn--primary" type="submit">
                    <i class="ph ph-plus" aria-hidden="true"></i>
                    Add package
                  </button>
                  <a class="dash-link" href="{{ route('client.packages.index') }}">Cancel</a>
                </div>
              </form>
            @endif
          </section>
        </main>

        <aside class="dash-form-layout__aside">
          <section class="dash-card dash-form-side">
            <h2>Post-a-job packages</h2>
            <p>Packages added here appear in the package selector on the tenant post-a-job form and on the pricing page.</p>
          </section>
        </aside>
      </div>
@endsection
