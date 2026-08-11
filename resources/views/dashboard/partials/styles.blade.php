<style>
.dash-page {
  background: var(--color-bg);
  padding: 34px clamp(34px, 6vw, 96px) 64px;
}

.dash-shell {
  width: min(1180px, 100%);
  margin: 0 auto;
  display: grid;
  gap: 18px;
}

.dash-app {
  grid-template-columns: 220px minmax(0, 1fr);
  align-items: start;
}

.dash-content {
  min-width: 0;
  display: grid;
  gap: 18px;
}

.dash-nav {
  position: sticky;
  top: 92px;
  display: grid;
  gap: 14px;
  padding: 14px;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-default);
  background: #ffffff;
}

.dash-nav__brand {
  display: flex;
  align-items: center;
  gap: 10px;
  padding-bottom: 14px;
  border-bottom: 1px solid var(--color-border);
}

.dash-nav__brand span {
  width: 36px;
  height: 36px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--radius-default);
  background: var(--color-primary);
  color: #ffffff;
  font-family: var(--font-ui);
  font-size: 13px;
  font-weight: 700;
}

.dash-nav__brand strong,
.dash-nav__brand small {
  display: block;
}

.dash-nav__brand strong {
  color: var(--color-text);
  font-family: var(--font-ui);
  font-size: 14px;
  font-weight: 700;
  line-height: 1.25;
}

.dash-nav__brand small {
  color: var(--color-text-muted);
  font-size: 12px;
  line-height: 1.35;
}

.dash-nav__links {
  display: grid;
  gap: 4px;
}

.dash-nav__links a,
.dash-nav__logout button {
  min-height: 40px;
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 0 10px;
  border: 1px solid transparent;
  border-radius: var(--radius-default);
  background: transparent;
  color: var(--color-text);
  font-family: var(--font-ui);
  font-size: 14px;
  font-weight: 600;
  text-align: left;
  text-decoration: none;
  transition: background .15s ease, border-color .15s ease, color .15s ease, transform .15s ease;
}

.dash-nav__links a:hover,
.dash-nav__links a.is-active,
.dash-nav__logout button:hover {
  border-color: var(--color-border);
  background: rgba(0, 0, 0, .03);
  color: var(--color-primary);
  text-decoration: none;
  transform: translateY(-1px);
}

.dash-nav__links a.is-active {
  border-color: var(--color-primary-muted);
  background: var(--color-primary-soft);
  color: var(--color-primary-strong);
}

.dash-nav__links i,
.dash-nav__logout i {
  font-size: 18px;
}

.dash-nav__logout {
  margin: 0;
  padding-top: 14px;
  border-top: 1px solid var(--color-border);
}

.dash-nav__logout button {
  width: 100%;
  cursor: pointer;
}

.dash-topbar {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 18px;
  margin-bottom: 6px;
}

.dash-eyebrow {
  margin: 0 0 6px;
  color: var(--color-text-muted);
  font-family: var(--font-ui);
  font-size: 12px;
  font-weight: 700;
  letter-spacing: .04em;
  text-transform: uppercase;
}

.dash-title {
  margin: 0;
  color: var(--color-text);
  font-family: var(--font-heading);
  font-size: 24px;
  font-weight: 700;
  line-height: 1.2;
}

.dash-subtitle {
  max-width: 760px;
  margin: 8px 0 0;
  color: var(--color-text-muted);
  font-size: 14px;
  line-height: 1.6;
}

.dash-user {
  min-width: 230px;
  padding: 14px;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-default);
  background: #ffffff;
}

.dash-user strong,
.dash-user span {
  display: block;
}

.dash-user strong {
  color: var(--color-text);
  font-family: var(--font-ui);
  font-size: 14px;
  font-weight: 700;
}

.dash-user span {
  margin-top: 3px;
  color: var(--color-text-muted);
  font-size: 13px;
}

.dash-layout {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 320px;
  gap: 18px;
  align-items: start;
}

