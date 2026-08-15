@extends('layouts.app')

@section('title', 'Domains | JobBoardSoftware admin')
@section('meta_description', 'Manage tenant domains and SSL status.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('admin.partials.navigation')
@endsection

@php
  $domainStatuses = [
    \App\Models\Domain::STATUS_PENDING => 'Pending',
    \App\Models\Domain::STATUS_VERIFIED => 'Verified',
    \App\Models\Domain::STATUS_ACTIVE => 'Active',
    \App\Models\Domain::STATUS_FAILED => 'Failed',
  ];
  $sslStatuses = [
    \App\Models\Domain::SSL_PENDING => 'Pending',
    \App\Models\Domain::SSL_ACTIVE => 'Active',
    \App\Models\Domain::SSL_FAILED => 'Failed',
  ];
@endphp

@section('content')
      @if(session('status'))
        <section class="dash-card dash-card--success"><strong>{{ session('status') }}</strong></section>
      @endif

      <section class="dash-panel">
        <div class="dash-panel__head">
          <div>
            <h2>All domains</h2>
            <p>Mark domains as verified or active when DNS and certificates are ready.</p>
          </div>
        </div>

        <table class="dash-table">
          <thead>
            <tr>
              <th>Domain</th>
              <th>Tenant</th>
              <th>Status</th>
              <th>Verification</th>
              <th>Manage</th>
            </tr>
          </thead>
          <tbody>
            @forelse($domains as $domain)
              <tr>
                <td>
                  <span class="dash-cell-title">{{ $domain->domain }}</span>
                  <span class="dash-cell-meta">{{ $domain->is_primary ? 'Primary' : 'Additional' }}</span>
                </td>
                <td>
                  <span class="dash-cell-title">{{ $domain->tenant?->name ?? $domain->tenant_id }}</span>
                  <span class="dash-cell-meta">{{ $domain->tenant?->owner?->email ?? 'Unknown owner' }}</span>
                </td>
                <td>
                  <span class="dash-status">{{ ucfirst($domain->status) }}</span>
                  <span class="dash-cell-meta">SSL: {{ ucfirst($domain->ssl_status) }}</span>
                </td>
                <td>
                  <span class="dash-cell-meta">Verified: {{ $domain->verified_at?->format('Y-m-d H:i') ?? 'No' }}</span>
                  <span class="dash-cell-meta">SSL issued: {{ $domain->ssl_issued_at?->format('Y-m-d H:i') ?? 'No' }}</span>
                </td>
                <td>
                  <form class="admin-table-form" method="POST" action="{{ route('admin.domains.update', $domain) }}">
                    @csrf
                    @method('PATCH')
                    <div class="admin-form-grid">
                      <select class="form-control" name="status" aria-label="DNS status" required>
                        @foreach($domainStatuses as $value => $label)
                          <option value="{{ $value }}" @selected(old('status', $domain->status) === $value)>{{ $label }}</option>
                        @endforeach
                      </select>
                      <select class="form-control" name="ssl_status" aria-label="SSL status" required>
                        @foreach($sslStatuses as $value => $label)
                          <option value="{{ $value }}" @selected(old('ssl_status', $domain->ssl_status) === $value)>{{ $label }}</option>
                        @endforeach
                      </select>
                    </div>
                    <label class="admin-switch">
                      <input type="checkbox" name="is_primary" value="1" @checked(old('is_primary', $domain->is_primary))>
                      Primary domain
                    </label>
                    <button class="dash-btn dash-btn--primary" type="submit">Save domain</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="5">No domains yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </section>
@endsection
