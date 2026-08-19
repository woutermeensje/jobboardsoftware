@php
  $items = [
    ['label' => 'My jobs', 'icon' => 'ph-briefcase', 'url' => route('tenant.employer.dashboard'), 'active' => request()->routeIs('tenant.employer.dashboard')],
    ['label' => 'My company page', 'icon' => 'ph-buildings', 'url' => route('tenant.employer.company'), 'active' => request()->routeIs('tenant.employer.company')],
    ['label' => 'CV Database', 'icon' => 'ph-database', 'url' => route('tenant.employer.cv-database'), 'active' => request()->routeIs('tenant.employer.cv-database')],
    ['label' => 'Applicants', 'icon' => 'ph-file-text', 'url' => route('tenant.employer.applicants'), 'active' => request()->routeIs('tenant.employer.applicants')],
    ['label' => 'My account', 'icon' => 'ph-user-circle', 'url' => route('tenant.employer.account'), 'active' => request()->routeIs('tenant.employer.account')],
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
