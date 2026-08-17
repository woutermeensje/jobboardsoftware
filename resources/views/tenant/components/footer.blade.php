@php
  $footerTenant = $tenant ?? null;
  $footerEmail = $footerTenant?->owner?->email;
  $lastUpdatedYear = $footerTenant?->updated_at?->format('Y') ?? now()->format('Y');
@endphp

<footer class="tenant-footer">
  <div class="tenant-footer__inner">
    <div class="tenant-footer__email">
      @if($footerEmail)
        <a href="mailto:{{ $footerEmail }}">{{ $footerEmail }}</a>
      @endif
    </div>

    <div class="tenant-footer__brand">
      <strong>{{ $brandName }}</strong>
      <span class="tenant-footer__updated" aria-label="Last updated in {{ $lastUpdatedYear }}">
        <i class="ph ph-calendar-check" aria-hidden="true"></i>
        <span>{{ $lastUpdatedYear }}</span>
      </span>
    </div>
  </div>
</footer>
