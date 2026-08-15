@extends('layouts.app')

@section('title', $section['title'].' | Client dashboard')
@section('meta_description', $section['description'])
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('client.partials.navigation')
@endsection

@section('content')
      <section class="dash-panel">
        <div class="dash-panel__head">
          <div>
            <h2>{{ $section['title'] }}</h2>
            <p>This screen uses your custom Blade stack. You can build the full interface here or split it into your own components.</p>
          </div>
        </div>
      </section>
@endsection
