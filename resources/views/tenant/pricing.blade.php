@extends('layouts.tenant')

@section('title', 'Pricing | '.$brandName)
@section('meta_description', 'Job posting packages for this job board.')

@section('content')
  <main class="tenant-shell tenant-pricing-shell">
    <section class="tenant-pricing" aria-label="Job posting packages">
      @if($packages->isEmpty())
        <div class="tenant-pricing-empty">
          <h2>No packages available yet</h2>
          <p>Packages will appear here as soon as they are added by the job board owner.</p>
        </div>
      @else
        <div class="tenant-pricing-grid">
          @foreach($packages as $package)
            @php
              $currencySymbol = match ($package->currency) {
                'EUR' => '&euro;',
                'USD' => '$',
                'GBP' => '&pound;',
                default => e($package->currency),
              };
              $durationLabel = $package->online_days.' '.($package->online_days === 1 ? 'day' : 'days').' online';
            @endphp
            <article class="tenant-pricing-card">
              <div class="tenant-pricing-card__summary">
                <h2>{{ $package->name }}</h2>
                <p class="tenant-pricing-card__price">{!! $currencySymbol !!} {{ number_format((float) $package->price, 2) }}</p>
              </div>

              <a class="tenant-btn tenant-btn--primary" href="{{ route('tenant.post-job', ['package' => $package->id]) }}">
                Post a job
              </a>

              <p class="tenant-pricing-card__duration">
                <i class="ph ph-check" aria-hidden="true"></i>
                <span>{{ $durationLabel }}</span>
              </p>

              @if($package->description)
                <div class="tenant-pricing-card__description rich-text">
                  {!! $package->description !!}
                </div>
              @endif
            </article>
          @endforeach
        </div>
      @endif
    </section>
  </main>
@endsection
