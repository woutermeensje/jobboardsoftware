@php
  $tenantSettings = isset($tenant) ? ($tenant->settings ?? []) : [];
  $tenantBrandName = $tenantSettings['brand_name'] ?? $tenant->name ?? 'Jobboard';
  $tenantPrimary = $tenantSettings['primary_color'] ?? $tenantSettings['accent_color'] ?? '#2f5f80';
  $tenantAccent = $tenantSettings['accent_color'] ?? $tenantPrimary;

  $tenantPrimary = is_string($tenantPrimary) && preg_match('/^#[0-9a-fA-F]{6}$/', $tenantPrimary) ? $tenantPrimary : '#2f5f80';
  $tenantAccent = is_string($tenantAccent) && preg_match('/^#[0-9a-fA-F]{6}$/', $tenantAccent) ? $tenantAccent : $tenantPrimary;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="@yield('meta_description', 'Jobs and applications through a tenant job board.')">

  <title>@yield('title', 'Jobboard')</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@700&family=Work+Sans:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">

  @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/css/tenants/index.css', 'resources/js/app.js'])
  @endif

  @stack('styles')
</head>
<body
  class="tenant-body"
  style="--tenant-primary: {{ $tenantPrimary }}; --tenant-accent: {{ $tenantAccent }};"
>
  <section class="tenant-page">
    @include('tenant.components.header', [
      'brandName' => $tenantBrandName,
    ])

    @yield('content')

    @include('tenant.components.footer', [
      'brandName' => $tenantBrandName,
    ])
  </section>

  @stack('scripts')
</body>
</html>
