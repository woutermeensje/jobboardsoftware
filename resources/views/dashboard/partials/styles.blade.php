<style>
.dash-page {
  background: var(--color-bg);
  padding: 38px 24px 72px;
}

.dash-shell {
  width: min(1280px, 100%);
  margin: 0 auto;
  display: grid;
  gap: 22px;
}

.dash-topbar {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 24px;
}

.dash-eyebrow {
  margin: 0 0 8px;
  color: var(--color-primary-strong);
  font-family: var(--font-ui);
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.dash-title {
  margin: 0;
  font-size: clamp(30px, 4vw, 48px);
  font-weight: 800;
}

.dash-subtitle {
  max-width: 760px;
  margin: 10px 0 0;
  color: var(--color-text-muted);
  font-size: 15px;
  line-height: 1.7;
}

.dash-user {
  min-width: 240px;
  padding: 16px;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  background: #ffffff;
  box-shadow: var(--shadow-sm);
}

.dash-user strong,
.dash-user span {
  display: block;
}

.dash-user strong {
  color: var(--color-text);
  font-family: var(--font-ui);
  font-size: 15px;
}

.dash-user span {
  margin-top: 3px;
  color: var(--color-text-muted);
  font-size: 13px;
}

.dash-layout {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 320px;
  gap: 22px;
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
  border-radius: 8px;
  background: #ffffff;
  box-shadow: var(--shadow-sm);
}

.dash-stat {
  padding: 18px;
}

.dash-stat span {
  display: block;
  color: var(--color-text-muted);
  font-size: 13px;
}

.dash-stat strong {
  display: block;
  margin-top: 8px;
  color: var(--color-text);
  font-family: var(--font-ui);
  font-size: 30px;
  line-height: 1;
}

.dash-panel {
  overflow: hidden;
}

.dash-panel__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  padding: 18px 20px;
  border-bottom: 1px solid var(--color-border);
}

.dash-panel__head h2 {
  margin: 0;
  font-size: 20px;
  font-weight: 800;
}

.dash-panel__head p {
  margin: 5px 0 0;
  color: var(--color-text-muted);
  font-size: 13px;
}

.dash-table {
  width: 100%;
  border-collapse: collapse;
}

.dash-table th,
.dash-table td {
  padding: 14px 20px;
  border-bottom: 1px solid var(--color-border);
  text-align: left;
  vertical-align: top;
}

.dash-table th {
  color: var(--color-text-soft);
  font-family: var(--font-ui);
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
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
  font-weight: 800;
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
  min-height: 26px;
  padding: 0 10px;
  border: 1px solid var(--color-primary-muted);
  border-radius: 999px;
  background: var(--color-primary-soft);
  color: var(--color-primary-strong);
  font-family: var(--font-ui);
  font-size: 12px;
  font-weight: 800;
}

.dash-status--accent {
  border-color: rgba(217, 154, 91, 0.32);
  background: var(--color-accent-soft);
  color: var(--color-accent-strong);
}

.dash-status--muted {
  border-color: var(--color-border);
  background: #f4f7fa;
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
  gap: 7px;
  padding: 0 14px;
  border: 1px solid transparent;
  border-radius: 6px;
  font-family: var(--font-ui);
  font-size: 14px;
  font-weight: 800;
  text-decoration: none;
  cursor: pointer;
}

.dash-btn:hover {
  text-decoration: none;
}

.dash-btn--primary {
  background: var(--color-primary-strong);
  color: #ffffff;
}

.dash-btn--ghost {
  border-color: var(--color-border-strong);
  background: #ffffff;
  color: var(--color-primary-strong);
}

.dash-card {
  padding: 18px;
}

.dash-card h2,
.dash-card h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 800;
}

.dash-card p {
  margin: 8px 0 0;
  color: var(--color-text-muted);
  font-size: 14px;
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
  font-size: 14px;
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
  background: var(--color-primary-strong);
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
  color: var(--color-text-muted);
  font-size: 14px;
}

.dash-checklist i {
  color: var(--color-primary-strong);
  font-size: 18px;
}

@media (max-width: 1080px) {
  .dash-layout,
  .dash-topbar {
    grid-template-columns: 1fr;
  }

  .dash-topbar {
    display: grid;
  }

  .dash-user {
    min-width: 0;
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

  .dash-panel__head,
  .dash-table th,
  .dash-table td {
    padding-left: 16px;
    padding-right: 16px;
  }
}
</style>
