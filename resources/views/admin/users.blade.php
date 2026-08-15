@extends('layouts.app')

@section('title', 'Users | JobBoardSoftware admin')
@section('meta_description', 'Manage SaaS users and admin accounts.')
@section('layout', 'dashboard')
@section('dashboard_label', 'Admin')
@section('dashboard_title', 'Users')
@section('dashboard_subtitle', 'Manage roles, package selection, billing status and onboarding progress.')
@section('dashboard_sidebar')
  @include('admin.partials.navigation')
@endsection

@php
  $roles = [
    \App\Models\User::ROLE_TENANT_OWNER => 'Tenant owner',
    \App\Models\User::ROLE_ADMIN => 'Admin',
    \App\Models\User::ROLE_WERKGEVER => 'Werkgever',
    \App\Models\User::ROLE_WERKZOEKENDE => 'Werkzoekende',
  ];
  $billingStatuses = ['trial' => 'Trial', 'active' => 'Active', 'past_due' => 'Past due', 'canceled' => 'Canceled'];
  $steps = ['plan' => 'Plan', 'environment' => 'Environment', 'domain' => 'Domain', 'jobs' => 'Jobs', 'billing' => 'Billing', 'complete' => 'Complete'];
@endphp

@section('content')
      @if(session('status'))
        <section class="dash-card dash-card--success"><strong>{{ session('status') }}</strong></section>
      @endif

      <section class="dash-panel">
        <div class="dash-panel__head">
          <div>
            <h2>All users</h2>
            <p>Admin role changes are protected so you cannot remove your own admin access.</p>
          </div>
        </div>

        <table class="dash-table">
          <thead>
            <tr>
              <th>User</th>
              <th>Role</th>
              <th>Package</th>
              <th>Tenants</th>
              <th>Manage</th>
            </tr>
          </thead>
          <tbody>
            @forelse($users as $managedUser)
              <tr>
                <td>
                  <span class="dash-cell-title">{{ $managedUser->name }}</span>
                  <span class="dash-cell-meta">{{ $managedUser->email }}</span>
                  <span class="dash-cell-meta">{{ $managedUser->company_name ?: 'No company' }}</span>
                </td>
                <td><span class="dash-status">{{ $roles[$managedUser->role] ?? $managedUser->role }}</span></td>
                <td>
                  <span class="dash-cell-title">{{ $managedUser->billingPlan?->name ?? 'No package' }}</span>
                  <span class="dash-cell-meta">{{ ucfirst($managedUser->billing_status ?? 'trial') }} - {{ ucfirst($managedUser->onboarding_step ?? 'plan') }}</span>
                </td>
                <td>{{ $managedUser->owned_tenants_count }}</td>
                <td>
                  <form class="admin-table-form" method="POST" action="{{ route('admin.users.update', $managedUser) }}">
                    @csrf
                    @method('PATCH')
                    <div class="admin-form-grid">
                      <input class="form-control" name="name" value="{{ old('name', $managedUser->name) }}" aria-label="Name" required>
                      <input class="form-control" name="company_name" value="{{ old('company_name', $managedUser->company_name) }}" aria-label="Company name" placeholder="Company">
                    </div>
                    <div class="admin-form-grid admin-form-grid--three">
                      <select class="form-control" name="role" aria-label="Role" required>
                        @foreach($roles as $value => $label)
                          <option value="{{ $value }}" @selected(old('role', $managedUser->role) === $value)>{{ $label }}</option>
                        @endforeach
                      </select>
                      <select class="form-control" name="billing_plan_id" aria-label="Package">
                        <option value="">No package</option>
                        @foreach($plans as $plan)
                          <option value="{{ $plan->id }}" @selected((string) old('billing_plan_id', $managedUser->billing_plan_id) === (string) $plan->id)>{{ $plan->name }}</option>
                        @endforeach
                      </select>
                      <select class="form-control" name="billing_status" aria-label="Billing status" required>
                        @foreach($billingStatuses as $value => $label)
                          <option value="{{ $value }}" @selected(old('billing_status', $managedUser->billing_status) === $value)>{{ $label }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="admin-form-grid">
                      <select class="form-control" name="onboarding_step" aria-label="Onboarding step" required>
                        @foreach($steps as $value => $label)
                          <option value="{{ $value }}" @selected(old('onboarding_step', $managedUser->onboarding_step) === $value)>{{ $label }}</option>
                        @endforeach
                      </select>
                      <button class="dash-btn dash-btn--primary" type="submit">Save user</button>
                    </div>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="5">No users yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </section>
@endsection
