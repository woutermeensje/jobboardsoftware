@php
  $adminUser = auth()->user();

  $items = [
    ['label' => 'Platform management', 'icon' => 'ph-squares-four', 'url' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard')],
    ['label' => 'Users', 'icon' => 'ph-users', 'url' => route('admin.users.index'), 'active' => request()->routeIs('admin.users.*')],
    ['label' => 'Tenants', 'icon' => 'ph-buildings', 'url' => route('admin.tenants.index'), 'active' => request()->routeIs('admin.tenants.*')],
    ['label' => 'Domains', 'icon' => 'ph-globe', 'url' => route('admin.domains.index'), 'active' => request()->routeIs('admin.domains.*')],
    ['label' => 'Jobs', 'icon' => 'ph-briefcase', 'url' => route('admin.jobs.index'), 'active' => request()->routeIs('admin.jobs.*')],
    ['label' => 'Applications', 'icon' => 'ph-file-text', 'url' => route('admin.applications.index'), 'active' => request()->routeIs('admin.applications.*')],
    ['label' => 'Website', 'icon' => 'ph-house', 'url' => route('welcome'), 'active' => false],
  ];
@endphp

<div class="dash-nav__brand">
  <span>AD</span>
  <div>
    <strong>Admin</strong>
    <small>{{ $adminUser?->email ?: 'JobBoardSoftware' }}</small>
  </div>
</div>

<nav class="dash-nav__links" aria-label="Admin navigation">
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
    Log out
  </button>
</form>
