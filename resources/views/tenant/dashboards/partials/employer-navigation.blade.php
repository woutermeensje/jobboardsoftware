@php
  $items = [
    ['label' => 'Dashboard', 'icon' => 'ph-squares-four', 'url' => route('tenant.employer.dashboard'), 'active' => request()->routeIs('tenant.employer.dashboard')],
    ['label' => 'Jobs', 'icon' => 'ph-briefcase', 'url' => route('tenant.employer.dashboard').'#jobs', 'active' => false],
    ['label' => 'Applications', 'icon' => 'ph-file-text', 'url' => route('tenant.employer.dashboard').'#applications', 'active' => false],
    ['label' => 'Company profile', 'icon' => 'ph-buildings', 'url' => route('tenant.employer.dashboard').'#company', 'active' => false],
    ['label' => 'Job board', 'icon' => 'ph-house', 'url' => route('tenant.home'), 'active' => false],
  ];
@endphp

<nav class="dash-nav__links" aria-label="Employer navigation">
  @foreach($items as $item)
    <a class="{{ $item['active'] ? 'is-active' : '' }}" href="{{ $item['url'] }}">
      <i class="ph {{ $item['icon'] }}"></i>
      {{ $item['label'] }}
    </a>
  @endforeach
</nav>

<form method="POST" action="{{ route('tenant.logout') }}" class="dash-nav__logout">
  @csrf
  <button type="submit">
    <i class="ph ph-sign-out"></i>
    Log out
  </button>
</form>
