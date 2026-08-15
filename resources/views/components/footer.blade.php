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
          <li><a href="{{ route('client.dashboard') }}">Dashboard</a></li>
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
