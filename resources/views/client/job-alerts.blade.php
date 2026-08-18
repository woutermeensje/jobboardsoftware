@extends('layouts.app')

@section('title', 'Job alerts | Client dashboard')
@section('meta_description', 'Manage job alert subscriptions for your job board environments.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('client.partials.navigation')
@endsection

@section('content')
      <section class="dash-panel dash-panel--list">
        <div class="dash-panel__head">
          <div>
            <h2>Job alerts</h2>
            <p>Everyone subscribed to job alerts and the categories they're watching.</p>
          </div>
        </div>

        <div class="dash-panel__body">
          @if($alerts->isEmpty())
            <div class="dash-empty">
              <h3>No job alerts yet</h3>
              <p>Job alert sign-ups will appear here.</p>
            </div>
          @else
            <table class="dash-table">
              <thead>
                <tr>
                  <th>Email</th>
                  <th>Environment</th>
                  <th>Categories</th>
                  <th>Subscribed</th>
                </tr>
              </thead>
              <tbody>
                @foreach($alerts as $alert)
                  @php
                    $categories = collect($alert->employment_types ?? [])
                      ->merge($alert->departments ?? [])
                      ->merge($alert->sectors ?? [])
                      ->merge($alert->organization_types ?? [])
                      ->filter()
                      ->values();
                  @endphp
                  <tr>
                    <td><span class="dash-cell-title">{{ $alert->email }}</span></td>
                    <td>
                      <span class="dash-cell-title">{{ $alert->tenant?->name ?? $alert->tenant_id }}</span>
                      <span class="dash-cell-meta">{{ $alert->tenant?->slug }}</span>
                    </td>
                    <td>
                      @if($categories->isEmpty())
                        <span class="dash-cell-meta">All categories</span>
                      @else
                        {{ $categories->implode(', ') }}
                      @endif
                    </td>
                    <td>{{ $alert->created_at?->format('M j, Y') }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif
        </div>
      </section>
@endsection
