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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&family=Work+Sans:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">

  @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/css/tenants/index.css', 'resources/js/app.js'])
  @endif

  <style>
    :root {
      --color-bg: #f7f6f2;
      --color-bg-white: #ffffff;
      --color-card: #ffffff;
      --color-border: #DEDEDE;
      --color-border-strong: #cbd5e1;
      --color-text: #333;
      --color-text-muted: #555;
      --color-text-subtle: #94a3b8;
      --color-primary: #3f7296;
      --color-primary-strong: #2f5f80;
      --color-primary-soft: rgba(63, 114, 150, 0.08);
      --color-primary-muted: rgba(63, 114, 150, 0.24);
      --color-accent: #d99a5b;
      --color-accent-strong: #a9652f;
      --color-accent-soft: rgba(217, 154, 91, 0.10);
      --font-ui: 'Inter', sans-serif;
      --font-text: 'Poppins', sans-serif;
      --font-heading: 'Inter', sans-serif;
      --shadow-sm: 0 1px 2px rgba(17, 24, 39, 0.05);
      --shadow-md: 0 14px 32px rgba(17, 24, 39, 0.08);
      --radius-default: 5px;
      --border-default: 1px solid var(--color-border);
      --field-bg: #ffffff;
      --field-border: #DEDEDE;
      --field-border-hover: #DEDEDE;
      --field-focus: #DEDEDE;
    }

    *,
    *::before,
    *::after {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      min-height: 100vh;
      background: var(--color-bg);
      color: var(--color-text);
      font-family: var(--font-text);
    }

    a {
      color: inherit;
    }
  </style>

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
