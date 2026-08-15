@extends('layouts.app')

@section('title', $section['title'].' | Client dashboard')
@section('meta_description', $section['description'])
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('client.partials.navigation')
@endsection

@php
  $usesFormLayout = ($section['layout'] ?? null) === 'form';
@endphp

@section('content')
      @if($usesFormLayout)
        <div class="dash-form-layout">
          <main class="dash-form-layout__main">
            <section class="dash-panel">
              <div class="dash-panel__head">
                <div>
                  <h2>{{ $section['title'] }}</h2>
                  <p>{{ $section['description'] }}</p>
                </div>
              </div>

              <div class="dash-empty-state">
                <h3>{{ $section['title'] }} form</h3>
                <p>The form fields for this section will use this main content area.</p>
              </div>
            </section>
          </main>

          <aside class="dash-form-layout__aside">
            <section class="dash-card dash-form-side">
              <h2>{{ $section['aside_title'] ?? $section['title'].' details' }}</h2>
              <p>{{ $section['aside_description'] ?? $section['description'] }}</p>
            </section>
          </aside>
        </div>
      @else
        <section class="dash-panel">
          <div class="dash-panel__head">
            <div>
              <h2>{{ $section['title'] }}</h2>
              <p>{{ $section['description'] }}</p>
            </div>
          </div>
        </section>
      @endif
@endsection
