@extends('layouts.app')

@section('title', 'CV Database | '.$brandName)
@section('meta_description', 'Search and browse the CV database.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('tenant.dashboards.partials.employer-navigation')
@endsection

@section('content')
    @if(session('status'))
      <section class="dash-card dash-card--success"><strong>{{ session('status') }}</strong></section>
    @endif

    <section class="dash-panel" aria-labelledby="cv-database-title">
      <div class="dash-panel__head">
        <div>
          <h2 id="cv-database-title">CV Database</h2>
          <p>Search candidate CVs. This feature is coming soon.</p>
        </div>
      </div>
    </section>
@endsection
