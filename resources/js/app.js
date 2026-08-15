const tenantJobFilterForm = document.querySelector('[data-tenant-job-filter-form]');

if (tenantJobFilterForm) {
  let submitTimer = null;

  tenantJobFilterForm
    .querySelectorAll('[data-tenant-auto-submit]')
    .forEach((field) => {
      field.addEventListener('input', () => {
        window.clearTimeout(submitTimer);

        submitTimer = window.setTimeout(() => {
          tenantJobFilterForm.requestSubmit();
        }, 450);
      });
    });
}

const tenantHeader = document.getElementById('tenant-header');
const tenantMenuToggle = document.querySelector('[data-tenant-menu-toggle]');
const tenantMobileNav = document.querySelector('[data-tenant-mobile-nav]');
const tenantMenuClose = document.querySelector('[data-tenant-menu-close]');

if (tenantMenuToggle && tenantMobileNav) {
  const closeTenantMenu = () => {
    tenantMobileNav.classList.remove('is-open');
    tenantMenuToggle.classList.remove('is-open');
    tenantMenuToggle.setAttribute('aria-expanded', 'false');
    tenantMobileNav.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  };

  const openTenantMenu = () => {
    tenantMobileNav.classList.add('is-open');
    tenantMenuToggle.classList.add('is-open');
    tenantMenuToggle.setAttribute('aria-expanded', 'true');
    tenantMobileNav.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  };

  tenantMenuToggle.addEventListener('click', () => {
    tenantMobileNav.classList.contains('is-open') ? closeTenantMenu() : openTenantMenu();
  });

  tenantMenuClose?.addEventListener('click', closeTenantMenu);

  tenantMobileNav.addEventListener('click', (event) => {
    if (!event.target.closest('.tenant-mobile-nav__panel')) {
      closeTenantMenu();
    }
  });

  tenantMobileNav.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', closeTenantMenu);
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeTenantMenu();
    }
  });

  window.addEventListener('resize', () => {
    if (window.innerWidth > 1180) {
      closeTenantMenu();
    }
  });
}

if (tenantHeader) {
  const updateTenantHeaderScrollState = () => {
    tenantHeader.classList.toggle('is-scrolled', window.scrollY > 8);
  };

  updateTenantHeaderScrollState();
  window.addEventListener('scroll', updateTenantHeaderScrollState, { passive: true });
}
