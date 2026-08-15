<footer class="tenant-footer">
  <div>
    <strong>{{ $brandName }}</strong>
    <span>Jobs and applications</span>
  </div>

  <nav class="tenant-footer__links" aria-label="Footer navigation">
    <a href="{{ route('tenant.jobs') }}">Jobs</a>
    <a href="{{ route('tenant.contact') }}">Contact</a>
  </nav>
</footer>
