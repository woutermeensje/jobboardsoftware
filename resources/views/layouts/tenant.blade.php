<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="@yield('meta_description', 'Vacatures en sollicitaties via een tenant jobboard.')">

  <title>@yield('title', 'Jobboard')</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&family=Work+Sans:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">

  @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  @endif

  <style>
    :root {
      --color-bg: #f7f6f2;
      --color-bg-white: #ffffff;
      --color-card: #ffffff;
      --color-border: #dfe7ee;
      --color-border-strong: #c8d7e3;
      --color-text: #27313a;
      --color-text-muted: #5f6f7a;
      --color-primary-strong: #2f5f80;
      --font-ui: 'Inter', sans-serif;
      --font-text: 'Poppins', sans-serif;
      --font-heading: 'Work Sans', sans-serif;
      --shadow-sm: 0 1px 2px rgba(17, 24, 39, 0.05);
      --shadow-md: 0 14px 32px rgba(17, 24, 39, 0.08);
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
<body>
  @yield('content')
  @stack('scripts')
</body>
</html>
