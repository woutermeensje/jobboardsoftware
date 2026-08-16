@extends('layouts.tenant')

@section('title', 'Pricing | '.$brandName)
@section('meta_description', 'Job posting packages for this job board.')

@section('content')
  <main class="tenant-shell">
    <section class="tenant-panel tenant-pricing" aria-labelledby="tenant-pricing-title">
      <div class="tenant-panel__head tenant-pricing__head">
        <p class="tenant-eyebrow">Pricing</p>
        <h1 id="tenant-pricing-title">Choose your package</h1>
        <p>Pick the package that fits your vacancy and continue to the job submission form.</p>
      </div>

      @if($packages->isEmpty())
        <div class="tenant-pricing-empty">
          <h2>No packages available yet</h2>
          <p>Packages will appear here as soon as they are added by the job board owner.</p>
        </div>
      @else
        <div class="tenant-pricing-grid">
          @foreach($packages as $package)
            <article class="tenant-pricing-card">
              <div>
                <h2>{{ $package->name }}</h2>
                <p>{{ $package->online_days }} {{ $package->online_days === 1 ? 'day' : 'days' }} online</p>
              </div>

              <strong>{{ $package->currency }} {{ number_format((float) $package->price, 2) }}</strong>

              <a class="tenant-btn tenant-btn--primary" href="{{ route('tenant.post-job') }}">
                Choose package
              </a>
            </article>
          @endforeach
        </div>
      @endif
    </section>
  </main>
@endsection
