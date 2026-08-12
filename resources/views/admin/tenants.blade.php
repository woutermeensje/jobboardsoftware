@extends('layouts.app')

@section('title', 'Tenants | JobBoardSoftware admin')
@section('meta_description', 'Manage tenant job board environments.')

@php
  $tenantStatuses = [
    \App\Models\Tenant::STATUS_TRIAL => 'Trial',
    \App\Models\Tenant::STATUS_ACTIVE => 'Active',
    \App\Models\Tenant::STATUS_SUSPENDED => 'Suspended',
  ];
  $billingStatuses = ['trial' => 'Trial', 'active' => 'Active', 'past_due' => 'Past due', 'canceled' => 'Canceled'];
  $steps = ['domain' => 'Domain', 'jobs' => 'Jobs', 'complete' => 'Complete'];
@endphp

@section('content')
<section class="dash-page">
  <div class="dash-shell dash-app">
    @include('admin.partials.navigation')

    <div class="dash-content">
      <header class="dash-topbar">
        <div>
          <p class="dash-eyebrow">Admin</p>
          <h1 class="dash-title">Tenants</h1>
          <p class="dash-subtitle">Manage job board environments, package assignment, status and onboarding progress.</p>
        </div>
        <aside class="dash-user">
          <strong>{{ $tenants->count() }} tenants</strong>
          <span>{{ $user->email }}</span>
          <span>Environment management</span>
        </aside>
      </header>

      @if(session('status'))
        <section class="dash-card dash-card--success"><strong>{{ session('status') }}</strong></section>
      @endif

      <section class="dash-panel">
        <div class="dash-panel__head">
          <div>
            <h2>All environments</h2>
            <p>Changes apply to the central tenant record and are reflected in customer dashboards.</p>
          </div>
        </div>

        <table class="dash-table">
          <thead>
            <tr>
              <th>Tenant</th>
              <th>Owner</th>
              <th>Usage</th>
              <th>Domains</th>
              <th>Manage</th>
            </tr>
          </thead>
          <tbody>
            @forelse($tenants as $tenant)
              <tr>
                <td>
                  <span class="dash-cell-title">{{ $tenant->name }}</span>
                  <span class="dash-cell-meta">{{ $tenant->id }} - {{ $tenant->slug }}</span>
                </td>
                <td>
                  <span class="dash-cell-title">{{ $tenant->owner?->name ?? 'No owner' }}</span>
                  <span class="dash-cell-meta">{{ $tenant->owner?->email ?? 'Unknown' }}</span>
                </td>
                <td>
                  <span class="dash-cell-title">{{ $tenant->jobs_count }} jobs</span>
                  <span class="dash-cell-meta">{{ $tenant->applications_count }} applications</span>
                </td>
                <td>
                  @foreach($tenant->domains as $domain)
                    <span class="dash-cell-meta">{{ $domain->domain }}</span>
                  @endforeach
                </td>
                <td>
                  <form class="admin-table-form" method="POST" action="{{ route('admin.tenants.update', $tenant) }}">
                    @csrf
                    @method('PATCH')
                    <div class="admin-form-grid admin-form-grid--single">
                      <input class="form-control" name="name" value="{{ old('name', $tenant->name) }}" aria-label="Tenant name" required>
                    </div>
                    <div class="admin-form-grid admin-form-grid--three">
                      <select class="form-control" name="plan" aria-label="Plan" required>
                        @foreach($plans as $plan)
                          <option value="{{ $plan->key }}" @selected(old('plan', $tenant->plan) === $plan->key)>{{ $plan->name }}</option>
                        @endforeach
                      </select>
                      <select class="form-control" name="status" aria-label="Tenant status" required>
                        @foreach($tenantStatuses as $value => $label)
                          <option value="{{ $value }}" @selected(old('status', $tenant->status) === $value)>{{ $label }}</option>
                        @endforeach
                      </select>
                      <select class="form-control" name="billing_status" aria-label="Billing status" required>
                        @foreach($billingStatuses as $value => $label)
                          <option value="{{ $value }}" @selected(old('billing_status', $tenant->billing_status) === $value)>{{ $label }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="admin-form-grid">
                      <select class="form-control" name="onboarding_step" aria-label="Onboarding step" required>
                        @foreach($steps as $value => $label)
                          <option value="{{ $value }}" @selected(old('onboarding_step', $tenant->onboarding_step) === $value)>{{ $label }}</option>
                        @endforeach
                      </select>
                      <button class="dash-btn dash-btn--primary" type="submit">Save tenant</button>
                    </div>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="5">No tenants yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </section>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('dashboard.partials.styles')
  @include('admin.partials.styles')
@endpush
