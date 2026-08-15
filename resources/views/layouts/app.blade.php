@php
  $usesDashboardLayout = trim($__env->yieldContent('layout')) === 'dashboard';
  $dashboardUser = auth()->user();
  $dashboardBrandName = $dashboardBrandName ?? 'JobBoardSoftware';
  $dashboardBrandMark = $dashboardBrandMark ?? 'JB';
  $dashboardBrandUrl = $dashboardBrandUrl ?? route('welcome');
  $dashboardBrandAriaLabel = $dashboardBrandAriaLabel ?? $dashboardBrandName.' home';
  $dashboardLogoutUrl = $dashboardLogoutUrl ?? route('logout');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="@yield('meta_description', 'JobBoardSoftware is SaaS job board software for job boards, niche platforms and recruitment teams.')">
  <meta name="google-site-verification" content="Qy97RLjPFw4tZ2yDgVLny2_8SiM6eJ6FyHOhrsscBrw">

  <title>@yield('title', config('app.name', 'JobBoardSoftware'))</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@400;500;700&family=Work+Sans:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">

  @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite($usesDashboardLayout
      ? ['resources/css/app.css', 'resources/css/dashboard.css', 'resources/js/app.js']
      : ['resources/css/app.css', 'resources/css/header.css', 'resources/css/footer.css', 'resources/js/app.js']
    )
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
      --color-text-soft: #94a3b8;
      --color-primary: #3f7296;
      --color-primary-strong: #2f5f80;
      --color-primary-soft: rgba(63, 114, 150, 0.09);
      --color-primary-muted: rgba(63, 114, 150, 0.22);
      --color-accent: #d99a5b;
      --color-accent-strong: #a9652f;
      --color-accent-soft: rgba(217, 154, 91, 0.13);
      --font-ui: 'Inter', sans-serif;
      --font-text: 'Poppins', sans-serif;
      --font-heading: 'Inter', sans-serif;
      --shadow-sm: 0 1px 2px rgba(17, 24, 39, 0.05);
      --shadow-md: 0 14px 32px rgba(17, 24, 39, 0.08);
      --radius-sm: 6px;
      --radius-md: 5px;
      --radius-lg: 5px;
      --radius-default: 5px;
      --border-default: 1px solid var(--color-border);
    }

    *,
    *::before,
    *::after {
      box-sizing: border-box;
    }

    html,
    body {
      margin: 0;
      min-height: 100%;
      background: var(--color-bg);
      color: #333;
      font-family: var(--font-text);
      font-weight: 400;
      line-height: 1.6;
    }

    body.is-menu-open {
      overflow: hidden;
    }

    a {
      color: var(--color-primary);
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }

    button,
    input {
      font: inherit;
    }

    img,
    svg {
      display: block;
      max-width: 100%;
    }

    h1,
    h2,
    h3 {
      color: #333;
      font-family: var(--font-heading);
      font-weight: 600;
      letter-spacing: 0;
      line-height: 1.15;
    }

    .app-shell {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .app-main {
      flex: 1 0 auto;
    }

    .sr-only {
      position: absolute;
      width: 1px;
      height: 1px;
      padding: 0;
      margin: -1px;
      overflow: hidden;
      clip: rect(0, 0, 0, 0);
      white-space: nowrap;
      border: 0;
    }
  </style>

  @stack('styles')
</head>
<body class="{{ $usesDashboardLayout ? 'dashboard-body' : '' }}">
  @if($usesDashboardLayout)
    <div class="dashboard-app-shell">
      <header class="dashboard-topbar">
        <a class="dashboard-brand" href="{{ $dashboardBrandUrl }}" aria-label="{{ $dashboardBrandAriaLabel }}">
          <span class="dashboard-brand__mark" aria-hidden="true">{{ $dashboardBrandMark }}</span>
          <span class="dashboard-brand__name">{{ $dashboardBrandName }}</span>
        </a>

        <div class="dashboard-topbar__account">
          <div>
            <strong>{{ $dashboardUser?->name ?? 'Account' }}</strong>
            <span>{{ $dashboardUser?->email ?? 'Not signed in' }}</span>
          </div>
          @auth
            <form method="POST" action="{{ $dashboardLogoutUrl }}">
              @csrf
              <button type="submit" aria-label="Log out">
                <i class="ph ph-sign-out" aria-hidden="true"></i>
              </button>
            </form>
          @endauth
        </div>
      </header>

      <div class="dashboard-app-frame">
        <aside class="dashboard-sidebar dash-nav">
          @yield('dashboard_sidebar')
        </aside>

        <main class="dashboard-main">
          @yield('content')
        </main>
      </div>
    </div>
  @else
    <div class="app-shell">
      <x-header />

      <main class="app-main">
        @yield('content')
      </main>

      <x-footer />
    </div>
  @endif

  @stack('scripts')
</body>
</html>
