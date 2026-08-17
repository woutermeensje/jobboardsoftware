@extends('layouts.tenant')

@section('title', 'Post a job | '.$brandName)
@section('meta_description', 'Submit a job for this job board.')

@php
  $selectedJobTypes = collect((array) old('employment_type', []));
  $selectedPackageId = (string) old('tenant_package_id', '');
  $selectedCountry = (string) old('country', '');
  $selectedIsRemote = in_array((string) old('is_remote', '1'), ['1', 'true', 'on'], true);
@endphp

@section('content')
  <main class="tenant-shell">
    @if(session('status'))
      <section class="tenant-alert">{{ session('status') }}</section>
    @endif

    @if($errors->any())
      <section class="tenant-alert tenant-alert--danger">
        <strong>Job could not be submitted.</strong>
        <ul class="tenant-message-list">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </section>
    @endif

    <section class="tenant-post-job">
      <div class="tenant-post-job__content">
        <article class="tenant-panel tenant-post-job__main">
          <form id="tenant-post-job-form" method="POST" action="{{ route('tenant.post-job.store') }}" class="tenant-post-job-form" enctype="multipart/form-data">
            @csrf

            <div class="tenant-panel__head tenant-form-header">
              <h2 class="tenant-form-title">Submit a job.</h2>
              <p class="tenant-form-intro">Fill in the details below and post your job.</p>
            </div>

            <section class="tenant-form-section-block tenant-post-job-form__section" aria-labelledby="tenant-package-title">
              <div class="tenant-form-section-head">
                <h2 id="tenant-package-title" class="tenant-form-section-title">Choose your package</h2>
              </div>

              <div class="tenant-post-job-form__field tenant-multiselect" data-multiselect data-multiselect-max="1">
                <label id="tenant-package-label">Package</label>
                <button
                  class="tenant-multiselect__button"
                  type="button"
                  aria-haspopup="listbox"
                  aria-expanded="false"
                  aria-labelledby="tenant-package-label"
                  data-multiselect-button
                  data-multiselect-placeholder="Select a package"
                  @disabled($packages->isEmpty())
                >
                  Select a package
                </button>
                <div class="tenant-multiselect__menu" data-multiselect-menu>
                  <input
                    class="tenant-multiselect__search"
                    type="search"
                    placeholder="Search packages"
                    aria-label="Search packages"
                    autocomplete="off"
                    data-multiselect-search
                  >
                  <div class="tenant-multiselect__options" role="listbox" aria-multiselectable="false">
                    @foreach($packages as $package)
                      @php
                        $packageLabel = $package->displayLabel();
                      @endphp
                      <label class="tenant-multiselect__option" data-multiselect-option-row>
                        <input
                          type="radio"
                          name="tenant_package_id"
                          value="{{ $package->id }}"
                          @checked($selectedPackageId === (string) $package->id)
                          data-multiselect-option
                          data-multiselect-label="{{ $packageLabel }}"
                        >
                        <span>{{ $packageLabel }}</span>
                      </label>
                    @endforeach
                  </div>
                  <p class="tenant-multiselect__empty" @if($packages->isNotEmpty()) hidden @endif data-multiselect-empty>No packages found.</p>
                </div>
                @error('tenant_package_id')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
              </div>
            </section>

            <section class="tenant-form-section-block tenant-post-job-form__section" aria-labelledby="tenant-job-details-title">
              <div class="tenant-form-section-head">
                <h2 id="tenant-job-details-title" class="tenant-form-section-title">Job details</h2>
              </div>

              <label>
                Job title
                <input name="title" value="{{ old('title') }}" required autofocus>
                <span class="input-description">Example: "Senior Laravel Developer", "Software Engineer"</span>
                @error('title')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
              </label>

              <div class="tenant-post-job-form__field tenant-multiselect" data-multiselect>
                <label id="tenant-job-type-label">Job type</label>
                <button
                  class="tenant-multiselect__button"
                  type="button"
                  aria-haspopup="listbox"
                  aria-expanded="false"
                  aria-labelledby="tenant-job-type-label"
                  data-multiselect-button
                >
                  Select job types
                </button>
                <div class="tenant-multiselect__menu" data-multiselect-menu>
                  <input
                    class="tenant-multiselect__search"
                    type="search"
                    placeholder="Search job types"
                    aria-label="Search job types"
                    autocomplete="off"
                    data-multiselect-search
                  >
                  <div class="tenant-multiselect__options" role="listbox" aria-multiselectable="true">
                    @foreach($jobTypes as $jobType)
                      <label class="tenant-multiselect__option" data-multiselect-option-row>
                        <input
                          type="checkbox"
                          name="employment_type[]"
                          value="{{ $jobType }}"
                          @checked($selectedJobTypes->contains($jobType))
                          data-multiselect-option
                        >
                        <span>{{ $jobType }}</span>
                      </label>
                    @endforeach
                  </div>
                  <p class="tenant-multiselect__empty" hidden data-multiselect-empty>No job types found.</p>
                </div>
                @error('employment_type')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
                @error('employment_type.*')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
              </div>

              <div class="tenant-post-job-form__field tenant-rich-text" data-quill-field>
                <label for="job-description">Job description</label>
                <textarea
                  id="job-description"
                  name="description"
                  rows="8"
                  required
                  data-quill-source
                >{{ old('description') }}</textarea>
                <div
                  class="tenant-rich-text__editor"
                  data-quill-editor
                ></div>
                @error('description')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
              </div>
            </section>

            <section class="tenant-form-section-block tenant-post-job-form__section" aria-labelledby="tenant-location-title">
              <div class="tenant-form-section-head">
                <h2 id="tenant-location-title" class="tenant-form-section-title">Location</h2>
              </div>

              <fieldset class="tenant-remote-toggle" data-remote-position-toggle>
                <legend>Is this a remote position?</legend>
                <div class="tenant-remote-toggle__choices">
                  <label class="tenant-remote-toggle__option">
                    <input type="radio" name="is_remote" value="1" @checked($selectedIsRemote) data-remote-position-input>
                    <span>Yes</span>
                  </label>
                  <label class="tenant-remote-toggle__option">
                    <input type="radio" name="is_remote" value="0" @checked(! $selectedIsRemote) data-remote-position-input>
                    <span>No</span>
                  </label>
                </div>
                @error('is_remote')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
              </fieldset>

              <div class="tenant-post-job-form__grid" data-remote-location-fields @if($selectedIsRemote) hidden @endif>
                <label>
                  Location
                  <input
                    name="location"
                    value="{{ old('location') }}"
                    @if(! $selectedIsRemote) required @endif
                    @disabled($selectedIsRemote)
                    data-remote-controlled
                    data-remote-required="true"
                  >
                  <span class="input-description">Enter the city or place where this job is based.</span>
                  @error('location')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
                </label>

                <div class="tenant-post-job-form__field tenant-multiselect" data-multiselect data-multiselect-max="1">
                  <label id="tenant-country-label">Country</label>
                  <button
                    class="tenant-multiselect__button"
                    type="button"
                    aria-haspopup="listbox"
                    aria-expanded="false"
                    aria-labelledby="tenant-country-label"
                    data-multiselect-button
                    data-multiselect-placeholder="Select country"
                  >
                    Select country
                  </button>
                  <div class="tenant-multiselect__menu" data-multiselect-menu>
                    <input
                      class="tenant-multiselect__search"
                      type="search"
                      placeholder="Search countries"
                      aria-label="Search countries"
                      autocomplete="off"
                      data-multiselect-search
                    >
                    <div class="tenant-multiselect__options" role="listbox" aria-multiselectable="false">
                      @foreach($countries as $country)
                        <label class="tenant-multiselect__option" data-multiselect-option-row>
                          <input
                            type="radio"
                            name="country"
                            value="{{ $country['code'] }}"
                            @checked($selectedCountry === $country['code'])
                            @if(! $selectedIsRemote) required @endif
                            @disabled($selectedIsRemote)
                            data-multiselect-option
                            data-multiselect-label="{{ $country['label'] }}"
                            data-remote-controlled
                            data-remote-required="true"
                          >
                          <span class="tenant-country-option">
                            <span class="tenant-country-option__flag" aria-hidden="true">{{ $country['flag'] }}</span>
                            <span class="tenant-country-option__name">{{ $country['name'] }}</span>
                          </span>
                        </label>
                      @endforeach
                    </div>
                    <p class="tenant-multiselect__empty" hidden data-multiselect-empty>No countries found.</p>
                  </div>
                  @error('country')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
                </div>
              </div>
            </section>

            <section class="tenant-form-section-block tenant-post-job-form__section" aria-labelledby="tenant-company-information-title">
              <div class="tenant-form-section-head">
                <h2 id="tenant-company-information-title" class="tenant-form-section-title">Company information</h2>
              </div>

              <label class="tenant-post-job-form__field tenant-post-job-form__logo-field">
                Add logo
                <span class="tenant-logo-upload" data-file-picker>
                  <span class="tenant-file-picker__button">Choose file</span>
                  <input type="file" name="company_logo" accept=".jpg,.jpeg,.png,.webp,.svg,image/jpeg,image/png,image/webp,image/svg+xml">
                </span>
                @error('company_logo')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
              </label>
            </section>

            <section class="tenant-form-section-block tenant-post-job-form__section" aria-labelledby="tenant-contact-details-title">
              <div class="tenant-form-section-head">
                <h2 id="tenant-contact-details-title" class="tenant-form-section-title">Contact details</h2>
              </div>

              <div class="tenant-post-job-form__grid">
                <label>
                  First name
                  <input name="contact_first_name" value="{{ old('contact_first_name') }}" required>
                  @error('contact_first_name')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
                </label>

                <label>
                  Last name
                  <input name="contact_last_name" value="{{ old('contact_last_name') }}" required>
                  @error('contact_last_name')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
                </label>
              </div>

              <div class="tenant-post-job-form__grid">
                <label>
                  Phone number
                  <input name="contact_phone" value="{{ old('contact_phone') }}">
                  @error('contact_phone')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
                </label>

                <label>
                  Email address
                  <input name="contact_email" type="email" value="{{ old('contact_email') }}" required>
                  @error('contact_email')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
                </label>
              </div>
            </section>
          </form>
        </article>

        <article class="tenant-panel tenant-post-job__account-panel">
          <div class="tenant-post-job-account">
            <div class="tenant-form-section-head">
              <h2 class="tenant-form-section-title">Do you want to create an employer account?</h2>
            </div>

            <label class="tenant-post-job-form__check">
              <input form="tenant-post-job-form" type="checkbox" name="create_account" value="1" @checked(old('create_account'))>
              <span class="checkbox-text">
                Yes, i want to create an account!
              </span>
            </label>

            <div class="tenant-post-job-form__grid">
              <label>
                Password
                <input form="tenant-post-job-form" name="password" type="password" autocomplete="new-password">
                @error('password')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
              </label>

              <label>
                Confirm password
                <input form="tenant-post-job-form" name="password_confirmation" type="password" autocomplete="new-password">
              </label>
            </div>
          </div>
        </article>

        <button class="tenant-btn tenant-btn--primary tenant-post-job-form__submit" type="submit" form="tenant-post-job-form">
          Submit job
        </button>
      </div>

    </section>
  </main>
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
          placeholder: editor.dataset.placeholder || '',
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
      document.querySelectorAll('[data-remote-position-toggle]').forEach((toggle) => {
        const section = toggle.closest('[data-remote-position-section]') || toggle.closest('.tenant-form-section-block');
        const fields = section?.querySelector('[data-remote-location-fields]');
        const remoteInputs = Array.from(toggle.querySelectorAll('[data-remote-position-input]'));
        const controlledInputs = fields ? Array.from(fields.querySelectorAll('[data-remote-controlled]')) : [];

        const updateRemoteFields = () => {
          const selected = remoteInputs.find((input) => input.checked);
          const isRemote = selected ? selected.value !== '0' : true;

          if (fields) {
            fields.hidden = isRemote;
          }

          controlledInputs.forEach((input) => {
            input.disabled = isRemote;
            input.required = !isRemote && input.dataset.remoteRequired === 'true';
          });

          if (isRemote && fields) {
            fields.querySelectorAll('[data-multiselect]').forEach((multiselect) => {
              multiselect.classList.remove('is-open');
              multiselect.querySelector('[data-multiselect-button]')?.setAttribute('aria-expanded', 'false');
            });
          }
        };

        remoteInputs.forEach((input) => input.addEventListener('change', updateRemoteFields));
        updateRemoteFields();
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
        const placeholder = button?.dataset.multiselectPlaceholder || 'Select job types';

        if (!button || !options.length) {
          return;
        }

        const updateButton = () => {
          const selected = options
            .filter((option) => option.checked)
            .map((option) => option.dataset.multiselectLabel || option.value);

          button.textContent = selected.length ? selected.join(', ') : placeholder;
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
