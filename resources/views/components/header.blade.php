@php
  $authUser = auth()->user();
  $dashboardUrl = null;

  if ($authUser) {
    $dashboardUrl = match ($authUser->role) {
      \App\Models\User::ROLE_ADMIN => route('admin.dashboard'),
      \App\Models\User::ROLE_TENANT_OWNER => route('client.dashboard'),
      default => route('client.dashboard'),
    };
  }

  $primaryNav = [
    ['label' => 'Features', 'url' => route('pages.features'), 'active' => request()->routeIs('pages.features')],
    ['label' => 'Pricing', 'url' => route('pages.tarieven'), 'active' => request()->routeIs('pages.tarieven')],
    ['label' => 'Customers', 'url' => route('pages.customers'), 'active' => request()->routeIs('pages.customers')],
    ['label' => 'FAQ', 'url' => route('pages.faq'), 'active' => request()->routeIs('pages.faq')],
    ['label' => 'About us', 'url' => route('pages.over-ons'), 'active' => request()->routeIs('pages.over-ons')],
    ['label' => 'Contact', 'url' => route('pages.contact'), 'active' => request()->routeIs('pages.contact')],
  ];

  $utilityLinks = $authUser
    ? [
      ['label' => 'My dashboard', 'url' => $dashboardUrl],
      ['label' => 'Manage environment', 'url' => route('client.environments.index')],
      ['label' => 'Contact', 'url' => route('pages.contact')],
    ]
    : [
      ['label' => 'Sign up', 'url' => route('register.choice')],
      ['label' => 'Log in', 'url' => route('login.choice')],
      ['label' => 'Pricing', 'url' => route('pages.tarieven')],
      ['label' => 'Book a demo', 'url' => route('pages.contact')],
    ];
@endphp

<header id="rn-header" class="rn-header" role="banner">
  <div class="rn-header__inner">
    <div class="rn-header__brand">
      <a href="{{ route('welcome') }}" aria-label="JobBoardSoftware home">
        <span class="rn-header__mark" aria-hidden="true">JB</span>
        <span class="rn-header__name">JobBoardSoftware</span>
      </a>
    </div>

    <nav class="rn-header__nav" aria-label="Primary navigation">
      <ul class="rn-nav__list">
        @foreach($primaryNav as $item)
          @php
            $children = $item['children'] ?? [];
            $hasChildren = count($children) > 0;
            $itemClass = 'rn-nav__item'
              . ($hasChildren ? ' rn-nav__item--has-children' : '')
              . (!empty($item['active']) ? ' is-active' : '');
          @endphp
          <li class="{{ $itemClass }}">
            <a class="rn-nav__link {{ !empty($item['active']) ? 'is-active' : '' }}" href="{{ $item['url'] }}">
              {{ $item['label'] }}
              @if($hasChildren)
                <span class="rn-nav__chev" aria-hidden="true"></span>
              @endif
            </a>

            @if($hasChildren)
              <ul class="rn-nav__dropdown">
                @foreach($children as $child)
                  <li class="rn-nav__item">
                    <a class="rn-nav__link" href="{{ $child['url'] }}">{{ $child['label'] }}</a>
                  </li>
                @endforeach
              </ul>
            @endif
          </li>
        @endforeach
      </ul>
    </nav>

    <div class="rn-header__divider"></div>

    <div class="rn-header__cta">
      @if($authUser)
        <a href="{{ $dashboardUrl }}" class="rn-btn rn-btn--accent">My dashboard</a>
        <form method="POST" action="{{ route('logout') }}" class="rn-header__logout-form">
          @csrf
          <button class="rn-btn rn-btn--outline" type="submit">Log out</button>
        </form>
      @else
        <a href="{{ route('register.choice') }}" class="rn-btn rn-btn--accent">Start free</a>
        <a href="{{ route('login.choice') }}" class="rn-btn rn-btn--outline">Log in</a>
      @endif
    </div>

    <button class="rn-header__hamburger" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="rn-mobile-nav">
      <span class="rn-hamburger__bar"></span>
      <span class="rn-hamburger__bar"></span>
      <span class="rn-hamburger__bar"></span>
    </button>
  </div>
