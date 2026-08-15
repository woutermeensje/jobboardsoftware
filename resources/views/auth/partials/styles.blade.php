<style>
.auth-page {
  padding: clamp(56px, 8vw, 104px) 24px 72px;
  background: var(--color-bg);
}

.auth-shell {
  width: min(840px, 100%);
  margin: 0 auto;
}

.auth-head {
  max-width: 640px;
  margin: 0 auto 24px;
}

.auth-eyebrow {
  margin: 0 0 8px;
  color: var(--color-text-muted);
  font-family: var(--font-ui);
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.auth-head h1 {
  margin: 0;
  color: var(--color-text);
  font-family: var(--font-heading);
  font-size: clamp(24px, 4vw, 34px);
  font-weight: 700;
  line-height: 1.15;
}

.auth-head p {
  margin: 10px 0 0;
  color: var(--color-text-muted);
  font-size: 15px;
  line-height: 1.6;
}

.auth-choice-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(260px, 1fr));
  gap: 20px;
}

.auth-choice-card,
.auth-form-card,
.auth-dashboard-card {
  border: 1px solid var(--color-border);
  border-radius: var(--radius-default);
  background: #ffffff;
}

.auth-choice-card {
  position: relative;
  display: grid;
  gap: 14px;
  align-content: center;
  min-height: 240px;
  padding: 32px 28px;
  color: var(--color-text);
  overflow: hidden;
  text-decoration: none;
  transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease, background-color .16s ease;
}

.auth-choice-card::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 5px;
  background: var(--color-primary);
}

.auth-choice-card:nth-child(2)::before {
  background: var(--color-accent);
}

.auth-choice-card:hover {
  border-color: var(--color-primary);
  box-shadow: 0 12px 28px rgba(17, 24, 39, 0.08);
  color: var(--color-primary-strong);
  text-decoration: none;
  transform: translateY(-2px);
}

.auth-choice-card i {
  display: inline-flex;
  width: 42px;
  height: 42px;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  background: var(--color-primary-soft);
  color: var(--color-primary);
  font-size: 22px;
}

.auth-choice-card h2 {
  margin: 0;
  color: inherit;
  font-family: var(--font-heading);
  font-size: clamp(24px, 4vw, 32px);
  font-weight: 700;
  line-height: 1.15;
}

.auth-choice-card p {
  margin: 0;
  color: var(--color-text-muted);
  font-size: 14px;
}

.auth-form-card {
  width: min(720px, 100%);
  margin: 0 auto;
  padding: 24px;
}

.auth-form-head {
  margin-bottom: 18px;
}

.auth-form-head h1 {
  margin: 0;
  color: var(--color-text);
  font-family: var(--font-heading);
  font-size: 22px;
  font-weight: 700;
  line-height: 1.2;
}

.auth-form {
  display: grid;
  gap: 14px;
}

.auth-grid {
  display: grid;
  gap: 14px;
}

.auth-grid--two {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.auth-field {
  display: grid;
  gap: 8px;
}

.auth-label {
  color: var(--color-text-muted);
  font-family: var(--font-ui);
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.auth-input {
  width: 100%;
  min-height: 44px;
  padding: 10px 12px;
  border: 1px solid var(--field-border);
  border-radius: var(--radius-default);
  background: #ffffff;
  color: var(--color-text);
  font-family: var(--font-text);
  font-size: 14px;
  outline: none;
  transition: border-color .16s ease, background-color .16s ease;
}

.auth-input:hover {
  border-color: var(--field-border-hover);
}

.auth-input:focus {
  border-color: var(--field-focus);
}

.auth-error {
  margin: 0;
  color: #a9652f;
  font-size: 13px;
}

.auth-check {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: var(--color-text-muted);
  font-size: 13px;
}

.auth-check input {
  width: 16px;
  height: 16px;
  accent-color: var(--color-primary);
}

.auth-actions {
  display: grid;
  gap: 12px;
  padding-top: 4px;
}

/* Shape & color come from resources/css/buttons.css — this form's
   submit button is just full-width instead of the usual inline size. */
.auth-button {
  width: 100%;
}

.auth-secondary-actions {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  justify-content: space-between;
  gap: 10px 14px;
}

.auth-link {
  color: var(--color-primary);
  font-family: var(--font-text);
  font-size: 14px;
  font-weight: 500;
}

.auth-dashboard-card {
  padding: 24px;
}

.auth-dashboard-card h2 {
  margin: 0 0 8px;
  font-size: 22px;
  font-weight: 700;
}

.auth-dashboard-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin: 18px 0 24px;
}

.auth-dashboard-meta span {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  padding: 4px 10px;
  border: 1px solid var(--color-primary-muted);
  background: var(--color-primary-soft);
  color: var(--color-text);
  font-family: var(--font-ui);
  font-size: 12px;
  font-weight: 600;
}

.auth-dashboard-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

@media (max-width: 760px) {
  .auth-page {
    padding: 40px 18px 56px;
  }

  .auth-choice-grid {
    grid-template-columns: 1fr;
  }

  .auth-grid--two {
    grid-template-columns: 1fr;
  }

  .auth-form-card,
  .auth-dashboard-card {
    padding: 22px;
  }
}
</style>
