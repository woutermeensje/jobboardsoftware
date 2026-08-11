@php
  $adminUser = auth()->user();

  $items = [
    ['label' => 'Platformbeheer', 'icon' => 'ph-squares-four', 'url' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard')],
    ['label' => 'Website', 'icon' => 'ph-house', 'url' => route('welcome'), 'active' => false],
  ];
@endphp

<aside class="dash-nav" aria-label="Admin navigatie">
  <div class="dash-nav__brand">
    <span>AD</span>
    <div>
      <strong>Admin</strong>
      <small>{{ $adminUser?->email ?: 'JobBoardSoftware' }}</small>
    </div>
  </div>

  <nav class="dash-nav__links">
    @foreach($items as $item)
      <a class="{{ $item['active'] ? 'is-active' : '' }}" href="{{ $item['url'] }}">
        <i class="ph {{ $item['icon'] }}"></i>
        {{ $item['label'] }}
      </a>
    @endforeach
  </nav>

  <form method="POST" action="{{ route('logout') }}" class="dash-nav__logout">
    @csrf
    <button type="submit">
      <i class="ph ph-sign-out"></i>
      Uitloggen
    </button>
  </form>
</aside>
