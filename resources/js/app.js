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
