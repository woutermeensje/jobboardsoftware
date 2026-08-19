@extends('layouts.app')

@section('title', 'My jobs | '.$brandName)
@section('meta_description', 'Manage the jobs posted by your employer account.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('tenant.dashboards.partials.employer-navigation')
@endsection

@section('content')
    @if(session('status'))
      <section class="dash-card dash-card--success"><strong>{{ session('status') }}</strong></section>
    @endif

    <div class="dash-panel__head">
      <div></div>
      <a class="dash-btn dash-btn--primary" href="{{ route('tenant.post-job') }}">
        <i class="ph ph-plus"></i>
        Post job
      </a>
    </div>
@endsection
