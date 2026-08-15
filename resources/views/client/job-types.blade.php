@extends('layouts.app')

@section('title', 'Job types | Client dashboard')
@section('meta_description', 'Manage job types for job board environments.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('client.partials.navigation')
@endsection

@php
  $selectedTenantId = old('tenant_id', $tenants->first()?->id);
@endphp

@section('content')
      @if(session('status'))
        <section class="dash-card dash-card--success"><strong>{{ session('status') }}</strong></section>
      @endif

      @if($errors->any())
        <section class="dash-card dash-card--danger">
          <strong>Job type could not be added.</strong>
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
                <h2>Job types</h2>
                <p>Default job types are always available. Add custom types for a specific environment when needed.</p>
              </div>
            </div>

            @if($tenants->isEmpty())
              <div class="dash-empty">
                <h3>No environments yet</h3>
                <p>Create an environment before adding custom job types.</p>
                <div class="dash-actions">
                  <a class="dash-link" href="{{ route('client.environments.create') }}">Create environment</a>
                </div>
              </div>
            @else
              <form class="domain-form" method="POST" action="{{ route('client.jobs-settings.job-type.store') }}">
                @csrf

                <div class="domain-form__grid">
                  <label class="domain-field">
                    <span>Environment</span>
                    <select name="tenant_id" required>
                      @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}" @selected($selectedTenantId === $tenant->id)>
                          {{ $tenant->name }} ({{ $tenant->slug }})
                        </option>
                      @endforeach
                    </select>
                  </label>

                  <label class="domain-field">
                    <span>New job type</span>
                    <input
                      type="text"
                      name="name"
                      value="{{ old('name') }}"
                      placeholder="For example: Volunteer"
                      autocomplete="off"
                      required
                    >
                  </label>
                </div>

                <div class="dash-actions dash-actions--spaced">
                  <button class="dash-btn dash-btn--primary" type="submit">
                    <i class="ph ph-plus" aria-hidden="true"></i>
                    Add job type
                  </button>
                </div>
              </form>
            @endif
          </section>
        </main>

        <aside class="dash-form-layout__aside">
          <section class="dash-card dash-form-side">
            <h2>Default job types</h2>
            <p>Every environment always includes the standard job types below.</p>
            <div class="dash-actions dash-actions--spaced">
              @foreach($defaultJobTypes as $jobType)
                <span class="dash-status">{{ $jobType }}</span>
              @endforeach
            </div>
          </section>
        </aside>
      </div>

      <section class="dash-panel">
        <div class="dash-panel__head">
          <div>
            <h2>Available job types</h2>
            <p>These types can be used when jobs are created for each environment.</p>
          </div>
        </div>

        @if($tenants->isEmpty())
          <div class="dash-empty">
            <h3>No job types to show</h3>
            <p>Default job types will appear after you create an environment.</p>
          </div>
        @else
          <table class="dash-table">
            <thead>
              <tr>
                <th>Environment</th>
                <th>Default job types</th>
                <th>Custom job types</th>
              </tr>
            </thead>
            <tbody>
              @foreach($tenants as $tenant)
                @php
                  $customJobTypes = collect($jobTypesByTenant[$tenant->id] ?? []);
                @endphp
                <tr>
                  <td>
                    <span class="dash-cell-title">{{ $tenant->name }}</span>
                    <span class="dash-cell-meta">{{ $tenant->slug }}</span>
                  </td>
                  <td>
                    <div class="dash-actions">
                      @foreach($defaultJobTypes as $jobType)
                        <span class="dash-status">{{ $jobType }}</span>
                      @endforeach
                    </div>
                  </td>
                  <td>
                    @if($customJobTypes->isEmpty())
                      <span class="dash-status dash-status--muted">No custom types</span>
                    @else
                      <div class="dash-actions">
                        @foreach($customJobTypes as $jobType)
                          <span class="dash-status dash-status--accent">{{ $jobType }}</span>
                        @endforeach
                      </div>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </section>
@endsection