</header>

<div id="rn-mobile-nav" class="rn-mobile-nav" aria-hidden="true">
  <div class="rn-mobile-nav__panel">
    <button class="rn-mobile-nav__close" type="button" aria-label="Close menu">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22" aria-hidden="true">
        <line x1="18" y1="6" x2="6" y2="18"/>
        <line x1="6" y1="6" x2="18" y2="18"/>
      </svg>
    </button>

    <div class="rn-mobile-nav__brand">
      <a href="{{ route('welcome') }}" aria-label="JobBoardSoftware home">
        <span class="rn-header__mark" aria-hidden="true">JB</span>
        <span class="rn-header__name">JobBoardSoftware</span>
      </a>
    </div>

    <div class="rn-mobile-nav__section">
      <p class="rn-mobile-nav__label">Menu</p>
      <ul class="rn-mobile-nav__list">
        @foreach($primaryNav as $item)
          <li>
            <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
            @if(!empty($item['children']))
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

    <div class="rn-mobile-nav__divider"></div>

    <div class="rn-mobile-nav__section">
      <p class="rn-mobile-nav__label">Quick links</p>
      <div class="rn-mobile-nav__utility" aria-label="Mobile quick links">
        @foreach($utilityLinks as $link)
          <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
        @endforeach
      </div>
    </div>

    <div class="rn-mobile-nav__divider"></div>

    <div class="rn-mobile-nav__section">
      <p class="rn-mobile-nav__label">Action</p>
      <div class="rn-mobile-nav__ctas">
        @if($authUser)
          <a href="{{ $dashboardUrl }}" class="rn-btn rn-btn--accent rn-mobile-nav__cta">My dashboard</a>
          <form method="POST" action="{{ route('logout') }}" class="rn-mobile-nav__logout-form">
            @csrf
            <button class="rn-btn rn-btn--outline rn-mobile-nav__cta" type="submit">Log out</button>
          </form>
        @else
          <a href="{{ route('register.choice') }}" class="rn-btn rn-btn--accent rn-mobile-nav__cta">Start free</a>
          <a href="{{ route('login.choice') }}" class="rn-btn rn-btn--outline rn-mobile-nav__cta">Log in</a>
        @endif
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const header = document.getElementById('rn-header');
  const hamburger = document.querySelector('.rn-header__hamburger');
  const mobileNav = document.getElementById('rn-mobile-nav');
  const closeBtn = document.querySelector('.rn-mobile-nav__close');

  if (!hamburger || !mobileNav) return;

  function closeMenu() {
    mobileNav.classList.remove('is-open');
    hamburger.classList.remove('is-open');
    hamburger.setAttribute('aria-expanded', 'false');
    mobileNav.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('is-menu-open');
  }

  function openMenu() {
    mobileNav.classList.add('is-open');
    hamburger.classList.add('is-open');
    hamburger.setAttribute('aria-expanded', 'true');
    mobileNav.setAttribute('aria-hidden', 'false');
    document.body.classList.add('is-menu-open');
  }

  hamburger.addEventListener('click', function () {
    mobileNav.classList.contains('is-open') ? closeMenu() : openMenu();
  });

  if (closeBtn) {
    closeBtn.addEventListener('click', closeMenu);
  }

  mobileNav.addEventListener('click', function (event) {
    if (!event.target.closest('.rn-mobile-nav__panel')) closeMenu();
  });

  mobileNav.querySelectorAll('a').forEach(function (link) {
    link.addEventListener('click', closeMenu);
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') closeMenu();
  });

  window.addEventListener('resize', function () {
    if (window.innerWidth > 1180) closeMenu();
  });

  if (header) {
    function updateScrollState() {
      header.classList.toggle('is-scrolled', window.scrollY > 8);
    }

    updateScrollState();
    window.addEventListener('scroll', updateScrollState, { passive: true });
  }
})();
</script>