.dash-main,
.dash-sidebar {
  display: grid;
  gap: 18px;
}

.dash-stats {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 14px;
}

.dash-stat,
.dash-panel,
.dash-card {
  border: 1px solid var(--color-border);
  border-radius: var(--radius-default);
  background: #ffffff;
}

.dash-stat {
  padding: 14px;
}

.dash-stat span {
  display: block;
  color: var(--color-text-muted);
  font-size: 12px;
}

.dash-stat strong {
  display: block;
  margin-top: 6px;
  color: var(--color-text);
  font-family: var(--font-ui);
  font-size: 22px;
  font-weight: 700;
  line-height: 1.1;
}

.dash-panel {
  overflow: hidden;
}

.dash-panel__head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 14px;
  padding: 18px 20px;
  border-bottom: 1px solid var(--color-border);
}

.dash-panel__head h2 {
  margin: 0;
  color: var(--color-text);
  font-family: var(--font-ui);
  font-size: 18px;
  font-weight: 700;
  line-height: 1.3;
}

.dash-panel__head p {
  margin: 5px 0 0;
  color: var(--color-text-muted);
  font-size: 13px;
  line-height: 1.55;
}

.dash-table {
  width: 100%;
  border-collapse: collapse;
}

.dash-table th,
.dash-table td {
  padding: 12px 14px;
  border-bottom: 1px solid var(--color-border);
  text-align: left;
  vertical-align: top;
}

.dash-table th {
  background: rgba(0, 0, 0, .02);
  color: var(--color-text-muted);
  font-family: var(--font-text);
  font-size: 12px;
  font-weight: 700;
}

.dash-table td {
  color: var(--color-text);
  font-size: 14px;
}

.dash-table tr:last-child td {
  border-bottom: 0;
}

.dash-cell-title {
  display: block;
  color: var(--color-text);
  font-family: var(--font-ui);
  font-size: 14px;
  font-weight: 700;
}

.dash-cell-meta {
  display: block;
  margin-top: 3px;
  color: var(--color-text-muted);
  font-size: 13px;
}

.dash-status {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 26px;
  padding: 4px 10px;
  border: 1px solid var(--color-primary-muted);
  border-radius: 999px;
  background: var(--color-primary-soft);
  color: var(--color-text);
  font-family: var(--font-ui);
  font-size: 12px;
  font-weight: 700;
  line-height: 1;
  white-space: nowrap;
}

.dash-status--accent {
  border-color: var(--color-accent-muted, rgba(217, 154, 91, .30));
  background: var(--color-accent-soft);
  color: var(--color-accent-strong);
}

.dash-status--muted {
  border-color: var(--color-border);
  background: #ffffff;
  color: var(--color-text-muted);
}

.dash-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.dash-actions--spaced {
  margin-top: 14px;
}

.dash-btn {
  display: inline-flex;
  min-height: 40px;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 16px;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-default);
  background: #ffffff;
  color: var(--color-text);
  font-family: var(--font-text);
  font-size: 15px;
  font-weight: 500;
  line-height: 1;
  text-decoration: none;
  white-space: nowrap;
  cursor: pointer;
  transition: background-color .15s ease, border-color .15s ease, color .15s ease, transform .15s ease;
}

.dash-btn:hover,
.dash-btn:focus,
.dash-btn:active,
.dash-btn:visited {
  text-decoration: none;
}

.dash-btn:hover {
  transform: translateY(-1px);
}

.dash-btn:active {
  transform: translateY(0);
}

.dash-btn:focus-visible {
  outline: 2px solid var(--color-primary);
  outline-offset: 2px;
}

.dash-btn--primary {
  border-color: var(--color-primary);
  background: var(--color-primary);
  color: #ffffff;
}

.dash-btn--primary:hover {
  border-color: var(--color-primary-strong);
  background: var(--color-primary-strong);
  color: #ffffff;
}

