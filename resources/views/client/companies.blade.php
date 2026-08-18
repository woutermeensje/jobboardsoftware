@extends('layouts.app')

@section('title', 'Companies | Client dashboard')
@section('meta_description', 'Manage companies for job board environments.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('client.partials.navigation')
@endsection

@section('content')
      @if(session('status'))
        <section class="dash-card dash-card--success">
          {{ session('status') }}
        </section>
      @endif

      <section class="dash-panel dash-panel--list">
        <div class="dash-panel__head">
          <div>
            <h2>Companies</h2>
            <p>Manage company profiles and logos for your job board environments.</p>
          </div>
          <a class="dash-link" href="{{ route('client.companies.create') }}">Create company</a>
        </div>

        <div class="dash-panel__body">
          <table class="dash-table">
            <thead>
              <tr>
                <th>Company</th>
                <th>Environment</th>
                <th>Contact</th>
                <th>Created</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($companies as $company)
                @php
                  $contactName = $company->contact_name ?: trim(collect([$company->contact_first_name, $company->contact_last_name])->filter()->implode(' '));
                  $logoUrl = \App\Support\PublicUploadStorage::url($company->logo_path);
                  $companyClassification = collect([
                    ...$company->sectorValues(),
                    ...$company->organizationTypeValues(),
                  ])->filter()->implode(' - ');
                @endphp
                <tr>
                  <td>
                    <div class="company-cell">
                      @if($logoUrl)
                        <img class="company-logo-thumb" src="{{ $logoUrl }}" alt="{{ $company->name }} logo">
                      @else
                        <span class="company-logo-thumb company-logo-thumb--empty" aria-hidden="true">{{ mb_strtoupper(mb_substr($company->name, 0, 1)) }}</span>
                      @endif
                      <div>
                        <a class="dash-cell-title dash-cell-title--link" href="{{ route('client.companies.edit', $company) }}">{{ $company->name }}</a>
                        <span class="dash-cell-meta">{{ $company->organization_name ?: 'No organization name added' }}</span>
                        @if($companyClassification !== '')
                          <span class="dash-cell-meta">{{ $companyClassification }}</span>
                        @endif
                      </div>
                    </div>
                  </td>
                  <td>{{ $company->tenant?->name ?? $company->tenant_id }}</td>
                  <td>
                    <span class="dash-cell-title">{{ $contactName !== '' ? $contactName : 'No contact person' }}</span>
                    <span class="dash-cell-meta">{{ $company->contact_email ?: 'No email address' }}</span>
                  </td>
                  <td>{{ $company->created_at?->format('M j, Y') }}</td>
                  <td><a class="dash-link" href="{{ route('client.companies.edit', $company) }}">Edit</a></td>
                </tr>
              @empty
                <tr><td colspan="5">No companies yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </section>
@endsection
