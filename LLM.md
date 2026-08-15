# LLM Project Notes

These notes describe the conventions future AI/code assistants should follow when working on this project.

## Language

- The entire application should use English for visible UI copy.
- This applies to the public website, authentication pages, client dashboard, admin dashboard, tenant job boards, forms, buttons, empty states, success/error messages, emails, and tests that assert UI text.
- Do not introduce Dutch labels, helper text, headings, placeholders, alerts, or navigation copy in new frontend work.
- Existing Dutch route aliases may stay when they are legacy redirects or backwards-compatible URLs, but new canonical routes and visible labels should be English.
- User-generated content, tenant-provided job data, internal database keys, and existing technical identifiers do not need to be translated unless the task explicitly asks for it.

## Tenant Frontend

- Tenant job boards run on the tenant's own domain or subdomain, for example `test.jobboardsoftware.co`.
- Tenant-facing views live under `resources/views/tenant`.
- Reusable tenant view pieces live under `resources/views/tenant/components`, such as the header, footer, job cards, and filters.
- Tenant CSS lives under `resources/css/tenants`, split by component where practical, for example `header.css`, `footer.css`, and job overview/filter styles. Import tenant CSS through `resources/css/tenants/index.css`.
- The tenant homepage should show the professional job overview directly, including search and filters.
- The tenant header and job overview/filter experience are inspired by the Student Inhuren Platform style: clean, practical, search-focused, and suitable for repeated job browsing.
- Job seeker and employer accounts are tenant-scoped accounts. They must be created and authenticated through tenant-domain routes, not as application-wide SaaS users.
- Tenant auth routes include `/login`, `/login/jobseeker`, `/login/employer`, `/sign-up`, `/sign-up/jobseeker`, and `/sign-up/employer`.
- Tenant dashboard routes are `/jobseeker/dashboard` and `/employer/dashboard`. They should use the same dashboard shell and `dash-*` UI patterns as the central client/admin dashboard files.
- Tenant public job posting lives at `/post-a-job`. It creates draft jobs first; the payment/publishing step comes later. The form can optionally create a tenant-scoped employer account.
- Tenant public form pages should keep shared form styling in `resources/css/tenants/forms.css`: 65% main content and 35% side content where applicable, 56px vertical spacing for the form block, 24px form padding, 24px Inter form titles, 18px Inter section titles, 14px/300 Poppins labels, and 14px/300 paragraph text inside forms only.
- Standard input fields should use a 5px border radius, `1px solid #dedede` border, and compact vertical padding.
- The client dashboard job type settings should always include these default English job types: `Part time`, `Full time`, `Freelance`, `Temporary`, and `Internship`. Custom job types are stored per tenant environment.
- Client dashboard companies are stored per tenant environment and can include an uploaded logo path.
- Client dashboard form pages should use the shared `dash-form-layout`: a 65% main form block with a 35% supporting side block.

## Implementation Notes

- Keep tenant routes and tenant rendering separate from the central SaaS pages.
- Prefer English canonical tenant routes such as `/jobs`; keep Dutch redirects only for compatibility when already present.
- Keep central SaaS owner/admin accounts tenantless. Tenant jobseeker/employer users should have `tenant_id` set and should not get access to the application-wide client dashboard.
- When changing visible frontend copy, update feature tests so assertions match the English UI.
- Run `php artisan test` after behavior or view changes that affect rendered output.
