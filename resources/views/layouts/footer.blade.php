@php
  $supportEmail = config('mail.from.address', 'support@jobboardsoftware.test');
  $currentYear = date('Y');
@endphp

<footer class="si-footer" aria-labelledby="footer-heading">
  <h2 id="footer-heading" class="sr-only">Footer</h2>

  <div class="si-footer__inner">
    <div class="si-footer__grid">
      <section class="si-footer__col">
        <h3 class="si-footer__heading">Contact</h3>
        <ul class="si-footer__list">
          <li>JobBoardSoftware B.V.</li>
          <li><a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a></li>
          <li>SaaS software for custom job boards</li>
        </ul>
        <div class="si-footer__socials" aria-label="Social media">
          <a href="{{ route('pages.contact') }}" aria-label="LinkedIn">
            <i class="ph ph-linkedin-logo"></i>
          </a>
          <a href="{{ route('pages.contact') }}" aria-label="Instagram">
            <i class="ph ph-instagram-logo"></i>
          </a>
        </div>
      </section>

      <section class="si-footer__col">
        <h3 class="si-footer__heading">Product</h3>
        <ul class="si-footer__list">
          <li><a href="{{ route('pages.features') }}#jobboard">Jobboard software</a></li>
          <li><a href="{{ route('pages.features') }}#management">Management portal</a></li>
          <li><a href="{{ route('pages.features') }}#domains">Custom domain</a></li>
          <li><a href="{{ route('pages.tarieven') }}">Pricing</a></li>
        </ul>
      </section>

      <section class="si-footer__col">
        <h3 class="si-footer__heading">Account</h3>
        <ul class="si-footer__list">
          <li><a href="{{ route('register.choice') }}">Create account</a></li>
          <li><a href="{{ route('login.choice') }}">Log in</a></li>
          <li><a href="{{ route('filament.workspace.pages.dashboard') }}">Dashboard</a></li>
          <li><a href="{{ route('pages.tarieven') }}">Pricing</a></li>
        </ul>
      </section>

      <section class="si-footer__col">
        <h3 class="si-footer__heading">Company</h3>
        <ul class="si-footer__list">
          <li><a href="{{ route('pages.over-ons') }}">About us</a></li>
          <li><a href="{{ route('pages.customers') }}">Customers</a></li>
          <li><a href="{{ route('pages.alternatives') }}">Alternatives</a></li>
          <li><a href="{{ route('pages.contact') }}">Contact</a></li>
          <li><a href="{{ route('pages.contact') }}">Book a demo</a></li>
          <li><a href="{{ route('admin.login') }}">Admin</a></li>
        </ul>
      </section>
    </div>

    <div class="si-footer__bottom">
      <p>&copy; {{ $currentYear }} JobBoardSoftware B.V. - All rights reserved.</p>
    </div>
  </div>
</footer>

<style>
.si-footer {
  background: var(--color-primary-strong);
  color: #ffffff;
  padding: 56px 24px 0;
}

.si-footer__inner {
  max-width: 1280px;
  margin: 0 auto;
}

.si-footer__grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0;
}

.si-footer__col {
  padding: 0 36px 52px;
}

.si-footer__col:first-child {
  padding-left: 0;
}

.si-footer__col:last-child {
  padding-right: 0;
}

.si-footer__col:not(:first-child) {
  border-left: 1px solid rgba(255, 255, 255, 0.12);
}

.si-footer__heading {
  margin: 0 0 18px;
  font-family: var(--font-ui);
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.48);
}

.si-footer__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  gap: 10px;
}

.si-footer__list li {
  margin: 0;
  font-family: var(--font-text);
  font-size: 15px;
  line-height: 1.4;
  color: rgba(255, 255, 255, 0.82);
}

.si-footer__list a {
  color: rgba(255, 255, 255, 0.82);
  text-decoration: none;
  transition: color 0.15s ease;
}

.si-footer__list a:hover {
  color: #ffffff;
}

.si-footer__socials {
  display: flex;
  gap: 8px;
  margin-top: 22px;
}

.si-footer__socials a {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  background: rgba(255, 255, 255, 0.06);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s ease, border-color 0.15s ease;
}

.si-footer__socials a:hover {
  background: rgba(255, 255, 255, 0.12);
  border-color: rgba(255, 255, 255, 0.35);
  text-decoration: none;
}

.si-footer__socials i {
  font-size: 20px;
  color: #ffffff;
}

.si-footer__bottom {
  border-top: 1px solid rgba(255, 255, 255, 0.12);
  padding: 20px 0;
  text-align: center;
}

.si-footer__bottom p {
  margin: 0;
  font-family: var(--font-text);
  font-size: 13px;
  color: rgba(255, 255, 255, 0.45);
}

@media (max-width: 900px) {
  .si-footer__grid {
    grid-template-columns: 1fr 1fr;
  }

  .si-footer__col {
    padding: 0 28px 40px;
  }

  .si-footer__col:first-child,
  .si-footer__col:nth-child(3) {
    padding-left: 0;
  }

  .si-footer__col:nth-child(2),
  .si-footer__col:nth-child(4) {
    padding-right: 0;
  }

  .si-footer__col:nth-child(3) {
    border-left: none;
  }
}

@media (max-width: 540px) {
  .si-footer {
    padding: 40px 18px 0;
  }

  .si-footer__grid {
    grid-template-columns: 1fr;
  }

  .si-footer__col {
    padding: 28px 0 !important;
    border-left: none !important;
    border-top: 1px solid rgba(255, 255, 255, 0.12);
  }

  .si-footer__col:first-child {
    padding-top: 0 !important;
    border-top: none;
  }
}
</style>
