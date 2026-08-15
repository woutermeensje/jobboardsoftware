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
  <style>
    .tenant-error {
      min-height: 54vh;
      display: grid;
      place-items: center;
      padding: clamp(56px, 9vw, 120px) 24px;
      background: linear-gradient(180deg, #f7f6f2 0%, #ffffff 100%);
    }

    .tenant-error__inner {
      width: min(100%, 680px);
      text-align: center;
    }

    .tenant-error__eyebrow {
      margin: 0 0 12px;
      color: var(--color-accent-strong);
      font-family: var(--font-ui);
      font-size: 0.78rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
    }

    .tenant-error h1 {
      margin: 0;
      font-size: clamp(2.2rem, 5vw, 4.2rem);
    }

    .tenant-error p {
      margin: 18px auto 0;
      max-width: 520px;
      color: var(--color-text-muted);
      font-size: 1rem;
    }

    .tenant-error strong {
      color: var(--color-text);
      font-weight: 600;
      overflow-wrap: anywhere;
    }

    .tenant-error__actions {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 14px;
      margin-top: 32px;
    }

    .tenant-error__link {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 46px;
      padding: 0 8px;
      border-radius: var(--radius-default);
      font-family: var(--font-ui);
      font-weight: 700;
      color: var(--color-primary-strong);
    }
  </style>
@endpush
