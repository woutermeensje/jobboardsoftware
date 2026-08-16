@php
  $authStyleAssets = ['resources/css/login.css'];

  if (($layout ?? 'layouts.app') === 'layouts.tenant') {
    $authStyleAssets[] = 'resources/css/tenants/login.css';
  }
@endphp

@if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
  @vite($authStyleAssets)
@endif
