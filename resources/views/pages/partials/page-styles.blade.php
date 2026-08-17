<style>
.content-page {
  background: var(--color-bg);
  padding: 48px 24px 72px;
}

.content-page__shell {
  width: min(1120px, 100%);
  margin: 0 auto;
  display: grid;
  gap: 24px;
}

.content-hero {
  display: grid;
  grid-template-columns: minmax(0, 0.9fr) minmax(280px, 0.48fr);
  gap: 28px;
  align-items: stretch;
}

.content-hero__copy {
  padding: 18px 0;
}

.content-eyebrow {
  margin: 0 0 10px;
  color: var(--color-primary-strong);
  font-family: var(--font-ui);
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.content-hero h1 {
  margin: 0;
  font-size: clamp(34px, 4.4vw, 56px);
  font-weight: 800;
}

.content-hero p,
.content-section p,
.content-card p {
  color: var(--color-text-muted);
}

.content-hero__intro {
  margin: 16px 0 0;
  max-width: 760px;
  font-size: 16px;
  line-height: 1.75;
}

.content-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 24px;
}

/* Button styling for .content-btn lives in resources/css/buttons.css */

.content-visual,
.content-section,
.content-card {
  border: 1px solid var(--color-border);
  border-radius: 8px;
  background: #ffffff;
  box-shadow: var(--shadow-sm);
}

.content-visual {
  display: grid;
  align-content: center;
  gap: 14px;
  padding: 26px;
}

.content-visual i,
.content-card i {
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

.content-visual strong {
  display: block;
  color: var(--color-text);
  font-family: var(--font-ui);
  font-size: 26px;
  line-height: 1.2;
}

.content-visual span {
  color: var(--color-text-muted);
  font-size: 14px;
}

.content-section {
  padding: 30px;
}

.content-section h1 {
  margin: 0 0 18px;
  color: #333;
  font-family: 'Work Sans', var(--font-heading), sans-serif;
  font-size: 24px;
  font-weight: 700;
}

.content-section h2 {
  margin: 0 0 10px;
  color: #333;
  font-family: 'Work Sans', var(--font-heading), sans-serif;
  font-size: 20px;
  font-weight: 700;
}

.content-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 16px;
}

.content-grid--two {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.content-card {
  padding: 22px;
}

.content-card h3 {
  margin: 16px 0 8px;
  font-size: 18px;
  font-weight: 800;
}

.content-card p {
  margin: 0;
  font-size: 14px;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 16px;
}

.features-card {
  display: grid;
  align-content: start;
  gap: 12px;
  min-height: 180px;
  padding: 28px;
  border: 1px solid #dedede;
  border-radius: 5px;
  background: #ffffff;
  color: inherit;
  text-decoration: none;
}

.features-card:hover {
  text-decoration: none;
}

.features-card__icon {
  width: 42px;
  height: 42px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 5px;
  background: var(--color-primary-soft);
  color: var(--color-primary-strong);
  font-size: 22px;
  line-height: 1;
}

.features-card__title {
  margin: 0;
  color: var(--color-text);
  font-family: 'Work Sans', var(--font-heading), sans-serif;
  font-size: 22px;
  font-weight: 700;
  line-height: 1.2;
}

.features-card__description {
  margin: 0;
  color: var(--color-text-muted);
  font-family: var(--font-text);
  font-size: 14px;
  font-weight: 400;
  line-height: 1.65;
}

.guide-page__shell {
  width: min(1100px, 100%);
}

.guide-layout {
  display: grid;
  grid-template-columns: minmax(0, 65fr) minmax(0, 35fr);
  gap: 24px;
  align-items: start;
}

.guide-sidebar {
  min-height: 240px;
}

.content-form {
  display: grid;
  gap: 14px;
  margin-top: 18px;
}

.content-form__grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.content-field {
  display: grid;
  gap: 7px;
}

.content-field label {
  color: var(--color-text);
  font-family: var(--font-ui);
  font-size: 13px;
  font-weight: 800;
}

.content-field input,
.content-field textarea,
.content-field select {
  width: 100%;
  min-height: 40px;
  padding: 8px 12px;
  border: 1px solid #dedede;
  border-radius: 5px;
  background: #ffffff;
  color: var(--color-text);
  font-size: 15px;
}

.content-field textarea {
  min-height: 124px;
  padding-top: 12px;
  resize: vertical;
}

.content-list {
  margin: 14px 0 0;
  padding: 0;
  display: grid;
  gap: 10px;
  list-style: none;
}

.content-list li {
  display: flex;
  gap: 9px;
  color: var(--color-text-muted);
  font-size: 14px;
}

.content-list li::before {
  content: "";
  width: 7px;
  height: 7px;
  margin-top: 9px;
  border-radius: 999px;
  background: var(--color-accent);
  flex-shrink: 0;
}

.price-card strong {
  display: block;
  margin-top: 12px;
  color: var(--color-text);
  font-family: var(--font-ui);
  font-size: 28px;
}

.pricing-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 16px;
  margin: 72px 0;
}

.pricing-card {
  display: grid;
  align-content: start;
  gap: 24px;
  padding: 28px;
  border: 1px solid #dedede;
  border-radius: 5px;
  background: #ffffff;
  box-shadow: none;
}

.pricing-card__header {
  display: grid;
  gap: 10px;
}

.pricing-card__name {
  margin: 0;
  color: var(--color-text);
  font-family: var(--font-heading);
  font-size: 20px;
  font-weight: 800;
  line-height: 1.25;
}

.pricing-card__price {
  margin: 0;
  color: var(--color-primary-strong);
  font-family: var(--font-ui);
  font-size: 30px;
  font-weight: 800;
  line-height: 1.15;
}

.pricing-card__description {
  margin: 0;
  color: var(--color-text-muted);
  font-size: 14px;
  line-height: 1.6;
}

.pricing-card__benefits {
  display: grid;
  gap: 10px;
  margin: 0;
  padding: 0;
  list-style: none;
}

.pricing-card__benefits li {
  display: flex;
  align-items: flex-start;
  gap: 9px;
  color: var(--color-text);
  font-size: 14px;
  line-height: 1.45;
}

.pricing-card__benefits i {
  margin-top: 2px;
  color: var(--color-accent-strong);
  font-size: 15px;
  font-weight: 700;
  line-height: 1;
}

.pricing-card__actions {
  margin-top: auto;
}

@media (max-width: 920px) {
  .content-hero,
  .content-grid,
  .content-grid--two,
  .features-grid,
  .guide-layout,
  .pricing-grid,
  .content-form__grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 620px) {
  .content-page {
    padding: 36px 18px 56px;
  }

  .content-section,
  .content-card,
  .content-visual {
    padding: 22px;
  }
}
</style>
