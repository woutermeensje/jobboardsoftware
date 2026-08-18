@extends('layouts.app')

@section('title', 'Newsletter subscribers | Client dashboard')
@section('meta_description', 'Manage newsletter subscribers for your job board environments.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('client.partials.navigation')
@endsection

@section('content')
      <section class="dash-panel dash-panel--list">
        <div class="dash-panel__head">
          <div>
            <h2>Newsletter subscribers</h2>
            <p>Everyone who signed up for the newsletter across your job board environments.</p>
          </div>
        </div>

        <div class="dash-panel__body">
          @if($subscribers->isEmpty())
            <div class="dash-empty">
              <h3>No subscribers yet</h3>
              <p>Newsletter sign-ups will appear here.</p>
            </div>
          @else
            <table class="dash-table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Environment</th>
                  <th>Subscribed</th>
                </tr>
              </thead>
              <tbody>
                @foreach($subscribers as $subscriber)
                  <tr>
                    <td><span class="dash-cell-title">{{ $subscriber->first_name ?: 'No name provided' }}</span></td>
                    <td>{{ $subscriber->email }}</td>
                    <td>
                      <span class="dash-cell-title">{{ $subscriber->tenant?->name ?? $subscriber->tenant_id }}</span>
                      <span class="dash-cell-meta">{{ $subscriber->tenant?->slug }}</span>
                    </td>
                    <td>{{ $subscriber->created_at?->format('M j, Y') }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif
        </div>
      </section>
@endsection
