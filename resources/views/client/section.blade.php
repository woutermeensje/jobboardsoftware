@extends('layouts.app')

@section('title', $section['title'].' | Workspace')
@section('meta_description', $section['description'])

@section('content')
<section class="dash-page">
  <div class="dash-shell dash-app">
    @include('client.partials.navigation')

    <div class="dash-content">
      <header class="dash-topbar">
        <div>
          <p class="dash-eyebrow">Client dashboard</p>
          <h1 class="dash-title">{{ $section['title'] }}</h1>
          <p class="dash-subtitle">{{ $section['description'] }}</p>
        </div>
        <aside class="dash-user">
          <strong>{{ $user->name }}</strong>
          <span>{{ $user->email }}</span>
          <span>Custom dashboard</span>
        </aside>
      </header>

      <section class="dash-panel">
        <div class="dash-panel__head">
          <div>
            <h2>{{ $section['title'] }}</h2>
            <p>This screen uses your custom Blade stack. You can build the full interface here or split it into your own components.</p>
          </div>
        </div>
      </section>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('dashboard.partials.styles')
  @include('admin.partials.styles')
@endpush