.dash-btn--ghost {
  border-color: var(--color-primary);
  background: #ffffff;
  color: var(--color-primary);
}

.dash-btn--ghost:hover {
  border-color: var(--color-primary);
  background: color-mix(in srgb, var(--color-primary) 10%, #ffffff 90%);
  color: var(--color-primary);
}

.dash-btn[disabled],
.dash-btn[aria-disabled="true"] {
  opacity: .6;
  pointer-events: none;
  transform: none;
}

.dash-card {
  padding: 18px;
}

.dash-card h2,
.dash-card h3 {
  margin: 0;
  color: var(--color-text);
  font-family: var(--font-ui);
  font-size: 18px;
  font-weight: 700;
}

.dash-card p {
  margin: 8px 0 0;
  color: var(--color-text-muted);
  font-size: 14px;
  line-height: 1.6;
}

.dash-list {
  display: grid;
  gap: 10px;
  margin: 14px 0 0;
  padding: 0;
  list-style: none;
}

.dash-list li {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  margin: 0;
  padding: 10px 0;
  border-bottom: 1px solid var(--color-border);
}

.dash-list li:last-child {
  border-bottom: 0;
}

.dash-list strong,
.dash-list span {
  display: block;
}

.dash-list strong {
  color: var(--color-text);
  font-family: var(--font-ui);
  font-size: 14px;
  font-weight: 700;
}

.dash-list span {
  color: var(--color-text-muted);
  font-size: 13px;
}

.dash-progress {
  display: grid;
  gap: 8px;
  margin-top: 14px;
}

.dash-progress__track {
  overflow: hidden;
  height: 8px;
  border-radius: 999px;
  background: var(--color-primary-soft);
}

.dash-progress__bar {
  display: block;
  height: 100%;
  border-radius: inherit;
  background: var(--color-primary);
}

.dash-progress__bar--employer {
  width: 68%;
}

.dash-progress__bar--candidate {
  width: 74%;
}

.dash-checklist {
  display: grid;
  gap: 9px;
  margin: 14px 0 0;
  padding: 0;
  list-style: none;
}

.dash-checklist li {
  display: flex;
  align-items: center;
  gap: 9px;
  margin: 0;
  color: var(--color-text-muted);
  font-size: 14px;
}

.dash-checklist i {
  color: var(--color-primary);
  font-size: 18px;
}

.dash-checklist--large {
  margin: 0;
  padding: 18px 20px;
}

.dash-checklist--large li {
  min-height: 36px;
  font-size: 15px;
}

.dash-empty {
  display: grid;
  gap: 12px;
  padding: 24px 20px;
  border-top: 1px solid var(--color-border);
  background: #ffffff;
}

.dash-empty h3,
.dash-empty p {
  margin: 0;
}

.dash-empty h3 {
  font-family: var(--font-ui);
  font-size: 18px;
  font-weight: 700;
}

.dash-empty p {
  max-width: 620px;
  color: var(--color-text-muted);
  font-size: 14px;
}

@media (max-width: 1080px) {
  .dash-layout,
  .dash-app {
    grid-template-columns: 1fr;
  }

  .dash-topbar {
    display: grid;
  }

  .dash-user {
    min-width: 0;
  }

  .dash-nav {
    position: static;
  }

  .dash-nav__links {
    display: flex;
    overflow-x: auto;
    padding-bottom: 2px;
  }

  .dash-nav__links a {
    white-space: nowrap;
  }
}

@media (max-width: 860px) {
  .dash-stats {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .dash-table {
    min-width: 680px;
  }

  .dash-panel {
    overflow-x: auto;
  }
}

@media (max-width: 620px) {
  .dash-page {
    padding: 30px 18px 56px;
  }

  .dash-stats {
    grid-template-columns: 1fr;
  }

  .dash-panel__head {
    display: grid;
  }

  .dash-panel__head,
  .dash-table th,
  .dash-table td {
    padding-left: 16px;
    padding-right: 16px;
  }
}
</style>
