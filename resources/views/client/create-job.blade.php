@extends('layouts.app')

@section('title', 'Create job | Client dashboard')
@section('meta_description', 'Create a job for a job board environment.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('client.partials.navigation')
@endsection

@php
  $selectedCompanyId = (string) old('tenant_company_id', '');
  $selectedCompany = $companies->first(fn ($company): bool => (string) $company->id === $selectedCompanyId);
  $selectedTenantId = (string) old('tenant_id', $selectedCompany?->tenant_id ?? $tenants->first()?->id);
  $selectedJobType = (string) old('employment_type', '');
@endphp

@section('content')
      @if(session('status'))
        <section class="dash-card dash-card--success"><strong>{{ session('status') }}</strong></section>
      @endif

      @if($errors->any())
        <section class="dash-card dash-card--danger">
          <strong>Job could not be created.</strong>
          <ul class="dash-message-list">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </section>
      @endif

      <div class="dash-form-layout">
        <main class="dash-form-layout__main">
          <section class="dash-panel">
            @if($tenants->isEmpty())
              <div class="dash-panel__head">
                <div>
                  <h2>Create job</h2>
                  <p>Add a vacancy to one of your job board environments.</p>
                </div>
                <a class="dash-link" href="{{ route('client.jobs.index') }}">Back to jobs</a>
              </div>

              <div class="dash-empty-state">
                <h3>No environments yet</h3>
                <p>Create an environment before adding jobs.</p>
                <a class="dash-link" href="{{ route('client.environments.create') }}">Create environment</a>
              </div>
            @elseif($companies->isEmpty())
              <div class="dash-panel__head">
                <div>
                  <h2>Create job</h2>
                  <p>Add a company before creating a job.</p>
                </div>
                <a class="dash-link" href="{{ route('client.jobs.index') }}">Back to jobs</a>
              </div>

              <div class="dash-empty-state">
                <h3>No companies yet</h3>
                <p>Create a company before adding jobs.</p>
                <a class="dash-link" href="{{ route('client.companies.create') }}">Create company</a>
              </div>
            @else
              <form class="tenant-form tenant-dashboard-form" method="POST" action="{{ route('client.jobs.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="tenant_id" value="{{ $selectedTenantId }}" data-company-tenant-input>

                <div class="tenant-form-header tenant-dashboard-form__header">
                  <div>
                    <h2 class="tenant-form-title">Create job</h2>
                    <p class="tenant-form-intro">Fill in the details below and save the job.</p>
                  </div>
                  <a class="dash-link" href="{{ route('client.jobs.index') }}">Back to jobs</a>
                </div>

                <section class="tenant-form-section-block tenant-form__section" aria-labelledby="job-company-title">
                  <div class="tenant-form-section-head">
                    <h2 id="job-company-title" class="tenant-form-section-title">Company information</h2>
                  </div>

                  <div class="tenant-form__field tenant-multiselect" data-multiselect data-multiselect-max="1" data-company-select>
                    <label id="dashboard-company-label">Company</label>
                    <button
                      class="tenant-multiselect__button"
                      type="button"
                      aria-haspopup="listbox"
                      aria-expanded="false"
                      aria-labelledby="dashboard-company-label"
                      data-multiselect-button
                      data-multiselect-empty-label="Select company"
                    >
                      Select company
                    </button>
                    <div class="tenant-multiselect__menu" data-multiselect-menu>
                      <input
                        class="tenant-multiselect__search"
                        type="search"
                        aria-label="Search company"
                        autocomplete="off"
                        data-multiselect-search
                      >
                      <div class="tenant-multiselect__options" role="listbox" aria-multiselectable="false">
                        @foreach($companies as $company)
                          <label class="tenant-multiselect__option" data-multiselect-option-row>
                            <input
                              type="radio"
                              name="tenant_company_id"
                              value="{{ $company->id }}"
                              @checked($selectedCompanyId === (string) $company->id)
                              data-multiselect-option
                              data-multiselect-label="{{ $company->name }}"
                              data-company-tenant-id="{{ $company->tenant_id }}"
                            >
                            <span>{{ $company->name }}</span>
                          </label>
                        @endforeach
                      </div>
                      <p class="tenant-multiselect__empty" hidden data-multiselect-empty>No companies found.</p>
                    </div>
                    @error('tenant_company_id')<span class="tenant-form__error">{{ $message }}</span>@enderror
                    @error('tenant_id')<span class="tenant-form__error">{{ $message }}</span>@enderror
                  </div>

                  <label>
                    Company website URL
                    <input type="url" name="company_url" value="{{ old('company_url') }}">
                    <span class="input-description">Add a homepage, about page, or another relevant company page for this company.</span>
                    @error('company_url')<span class="tenant-form__error">{{ $message }}</span>@enderror
                  </label>
                </section>

                <section class="tenant-form-section-block tenant-form__section" aria-labelledby="job-details-title">
                  <div class="tenant-form-section-head">
                    <h2 id="job-details-title" class="tenant-form-section-title">Job details</h2>
                  </div>

                  <label>
                    Job title
                    <input name="title" value="{{ old('title') }}" required autofocus>
                    <span class="input-description">Example: "Senior Laravel Developer", "Software Engineer"</span>
                    @error('title')<span class="tenant-form__error">{{ $message }}</span>@enderror
                  </label>

                  <label>
                    Vacancy URL
                    <input type="url" name="job_url" value="{{ old('job_url') }}">
                    <span class="input-description">Add the link to this vacancy on the client website.</span>
                    @error('job_url')<span class="tenant-form__error">{{ $message }}</span>@enderror
                  </label>

                  <div class="tenant-post-job-form__half-grid">
                    <label>
                      Location
                      <input name="location" value="{{ old('location') }}" required>
                      @error('location')<span class="tenant-form__error">{{ $message }}</span>@enderror
                    </label>

                    <div class="tenant-form__field tenant-multiselect" data-multiselect data-multiselect-max="1">
                      <label id="dashboard-job-type-label">Job type</label>
                      <button
                        class="tenant-multiselect__button"
                        type="button"
                        aria-haspopup="listbox"
                        aria-expanded="false"
                        aria-labelledby="dashboard-job-type-label"
                        data-multiselect-button
                        data-multiselect-empty-label="Select job types"
                      >
                        Select job types
                      </button>
                      <div class="tenant-multiselect__menu" data-multiselect-menu>
                        <input
                          class="tenant-multiselect__search"
                          type="search"
                          aria-label="Search job types"
                          autocomplete="off"
                          data-multiselect-search
                        >
                        <div class="tenant-multiselect__options" role="listbox" aria-multiselectable="false">
                          @foreach($jobTypes as $jobType)
                            <label class="tenant-multiselect__option" data-multiselect-option-row>
                              <input
                                type="radio"
                                name="employment_type"
                                value="{{ $jobType }}"
                                @checked($selectedJobType === $jobType)
                                data-multiselect-option
                                data-multiselect-label="{{ $jobType }}"
                              >
                              <span>{{ $jobType }}</span>
                            </label>
                          @endforeach
                        </div>
                        <p class="tenant-multiselect__empty" hidden data-multiselect-empty>No job types found.</p>
                      </div>
                      @error('employment_type')<span class="tenant-form__error">{{ $message }}</span>@enderror
                    </div>
                  </div>

                  <div class="tenant-form__field tenant-rich-text" data-quill-field>
                    <label for="dashboard-job-description">Job description</label>
                    <textarea
                      id="dashboard-job-description"
                      name="description"
                      rows="8"
                      required
                      data-quill-source
                    >{{ old('description') }}</textarea>
                    <div
                      class="richtext-field tenant-rich-text__editor"
                      data-quill-editor
                    ></div>
                    @error('description')<span class="tenant-form__error">{{ $message }}</span>@enderror
                  </div>
                </section>

                <section class="tenant-form-section-block tenant-form__section" aria-labelledby="job-contact-title">
                  <div class="tenant-form-section-head">
                    <h2 id="job-contact-title" class="tenant-form-section-title">Contact details</h2>
                  </div>

                  <div class="tenant-form__grid">
                    <label>
                      First name
                      <input name="contact_first_name" value="{{ old('contact_first_name') }}" required>
                      @error('contact_first_name')<span class="tenant-form__error">{{ $message }}</span>@enderror
                    </label>

                    <label>
                      Last name
                      <input name="contact_last_name" value="{{ old('contact_last_name') }}" required>
                      @error('contact_last_name')<span class="tenant-form__error">{{ $message }}</span>@enderror
                    </label>
                  </div>

                  <div class="tenant-form__grid">
                    <label>
                      Phone number
                      <input name="contact_phone" value="{{ old('contact_phone') }}">
                      @error('contact_phone')<span class="tenant-form__error">{{ $message }}</span>@enderror
                    </label>

                    <label>
                      Email address
                      <input name="contact_email" type="email" value="{{ old('contact_email') }}" required>
                      @error('contact_email')<span class="tenant-form__error">{{ $message }}</span>@enderror
                    </label>
                  </div>
                </section>

                <div class="tenant-dashboard-form__actions">
                  <button class="tenant-btn tenant-btn--primary tenant-post-job-form__submit" type="submit" name="status" value="{{ \App\Models\TenantJob::STATUS_PUBLISHED }}">
                    Publish
                  </button>
                  <button class="tenant-btn tenant-btn--ghost tenant-post-job-form__submit" type="submit" name="status" value="{{ \App\Models\TenantJob::STATUS_DRAFT }}">
                    Save as draft
                  </button>
                  <a class="dash-link" href="{{ route('client.jobs.index') }}">Cancel</a>
                </div>
              </form>
            @endif
          </section>
        </main>

        <aside class="dash-form-layout__aside">
          <section class="dash-card dash-form-side">
            <h2>Job setup</h2>
            <p>Jobs are saved as published or draft items and appear in the jobs overview.</p>
            <ul>
              <li>Published jobs are visible on the public job board.</li>
              <li>Draft jobs stay hidden until they are published.</li>
              <li>Company and contact details stay linked to the job.</li>
            </ul>
          </section>
        </aside>
      </div>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>
  <script>
    (() => {
      const fields = document.querySelectorAll('[data-quill-field]');

      if (!fields.length || !window.Quill) {
        return;
      }

      const toolbar = [
        ['bold', 'italic', 'underline'],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['link'],
        ['clean'],
      ];
      const emptyValue = '<p><br></p>';

      fields.forEach((field) => {
        const source = field.querySelector('[data-quill-source]');
        const editor = field.querySelector('[data-quill-editor]');

        if (!source || !editor || editor.dataset.quillReady) {
          return;
        }

        const quill = new Quill(editor, {
          theme: 'snow',
          modules: { toolbar },
        });

        if (source.value.trim()) {
          quill.clipboard.dangerouslyPasteHTML(source.value);
        }

        const syncSource = () => {
          const html = quill.root.innerHTML;
          source.value = html === emptyValue ? '' : html;
        };

        quill.on('text-change', syncSource);
        source.form?.addEventListener('submit', syncSource);

        field.classList.add('is-enhanced');
        editor.dataset.quillReady = 'true';
        syncSource();
      });
    })();

    (() => {
      document.querySelectorAll('[data-multiselect]').forEach((multiselect) => {
        const button = multiselect.querySelector('[data-multiselect-button]');
        const search = multiselect.querySelector('[data-multiselect-search]');
        const empty = multiselect.querySelector('[data-multiselect-empty]');
        const options = Array.from(multiselect.querySelectorAll('[data-multiselect-option]'));
        const optionRows = Array.from(multiselect.querySelectorAll('[data-multiselect-option-row]'));
        const maxSelections = Number(multiselect.dataset.multiselectMax || 0);
        const emptyLabel = button?.dataset.multiselectEmptyLabel || 'Select';
        const tenantInput = multiselect.hasAttribute('data-company-select')
          ? document.querySelector('[data-company-tenant-input]')
          : null;

        if (!button || !options.length) {
          return;
        }

        const updateButton = () => {
          const selected = options.filter((option) => option.checked);
          const labels = selected.map((option) => option.dataset.multiselectLabel || option.value);

          button.textContent = labels.length ? labels.join(', ') : emptyLabel;

          if (tenantInput && selected[0]?.dataset.companyTenantId) {
            tenantInput.value = selected[0].dataset.companyTenantId;
          }
        };

        button.addEventListener('click', () => {
          const isOpen = multiselect.classList.toggle('is-open');
          button.setAttribute('aria-expanded', String(isOpen));

          if (isOpen) {
            search?.focus();
          }
        });

        options.forEach((option) => {
          option.addEventListener('change', () => {
            if (maxSelections === 1 && option.checked) {
              options.forEach((otherOption) => {
                if (otherOption !== option) {
                  otherOption.checked = false;
                }
              });

              multiselect.classList.remove('is-open');
              button.setAttribute('aria-expanded', 'false');
            }

            updateButton();
          });
        });

        search?.addEventListener('input', () => {
          const query = search.value.trim().toLowerCase();
          let visibleCount = 0;

          optionRows.forEach((row) => {
            const label = row.textContent.trim().toLowerCase();
            const isVisible = label.includes(query);

            row.hidden = !isVisible;
            visibleCount += isVisible ? 1 : 0;
          });

          if (empty) {
            empty.hidden = visibleCount > 0;
          }
        });

        document.addEventListener('click', (event) => {
          if (!multiselect.contains(event.target)) {
            multiselect.classList.remove('is-open');
            button.setAttribute('aria-expanded', 'false');
          }
        });

        document.addEventListener('keydown', (event) => {
          if (event.key === 'Escape') {
            multiselect.classList.remove('is-open');
            button.setAttribute('aria-expanded', 'false');
          }
        });

        updateButton();
      });
    })();
  </script>
@endpush
