<style>
.tenant-page {
  min-height: 100vh;
  background: var(--color-bg);
  padding: 0 24px 72px;
}

.tenant-nav {
  width: min(1180px, 100%);
  min-height: 76px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
}

.tenant-brand,
.tenant-nav nav {
  display: inline-flex;
  align-items: center;
  gap: 12px;
}

.tenant-brand {
  color: var(--color-text);
  text-decoration: none;
}

.tenant-brand span {
  width: 38px;
  height: 38px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  background: var(--tenant-accent);
  color: #ffffff;
  font-family: var(--font-ui);
  font-weight: 800;
}

.tenant-nav nav a {
  color: var(--color-text-muted);
  font-family: var(--font-ui);
  font-size: 14px;
  font-weight: 800;
  text-decoration: none;
}

.tenant-shell {
  width: min(1180px, 100%);
  margin: 0 auto;
  display: grid;
  gap: 20px;
}

.tenant-shell--detail {
  grid-template-columns: minmax(0, 1fr) 390px;
  align-items: start;
}

.tenant-hero {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 280px;
  gap: 24px;
  align-items: stretch;
  padding: 44px 0 18px;
}

.tenant-eyebrow {
  margin: 0 0 8px;
  color: var(--tenant-accent);
  font-family: var(--font-ui);
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.tenant-hero h1,
.tenant-panel h1,
.tenant-panel h2 {
  margin: 0;
  color: var(--color-text);
  font-family: var(--font-heading);
  font-weight: 800;
  line-height: 1.08;
}

.tenant-hero h1 {
  font-size: clamp(36px, 5vw, 62px);
}

.tenant-panel h1 {
  font-size: clamp(34px, 4vw, 52px);
}

.tenant-hero p,
.tenant-panel p,
.tenant-job p,
.tenant-job span,
.tenant-detail__body {
  color: var(--color-text-muted);
}

.tenant-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 22px;
}

/* Button styling for .tenant-btn lives in resources/css/buttons.css */

.tenant-card,
.tenant-panel,
.tenant-alert {
  border: 1px solid var(--color-border);
  border-radius: 8px;
  background: #ffffff;
  box-shadow: var(--shadow-sm);
}

.tenant-card {
  display: grid;
  align-content: center;
  padding: 22px;
}

.tenant-card span {
  color: var(--color-text-muted);
  font-size: 13px;
}

.tenant-card strong {
  display: block;
  margin-top: 4px;
  font-family: var(--font-ui);
  font-size: 34px;
}

.tenant-panel {
  padding: 26px;
}

.tenant-panel__head {
  margin-bottom: 16px;
}

.tenant-filter {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 220px auto;
  gap: 10px;
  margin-bottom: 16px;
}

.tenant-filter input,
.tenant-filter select,
.tenant-filter button,
.tenant-apply input,
.tenant-apply textarea {
  width: 100%;
  border: 1px solid var(--color-border-strong);
  border-radius: 8px;
  background: #ffffff;
  color: var(--color-text);
  font: inherit;
}

.tenant-filter input,
.tenant-filter select,
.tenant-filter button {
  min-height: 46px;
  padding: 0 12px;
}

.tenant-filter button {
  background: var(--tenant-accent);
  color: #ffffff;
  font-family: var(--font-ui);
  font-weight: 800;
}

.tenant-jobs {
  display: grid;
  gap: 12px;
}

.tenant-job {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 18px;
  padding: 18px;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  background: #fbfdff;
}

.tenant-job h3,
.tenant-job p {
  margin: 0;
}

.tenant-job h3 {
  font-size: 20px;
  font-weight: 800;
}

.tenant-job a,
.tenant-back {
  color: var(--tenant-accent);
  font-family: var(--font-ui);
  font-weight: 800;
}

.tenant-detail {
  display: grid;
  gap: 16px;
}

.tenant-detail__meta,
.tenant-detail__intro {
  margin: 0;
}

.tenant-detail__intro {
  font-size: 18px;
}

.tenant-apply {
  position: sticky;
  top: 24px;
}

.tenant-apply form {
  display: grid;
  gap: 12px;
  margin-top: 16px;
}

.tenant-apply label {
  display: grid;
  gap: 6px;
  color: var(--color-text);
  font-family: var(--font-ui);
  font-size: 13px;
  font-weight: 800;
}

.tenant-apply input,
.tenant-apply textarea {
  min-height: 44px;
  padding: 10px 12px;
  font-family: var(--font-text);
  font-weight: 400;
}

.tenant-apply span {
  color: #a33a2c;
  font-weight: 700;
}

.tenant-alert {
  padding: 14px 16px;
  color: var(--tenant-accent);
  font-family: var(--font-ui);
  font-weight: 800;
}

@media (max-width: 900px) {
  .tenant-hero,
  .tenant-shell--detail {
    grid-template-columns: 1fr;
  }

  .tenant-filter {
    grid-template-columns: 1fr;
  }

  .tenant-apply {
    position: static;
  }
}

@media (max-width: 620px) {
  .tenant-page {
    padding-inline: 18px;
  }

  .tenant-nav,
  .tenant-job {
    display: grid;
  }

  .tenant-panel,
  .tenant-card {
    padding: 22px;
  }
}
</style>
