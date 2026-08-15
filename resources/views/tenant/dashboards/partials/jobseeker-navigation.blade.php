@php
  $items = [
    ['label' => 'Dashboard', 'icon' => 'ph-squares-four', 'url' => route('tenant.jobseeker.dashboard'), 'active' => request()->routeIs('tenant.jobseeker.dashboard')],
    ['label' => 'Search jobs', 'icon' => 'ph-magnifying-glass', 'url' => route('tenant.jobs'), 'active' => false],
    ['label' => 'Applications', 'icon' => 'ph-file-text', 'url' => route('tenant.jobseeker.dashboard').'#applications', 'active' => false],
    ['label' => 'Profile', 'icon' => 'ph-user-circle', 'url' => route('tenant.jobseeker.dashboard').'#profile', 'active' => false],
    ['label' => 'Job board', 'icon' => 'ph-house', 'url' => route('tenant.home'), 'active' => false],
  ];
@endphp

<nav class="dash-nav__links" aria-label="Job seeker navigation">
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
