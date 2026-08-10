<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="@yield('meta_description', 'JobBoardSoftware is SaaS job board software voor vacaturebanken, niche platforms en recruitment teams.')">

  <title>@yield('title', config('app.name', 'JobBoardSoftware'))</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@700&family=Work+Sans:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">

  @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  @endif

  <style>
    :root {
      --color-bg: #f7f9fb;
      --color-bg-white: #ffffff;
      --color-card: #ffffff;
      --color-border: #dfe7ee;
      --color-border-strong: #c8d7e3;
      --color-text: #27313a;
      --color-text-muted: #5f6f7a;
      --color-text-soft: #8a9aa6;
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
      --radius-md: 8px;
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
      color: var(--color-text);
      font-family: var(--font-text);
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
      color: var(--color-text);
      font-family: var(--font-heading);
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
<body>
  <div class="app-shell">
    @include('layouts.navigation-public')

    <main class="app-main">
      @yield('content')
    </main>

    @include('layouts.footer')
  </div>

  @stack('scripts')
</body>
</html>
