@extends('layouts.app')

@section('title', 'Job board not found')
@section('meta_description', 'The requested job board environment could not be found.')

@section('content')
  <section class="tenant-error">
    <div class="tenant-error__inner">
      <p class="tenant-error__eyebrow">404</p>
      <h1>Job board not found</h1>
      <p>
        We could not find an active job board for
        <strong>{{ $host }}</strong>.
      </p>
      <div class="tenant-error__actions">
        <a class="btn btn-primary" href="{{ route('welcome') }}">Go to JobBoardSoftware</a>
        <a class="tenant-error__link" href="{{ route('pages.contact') }}">Contact support</a>
      </div>
    </div>
  </section>
@endsection

@push('styles')
  @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite('resources/css/tenants/app.css')
  @endif
@endpush
