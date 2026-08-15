@php
  $authUser = auth()->user();
  $dashboardUrl = null;

  if ($authUser) {
    $dashboardUrl = match ($authUser->role) {
      \App\Models\User::ROLE_ADMIN => route('admin.dashboard'),
      \App\Models\User::ROLE_TENANT_OWNER => route('workspace.dashboard'),
      default => route('workspace.dashboard'),
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
      ['label' => 'Manage environment', 'url' => route('workspace.environments.index')],
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
  <div class="rn-topbar" aria-label="Quick links">
    <div class="rn-topbar__inner">
      <div class="rn-topbar__group rn-topbar__group--left">
        @if($authUser)
          <a class="rn-topbar__link" href="{{ $dashboardUrl }}">My dashboard</a>
          <span class="rn-topbar__sep" aria-hidden="true">|</span>
          <form method="POST" action="{{ route('logout') }}" class="rn-topbar__logout-form">
            @csrf
            <button class="rn-topbar__link rn-topbar__link--button" type="submit">Log out</button>
          </form>
        @else
          <a class="rn-topbar__link" href="{{ route('register.choice') }}">Sign up</a>
          <span class="rn-topbar__sep" aria-hidden="true">|</span>
          <a class="rn-topbar__link" href="{{ route('login.choice') }}">Log in</a>
        @endif
      </div>
      <div class="rn-topbar__group rn-topbar__group--right">
        <a class="rn-topbar__link" href="{{ route('welcome') }}#domains">Connect a custom domain</a>
        <span class="rn-topbar__sep" aria-hidden="true">|</span>
        <a class="rn-topbar__link" href="{{ route('pages.contact') }}">Book a demo</a>
      </div>
    </div>
  </div>

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

<style>
.rn-header,
.rn-header *,
.rn-header *::before,
.rn-header *::after,
.rn-mobile-nav,
.rn-mobile-nav *,
.rn-mobile-nav *::before,
.rn-mobile-nav *::after {
  box-sizing: border-box;
}

.rn-header {
  position: sticky;
  top: 0;
  z-index: 9000;
  width: 100%;
  background: #ffffff;
  border-bottom: 1px solid #e5edf3;
  box-shadow: var(--shadow-sm);
  transition: box-shadow .18s ease, border-color .18s ease;
}

.rn-header.is-scrolled {
  border-bottom-color: #dbe7ef;
  box-shadow: 0 8px 24px rgba(17, 24, 39, 0.08);
}

.rn-topbar {
  width: 100%;
  background: var(--color-primary-strong);
  color: #ffffff;
}

.rn-topbar__inner {
  width: min(100% - 64px, 1320px);
  height: 30px;
  min-height: 30px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
}

.rn-topbar__group {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  height: 30px;
  min-width: 0;
  font-size: 0;
}

.rn-topbar__link,
.rn-topbar__link:visited {
  display: inline-flex;
  align-items: center;
  height: 30px;
  margin: 0;
  padding: 0;
  color: rgba(255, 255, 255, 0.86);
  font-family: var(--font-ui);
  font-size: 12px;
  font-weight: 500;
  line-height: 1.2;
  text-decoration: none;
  white-space: nowrap;
  transition: color .15s ease;
}

.rn-topbar__link:hover,
.rn-topbar__link:focus {
  color: #ffffff;
  text-decoration: underline;
  text-underline-offset: 3px;
}

.rn-topbar__sep {
  display: inline-flex;
  align-items: center;
  height: 30px;
  color: rgba(255, 255, 255, 0.28);
  font-family: var(--font-ui);
  font-size: 12px;
  font-weight: 500;
  line-height: 1;
}

.rn-topbar__logout-form,
.rn-header__logout-form,
.rn-mobile-nav__logout-form {
  display: inline-flex;
  margin: 0;
}

.rn-topbar__link--button {
  appearance: none;
  border: none;
  background: transparent;
  cursor: pointer;
}

.rn-header__inner {
  width: min(100% - 64px, 1320px);
  min-height: 72px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  gap: 20px;
}

.rn-header__brand,
.rn-header__cta {
  display: flex;
  align-items: center;
}

.rn-header__brand a,
.rn-mobile-nav__brand a {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  color: var(--color-text);
  text-decoration: none;
}

.rn-header__mark {
  width: 38px;
  height: 38px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  background: var(--color-primary-strong);
  color: #ffffff;
  font-family: var(--font-ui);
  font-size: 13px;
  font-weight: 800;
  letter-spacing: 0;
  box-shadow: inset 0 -6px 14px rgba(0, 0, 0, 0.12);
}

.rn-header__name {
  font-family: var(--font-ui);
  font-size: 19px;
  font-weight: 800;
  letter-spacing: 0;
  color: var(--color-text);
  white-space: nowrap;
}

.rn-header__nav {
  flex: 1 1 auto;
  display: flex;
  align-items: center;
  justify-content: flex-end;
}

.rn-nav__list {
  display: flex;
  align-items: center;
  gap: 4px;
  margin: 0;
  padding: 0;
  list-style: none;
}

.rn-nav__item {
  position: relative;
  margin: 0;
  padding: 0;
  list-style: none;
}

.rn-nav__link {
  position: relative;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  min-height: 72px;
  padding: 0 10px;
  border-radius: 6px;
  color: #333333;
  font-family: 'Roboto', sans-serif;
  font-size: 15px;
  font-weight: 700;
  line-height: 1.2;
  text-decoration: none;
  white-space: nowrap;
  transition: color .15s ease, background .15s ease;
}

.rn-nav__link:hover {
  color: var(--color-primary-strong);
  background: #f7fafc;
  text-decoration: none;
}

.rn-nav__link.is-active {
  color: var(--color-primary-strong);
}

.rn-nav__chev {
  width: 7px;
  height: 7px;
  border-right: 1.5px solid currentColor;
  border-bottom: 1.5px solid currentColor;
  transform: rotate(45deg) translateY(-2px);
  transition: transform .2s ease;
  flex-shrink: 0;
}

.rn-nav__item--has-children:hover > .rn-nav__link .rn-nav__chev,
.rn-nav__item--has-children:focus-within > .rn-nav__link .rn-nav__chev {
  transform: rotate(-135deg) translateY(-2px);
}

.rn-nav__dropdown {
  position: absolute;
  top: calc(100% - 6px);
  left: 0;
  z-index: 9001;
  display: block;
  min-width: 220px;
  margin: 0;
  padding: 8px;
  list-style: none;
  background: #ffffff;
  border: 1px solid #e3ebf2;
  border-radius: 8px;
  box-shadow: 0 14px 32px rgba(17, 24, 39, 0.12);
  opacity: 0;
  pointer-events: none;
  transform: translateY(6px);
  visibility: hidden;
  transition: opacity .16s ease, transform .16s ease, visibility .16s ease;
}

.rn-nav__item--has-children:hover > .rn-nav__dropdown,
.rn-nav__item--has-children:focus-within > .rn-nav__dropdown {
  opacity: 1;
  pointer-events: auto;
  transform: translateY(0);
  visibility: visible;
}

.rn-nav__dropdown .rn-nav__link {
  display: flex;
  width: 100%;
  min-height: 0;
  padding: 10px 12px;
}

.rn-header__divider {
  width: 1px;
  height: 28px;
  background: #e5e7eb;
  flex-shrink: 0;
}

.rn-header__cta {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

.rn-btn {
  appearance: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  min-height: 42px;
  padding: 0 16px;
  border: 1px solid transparent;
  border-radius: 6px;
  font-family: var(--font-ui);
  font-size: 15px;
  font-weight: 600;
  line-height: 1.2;
  text-decoration: none;
  white-space: nowrap;
  cursor: pointer;
  transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease, color .15s ease;
}

.rn-btn--accent {
  color: #ffffff;
  border-color: var(--color-primary-strong);
  background: var(--color-primary-strong);
  box-shadow: 0 1px 2px rgba(17, 24, 39, 0.08);
}

.rn-btn--outline {
  color: var(--color-primary-strong);
  border-color: #c9d9e5;
  background: #ffffff;
  box-shadow: 0 1px 2px rgba(17, 24, 39, 0.04);
}

.rn-btn--accent:hover,
.rn-btn--accent:focus {
  color: #ffffff;
  background: #274f6b;
  border-color: #274f6b;
  box-shadow: 0 4px 10px rgba(47, 95, 128, 0.18);
  text-decoration: none;
}

.rn-btn--outline:hover,
.rn-btn--outline:focus {
  color: #244f6d;
  border-color: #a9c1d2;
  background: #f7fafc;
  box-shadow: 0 2px 6px rgba(47, 95, 128, 0.08);
  text-decoration: none;
}

.rn-header__hamburger {
  display: none;
  width: 40px;
  height: 40px;
  margin-left: auto;
  padding: 8px;
  border: 1px solid #c9d9e5;
  border-radius: 6px;
  background: #ffffff;
  cursor: pointer;
  box-shadow: 0 1px 2px rgba(17, 24, 39, 0.04);
}

.rn-header__hamburger:hover {
  background: #f7fafc;
  border-color: #a9c1d2;
}

.rn-hamburger__bar {
  display: block;
  width: 100%;
  height: 2px;
  margin: 5px 0;
  background: #333333;
  border-radius: 2px;
  transition: transform .25s ease, opacity .25s ease;
}

.rn-header__hamburger.is-open .rn-hamburger__bar:nth-child(1) {
  transform: translateY(7px) rotate(45deg);
}

.rn-header__hamburger.is-open .rn-hamburger__bar:nth-child(2) {
  opacity: 0;
}

.rn-header__hamburger.is-open .rn-hamburger__bar:nth-child(3) {
  transform: translateY(-7px) rotate(-45deg);
}

.rn-mobile-nav {
  position: fixed;
  inset: 0;
  z-index: 9100;
  display: none;
  background: rgba(17, 24, 39, 0.32);
  opacity: 0;
  visibility: hidden;
  transition: opacity .2s ease, visibility .2s ease;
}

.rn-mobile-nav.is-open {
  opacity: 1;
  visibility: visible;
}

.rn-mobile-nav__panel {
  position: absolute;
  top: 0;
  right: 0;
  width: min(400px, 82vw);
  min-width: 280px;
  height: 100dvh;
  padding: 20px 18px 18px;
  overflow-y: auto;
  background: #ffffff;
  border-left: 1px solid #e5e7eb;
  transform: translateX(100%);
  transition: transform .2s ease;
}

.rn-mobile-nav.is-open .rn-mobile-nav__panel {
  transform: translateX(0);
}

.rn-mobile-nav__close {
  position: absolute;
  top: 18px;
  right: 16px;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1.5px solid #e5e7eb;
  border-radius: 5px;
  background: transparent;
  color: #374151;
  cursor: pointer;
}

.rn-mobile-nav__brand {
  display: flex;
  align-items: center;
  min-height: 42px;
  max-width: calc(100% - 56px);
  margin-bottom: 22px;
}

.rn-mobile-nav__label {
  margin: 0 0 8px;
  color: #6b7280;
  font-family: var(--font-ui);
  font-size: 11px;
  font-weight: 700;
  line-height: 1.2;
  text-transform: uppercase;
}

.rn-mobile-nav__list,
.rn-mobile-nav__list ul {
  display: flex;
  flex-direction: column;
  margin: 0;
  padding: 0;
  list-style: none;
}

.rn-mobile-nav__list li {
  width: 100%;
  border-bottom: 1px solid #f3f4f6;
  list-style: none;
}

.rn-mobile-nav__list a {
  display: block;
  padding: 13px 4px;
  color: #333333;
  font-family: var(--font-ui);
  font-size: 15px;
  font-weight: 600;
  line-height: 1.2;
  text-decoration: none;
}

.rn-mobile-nav__list ul {
  padding: 0 0 8px 12px;
  border-left: 3px solid #f3f4f6;
}

.rn-mobile-nav__list ul li {
  border-bottom: none;
}

.rn-mobile-nav__list ul a {
  padding: 8px 4px;
}

.rn-mobile-nav__divider {
  height: 1px;
  margin: 16px 0;
  background: #f3f4f6;
}

.rn-mobile-nav__utility {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px;
}

.rn-mobile-nav__utility a {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 40px;
  padding: 8px 10px;
  border: 1px solid #e3ebf2;
  border-radius: 6px;
  background: #f7fafc;
  color: var(--color-primary-strong);
  font-family: var(--font-ui);
  font-size: 14px;
  font-weight: 600;
  line-height: 1.2;
  text-align: center;
  text-decoration: none;
}

.rn-mobile-nav__ctas {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.rn-mobile-nav__cta {
  width: 100%;
}

.rn-mobile-nav__logout-form {
  width: 100%;
}

@media (max-width: 1180px) {
  .rn-topbar {
    display: none;
  }

  .rn-header__inner {
    width: calc(100% - 48px);
  }

  .rn-header__nav,
  .rn-header__cta,
  .rn-header__divider {
    display: none;
  }

  .rn-header__hamburger,
  .rn-mobile-nav {
    display: block;
  }
}

@media (min-width: 1181px) {
  .rn-mobile-nav {
    display: none !important;
  }
}

@media (max-width: 560px) {
  .rn-header__inner {
    width: calc(100% - 32px);
  }

  .rn-header__name {
    font-size: 17px;
  }
}
</style>
