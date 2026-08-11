@php
  $dashboardUser = auth()->user();
  $activeTenant = $activeTenant ?? ($tenant ?? null);

  if (! $activeTenant && $dashboardUser?->isTenantOwner()) {
    $activeTenant = $dashboardUser->ownedTenants()->oldest()->first();
  }

  $items = [
    ['label' => 'Dashboard', 'icon' => 'ph-squares-four', 'url' => route('tenant.owner.dashboard'), 'active' => request()->routeIs('tenant.owner.dashboard')],
    ['label' => 'Onboarding', 'icon' => 'ph-list-checks', 'url' => route('onboarding.index'), 'active' => request()->routeIs('onboarding.*')],
    ['label' => 'Billing', 'icon' => 'ph-credit-card', 'url' => route('billing.index'), 'active' => request()->routeIs('billing.*')],
    ['label' => 'Omgevingen', 'icon' => 'ph-globe-hemisphere-west', 'url' => route('tenant.environments.index'), 'active' => request()->routeIs('tenant.environments.*')],
  ];

  if ($activeTenant) {
    $items[] = ['label' => 'Vacatures', 'icon' => 'ph-briefcase', 'url' => route('tenant.jobs.index', $activeTenant), 'active' => request()->routeIs('tenant.jobs.*')];
    $items[] = ['label' => 'Sollicitaties', 'icon' => 'ph-users-three', 'url' => route('tenant.applications.index', $activeTenant), 'active' => request()->routeIs('tenant.applications.*')];
  }
@endphp

<aside class="dash-nav" aria-label="Dashboard navigatie">
  <div class="dash-nav__brand">
    <span>JB</span>
    <div>
      <strong>Beheer</strong>
      <small>{{ $dashboardUser?->company_name ?: 'JobBoardSoftware' }}</small>
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
