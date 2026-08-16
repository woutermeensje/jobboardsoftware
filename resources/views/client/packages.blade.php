@extends('layouts.app')

@section('title', 'My packages | Client dashboard')
@section('meta_description', 'Manage job posting packages for job board environments.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('client.partials.navigation')
@endsection

@section('content')
      @if(session('status'))
        <section class="dash-card dash-card--success"><strong>{{ session('status') }}</strong></section>
      @endif

      <section class="dash-panel">
        <div class="dash-panel__head">
          <div>
            <h2>My packages</h2>
            <p>These packages are available on the post-a-job form and pricing page for each environment.</p>
          </div>
          <a class="dash-link" href="{{ route('client.packages.create') }}">Add packages</a>
        </div>

        @if(! $packageTableReady)
          <div class="dash-empty">
            <h3>Package setup is not ready yet</h3>
            <p>Run the latest database migrations before adding packages.</p>
          </div>
        @elseif($packages->isEmpty())
          <div class="dash-empty">
            <h3>No packages yet</h3>
            <p>Add your first package to make it available on the post-a-job form.</p>
            @if($tenants->isEmpty())
              <div class="dash-actions">
                <a class="dash-link" href="{{ route('client.environments.create') }}">Create environment</a>
              </div>
            @else
              <div class="dash-actions">
                <a class="dash-link" href="{{ route('client.packages.create') }}">Add packages</a>
              </div>
            @endif
          </div>
        @else
          <table class="dash-table">
            <thead>
              <tr>
                <th>Environment</th>
                <th>Package</th>
                <th>Price</th>
                <th>Days online</th>
              </tr>
            </thead>
            <tbody>
              @foreach($packages as $package)
                <tr>
                  <td>
                    <span class="dash-cell-title">{{ $package->tenant->name }}</span>
                    <span class="dash-cell-meta">{{ $package->tenant->slug }}</span>
                  </td>
                  <td>{{ $package->name }}</td>
                  <td>{{ $package->currency }} {{ number_format((float) $package->price, 2) }}</td>
                  <td>{{ $package->online_days }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </section>
@endsection
