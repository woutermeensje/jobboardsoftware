@php
  $clientUser = auth()->user();

  $items = [
    ['label' => 'Dashboard', 'icon' => 'ph-squares-four', 'url' => route('client.dashboard'), 'active' => request()->routeIs('client.dashboard')],
    ['label' => 'Environments', 'icon' => 'ph-buildings', 'url' => route('client.environments.index'), 'active' => request()->is('client/dashboard/environments*')],
    ['label' => 'Jobs', 'icon' => 'ph-briefcase', 'url' => route('client.jobs.index'), 'active' => request()->is('client/dashboard/jobs*')],
    ['label' => 'Domains', 'icon' => 'ph-globe', 'url' => route('client.domains.index'), 'active' => request()->is('client/dashboard/domains*')],
    ['label' => 'Applications', 'icon' => 'ph-file-text', 'url' => route('client.applications.index'), 'active' => request()->is('client/dashboard/applications*')],
    ['label' => 'Marketing', 'icon' => 'ph-megaphone', 'url' => route('client.marketing.index'), 'active' => request()->is('client/dashboard/marketing*')],
    ['label' => 'Jobs settings', 'icon' => 'ph-gear-six', 'url' => route('client.jobs-settings.index'), 'active' => request()->is('client/dashboard/jobs-settings*')],
    ['label' => 'Companies', 'icon' => 'ph-building-office', 'url' => route('client.companies.index'), 'active' => request()->is('client/dashboard/companies*')],
    ['label' => 'Billing', 'icon' => 'ph-credit-card', 'url' => route('client.billing'), 'active' => request()->routeIs('client.billing')],
    ['label' => 'Website', 'icon' => 'ph-house', 'url' => route('welcome'), 'active' => false],
  ];
@endphp

<div class="dash-nav__brand">
  <span>CL</span>
  <div>
    <strong>Client</strong>
    <small>{{ $clientUser?->email ?: 'JobBoardSoftware' }}</small>
  </div>
</div>

<nav class="dash-nav__links" aria-label="Client navigation">
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
