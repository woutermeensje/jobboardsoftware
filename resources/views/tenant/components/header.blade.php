@php
  $brandInitial = mb_substr((string) $brandName, 0, 1);
  $settings = isset($tenant) ? ($tenant->settings ?? []) : [];
  $logoUrl = $settings['logo_url'] ?? null;

  if (! $logoUrl && ! empty($settings['logo_path'])) {
    $logoUrl = asset('storage/'.ltrim((string) $settings['logo_path'], '/'));
  }

  $primaryNav = [
    [
      'label' => 'Jobs',
      'url' => route('tenant.jobs'),
      'active' => request()->routeIs('tenant.home', 'tenant.jobs', 'tenant.jobs.show'),
      'children' => [
        ['label' => 'All jobs', 'url' => route('tenant.jobs'), 'active' => request()->routeIs('tenant.home', 'tenant.jobs')],
        ['label' => 'Contact', 'url' => route('tenant.contact'), 'active' => request()->routeIs('tenant.contact')],
      ],
    ],
    ['label' => 'Pricing', 'url' => route('tenant.pricing'), 'active' => request()->routeIs('tenant.pricing')],
    ['label' => 'Contact', 'url' => route('tenant.contact'), 'active' => request()->routeIs('tenant.contact')],
  ];

  $utilityLinks = [
    ['label' => 'View jobs', 'url' => route('tenant.jobs').'#jobs'],
    ['label' => 'Pricing', 'url' => route('tenant.pricing')],
    ['label' => 'Contact', 'url' => route('tenant.contact')],
  ];
@endphp

<header id="tenant-header" class="tenant-header" role="banner">
  <div class="tenant-header__inner">
    <div class="tenant-header__brand">
      <a class="tenant-brand" href="{{ route('tenant.home') }}" aria-label="{{ $brandName }} home">
        @if($logoUrl)
          <img class="tenant-brand__logo" src="{{ $logoUrl }}" alt="{{ $brandName }}">
        @else
          <span class="tenant-brand__mark">{{ $brandInitial }}</span>
          <strong class="tenant-brand__name">{{ $brandName }}</strong>
        @endif
      </a>
    </div>

    <nav class="tenant-header__nav" aria-label="Primary navigation">
      <ul class="tenant-nav__list">
        @foreach($primaryNav as $item)
          @php
            $children = $item['children'] ?? [];
            $hasChildren = count($children) > 0;
          @endphp
          <li class="tenant-nav__item {{ $hasChildren ? 'tenant-nav__item--has-children' : '' }} {{ ! empty($item['active']) ? 'is-active' : '' }}">
            <a class="tenant-nav__link {{ ! empty($item['active']) ? 'is-active' : '' }}" href="{{ $item['url'] }}">
              {{ $item['label'] }}
              @if($hasChildren)
                <span class="tenant-nav__chev" aria-hidden="true"></span>
              @endif
            </a>

            @if($hasChildren)
              <ul class="tenant-nav__dropdown">
                @foreach($children as $child)
                  <li class="tenant-nav__item {{ ! empty($child['active']) ? 'is-active' : '' }}">
                    <a class="tenant-nav__link {{ ! empty($child['active']) ? 'is-active' : '' }}" href="{{ $child['url'] }}">{{ $child['label'] }}</a>
                  </li>
                @endforeach
              </ul>
            @endif
          </li>
        @endforeach
      </ul>
    </nav>

    <div class="tenant-header__divider"></div>

    <div class="tenant-header__cta">
      <a href="{{ route('tenant.post-job') }}" class="tenant-header-btn tenant-header-btn--primary">Post a job</a>
      <a href="{{ route('tenant.login.choice') }}" class="tenant-header-btn tenant-header-btn--outline">Login</a>
    </div>

    <button class="tenant-header__hamburger" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="tenant-mobile-nav" data-tenant-menu-toggle>
      <span class="tenant-hamburger__bar"></span>
      <span class="tenant-hamburger__bar"></span>
      <span class="tenant-hamburger__bar"></span>
    </button>
  </div>
</header>

<div id="tenant-mobile-nav" class="tenant-mobile-nav" aria-hidden="true" data-tenant-mobile-nav>
  <div class="tenant-mobile-nav__panel">
    <button class="tenant-mobile-nav__close" type="button" aria-label="Close menu" data-tenant-menu-close>
      <i class="ph ph-x" aria-hidden="true"></i>
    </button>

    <div class="tenant-mobile-nav__brand">
      <a class="tenant-brand" href="{{ route('tenant.home') }}" aria-label="{{ $brandName }} home">
        @if($logoUrl)
          <img class="tenant-brand__logo tenant-brand__logo--mobile" src="{{ $logoUrl }}" alt="{{ $brandName }}">
        @else
          <span class="tenant-brand__mark">{{ $brandInitial }}</span>
          <strong class="tenant-brand__name">{{ $brandName }}</strong>
        @endif
      </a>
    </div>

    <div class="tenant-mobile-nav__section">
      <p class="tenant-mobile-nav__label">Menu</p>
      <ul class="tenant-mobile-nav__list">
        @foreach($primaryNav as $item)
          <li>
            <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
            @if(! empty($item['children']))
              <ul>
                @foreach($item['children'] as $child)
                  <li><a href="{{ $child['url'] }}">{{ $child['label'] }}</a></li>
                @endforeach
              </ul>
            @endif
          </li>
        @endforeach
      </ul>
    </div>

    <div class="tenant-mobile-nav__divider"></div>

    <div class="tenant-mobile-nav__section">
      <p class="tenant-mobile-nav__label">Quick links</p>
      <div class="tenant-mobile-nav__utility">
        @foreach($utilityLinks as $link)
          <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
        @endforeach
      </div>
    </div>

    <div class="tenant-mobile-nav__divider"></div>

    <div class="tenant-mobile-nav__section">
      <p class="tenant-mobile-nav__label">Action</p>
      <div class="tenant-mobile-nav__ctas">
        <a href="{{ route('tenant.post-job') }}" class="tenant-header-btn tenant-header-btn--primary tenant-mobile-nav__cta">Post a job</a>
        <a href="{{ route('tenant.login.choice') }}" class="tenant-header-btn tenant-header-btn--outline tenant-mobile-nav__cta">Login</a>
      </div>
    </div>
  </div>
</div>
