@extends('layouts.tenant')

@section('title', $landingPage->title.' | '.($tenant->name ?? 'Jobboard'))
@section('meta_description', $landingPage->meta_description ?: \Illuminate\Support\Str::limit(trim(strip_tags($landingPage->content)), 160))

@section('content')
  <main class="tenant-shell">
    <article class="tenant-panel tenant-landing-page">
      <h1>{{ $landingPage->title }}</h1>

      <div class="rich-text">
        {!! $landingPage->content !!}
      </div>
    </article>
  </main>
@endsection
