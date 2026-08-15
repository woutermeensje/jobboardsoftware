@php
  $brandInitial = mb_substr((string) $brandName, 0, 1);
@endphp

<header class="tenant-header">
  <a class="tenant-brand" href="{{ route('tenant.home') }}">
    <span class="tenant-brand__mark">{{ $brandInitial }}</span>
    <strong class="tenant-brand__name">{{ $brandName }}</strong>
  </a>

  <nav class="tenant-navigation" aria-label="Jobboard navigation">
    <a href="{{ route('tenant.jobs') }}">Jobs</a>
    <a href="{{ route('tenant.contact') }}">Contact</a>
  </nav>
</header>
