<style>
.auth-page {
  padding: 56px 24px 72px;
}

.auth-shell {
  width: min(980px, 100%);
  margin: 0 auto;
}

.auth-head {
  max-width: 700px;
  margin-bottom: 28px;
}

.auth-eyebrow {
  margin: 0 0 10px;
  color: var(--color-primary-strong);
  font-family: var(--font-ui);
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.auth-head h1 {
  margin: 0;
  font-size: clamp(30px, 4vw, 44px);
  font-weight: 800;
}

.auth-head p {
  margin: 12px 0 0;
  color: var(--color-text-muted);
  font-size: 15px;
}

.auth-choice-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

.auth-choice-card,
.auth-form-card,
.auth-dashboard-card {
  border: 1px solid var(--color-border);
  border-radius: 8px;
  background: #ffffff;
  box-shadow: var(--shadow-sm);
}

.auth-choice-card {
  display: grid;
  gap: 12px;
  padding: 24px;
  color: var(--color-text);
  text-decoration: none;
}

.auth-choice-card:hover {
  border-color: var(--color-primary);
  box-shadow: var(--shadow-md);
  text-decoration: none;
}

.auth-choice-card i {
  display: inline-flex;
  width: 44px;
  height: 44px;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  background: var(--color-primary-soft);
  color: var(--color-primary-strong);
  font-size: 24px;
}

.auth-choice-card h2 {
  margin: 0;
  font-size: 21px;
  font-weight: 800;
}

.auth-choice-card p {
  margin: 0;
  color: var(--color-text-muted);
  font-size: 14px;
}

.auth-form-card {
  width: min(560px, 100%);
  padding: 28px;
}

.auth-form {
  display: grid;
  gap: 16px;
}

.auth-field {
  display: grid;
  gap: 7px;
}

.auth-label {
  color: var(--color-text);
  font-family: var(--font-ui);
  font-size: 13px;
  font-weight: 800;
}

.auth-input {
  width: 100%;
  min-height: 48px;
  padding: 0 13px;
  border: 1px solid #dedede;
  border-radius: 8px;
  background: #ffffff;
  color: var(--color-text);
  font-size: 15px;
  transition: border-color .2s ease, box-shadow .2s ease;
}

.auth-input:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px var(--color-primary-soft);
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
  font-size: 14px;
}

.auth-check input {
  width: 16px;
  height: 16px;
  accent-color: var(--color-primary);
}

.auth-actions {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px 14px;
  padding-top: 4px;
}

.auth-link {
  color: var(--color-primary-strong);
  font-family: var(--font-ui);
  font-size: 14px;
  font-weight: 700;
}

.auth-dashboard-card {
  padding: 28px;
}

.auth-dashboard-card h2 {
  margin: 0 0 10px;
  font-size: 26px;
  font-weight: 800;
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
  padding: 5px 11px;
  border: 1px solid var(--color-primary-muted);
  background: var(--color-primary-soft);
  color: var(--color-primary-strong);
  font-size: 13px;
  font-weight: 700;
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

  .auth-form-card,
  .auth-dashboard-card {
    padding: 22px;
  }
}
</style>
