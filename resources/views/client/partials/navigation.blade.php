@php
  $items = [
    ['label' => 'Dashboard', 'icon' => 'ph-squares-four', 'url' => route('client.dashboard'), 'active' => request()->routeIs('client.dashboard')],
    [
      'label' => 'Environments',
      'icon' => 'ph-buildings',
      'url' => route('client.environments.index'),
      'active' => request()->routeIs('client.environments.*'),
      'children' => [
        ['label' => 'Create environment', 'url' => route('client.environments.create'), 'active' => request()->routeIs('client.environments.create')],
      ],
    ],
    [
      'label' => 'Jobs',
      'icon' => 'ph-briefcase',
      'url' => route('client.jobs.index'),
      'active' => request()->routeIs('client.jobs.*'),
      'children' => [
        ['label' => 'Create job', 'url' => route('client.jobs.create'), 'active' => request()->routeIs('client.jobs.create')],
      ],
    ],
    [
      'label' => 'Domains',
      'icon' => 'ph-globe',
      'url' => route('client.domains.index'),
      'active' => request()->routeIs('client.domains.*'),
      'children' => [
        ['label' => 'Add domain', 'url' => route('client.domains.create'), 'active' => request()->routeIs('client.domains.create')],
      ],
    ],
    [
      'label' => 'Marketing',
      'icon' => 'ph-megaphone',
      'url' => route('client.marketing.index'),
      'active' => request()->routeIs('client.marketing.*'),
      'children' => [
        ['label' => 'Landing pages', 'url' => route('client.marketing.landingpagina'), 'active' => request()->routeIs('client.marketing.landingpagina')],
        ['label' => 'Social channels', 'url' => route('client.marketing.socials'), 'active' => request()->routeIs('client.marketing.socials')],
      ],
    ],
    [
      'label' => 'Jobs settings',
      'icon' => 'ph-gear-six',
      'url' => route('client.jobs-settings.index'),
      'active' => request()->routeIs('client.jobs-settings.*'),
      'children' => [
        ['label' => 'Sectors', 'url' => route('client.jobs-settings.sector'), 'active' => request()->routeIs('client.jobs-settings.sector')],
        ['label' => 'Categories', 'url' => route('client.jobs-settings.categorie'), 'active' => request()->routeIs('client.jobs-settings.categorie')],
        ['label' => 'Job types', 'url' => route('client.jobs-settings.job-type'), 'active' => request()->routeIs('client.jobs-settings.job-type')],
        ['label' => 'Organization types', 'url' => route('client.jobs-settings.organization-type'), 'active' => request()->routeIs('client.jobs-settings.organization-type')],
      ],
    ],
    [
      'label' => 'Companies',
      'icon' => 'ph-building-office',
      'url' => route('client.companies.index'),
      'active' => request()->routeIs('client.companies.*'),
      'children' => [
        ['label' => 'Create company', 'url' => route('client.companies.create'), 'active' => request()->routeIs('client.companies.create')],
      ],
    ],
    ['label' => 'Settings', 'icon' => 'ph-sliders-horizontal', 'url' => route('client.settings'), 'active' => request()->routeIs('client.settings*')],
    ['label' => 'Billing', 'icon' => 'ph-credit-card', 'url' => route('client.billing'), 'active' => request()->routeIs('client.billing')],
    [
      'label' => 'My packages',
      'icon' => 'ph-package',
      'url' => route('client.packages.index'),
      'active' => request()->routeIs('client.packages.*'),
      'children' => [
        ['label' => 'Add packages', 'url' => route('client.packages.create'), 'active' => request()->routeIs('client.packages.create')],
      ],
    ],
  ];
@endphp

<nav class="dash-nav__links" aria-label="Client navigation">
  @foreach($items as $item)
    <div class="dash-nav__item {{ $item['active'] ? 'is-open' : '' }}">
      <a class="dash-nav__link {{ $item['active'] ? 'is-active' : '' }}" href="{{ $item['url'] }}">
        <i class="ph {{ $item['icon'] }}"></i>
        {{ $item['label'] }}
      </a>

      @if(! empty($item['children']))
        <div class="dash-nav__sub" aria-label="{{ $item['label'] }} sections">
          @foreach($item['children'] as $child)
            <a class="dash-nav__sub-link {{ $child['active'] ? 'is-active' : '' }}" href="{{ $child['url'] }}">
              {{ $child['label'] }}
            </a>
          @endforeach
        </div>
      @endif
    </div>
  @endforeach
</nav>

<form method="POST" action="{{ route('logout') }}" class="dash-nav__logout">
  @csrf
  <button type="submit">
    <i class="ph ph-sign-out"></i>
    Log out
  </button>
</form>
