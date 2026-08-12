<style>
  .admin-table-form {
    display: grid;
    gap: 8px;
    min-width: 220px;
  }

  .admin-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
  }

  .admin-form-grid--three {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }

  .admin-form-grid--single {
    grid-template-columns: 1fr;
  }

  .admin-table-form .form-control {
    min-height: 38px;
    padding: 8px 10px;
    font-size: 13px;
  }

  .admin-table-form .dash-btn {
    min-height: 36px;
    padding: 8px 12px;
    font-size: 13px;
  }

  .admin-switch {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--color-text-muted);
    font-size: 13px;
  }

  .admin-summary-list {
    display: grid;
    gap: 10px;
    padding: 18px 20px;
  }

  .admin-summary-list a {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-default);
    color: var(--color-text);
    text-decoration: none;
  }

  .admin-summary-list a:hover {
    border-color: var(--color-primary-muted);
    background: var(--color-primary-soft);
    color: var(--color-primary-strong);
  }

  .dash-card--success {
    border-color: var(--color-primary-muted);
    background: var(--color-primary-soft);
    color: var(--color-primary-strong);
  }

  @media (max-width: 860px) {
    .admin-form-grid,
    .admin-form-grid--three {
      grid-template-columns: 1fr;
    }
  }
</style>
