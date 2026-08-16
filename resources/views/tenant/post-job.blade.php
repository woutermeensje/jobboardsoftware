@extends('layouts.tenant')

@section('title', 'Post a job | '.$brandName)
@section('meta_description', 'Submit a job for this job board.')

@php
  $selectedJobTypes = collect((array) old('employment_type', []));
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
      <article class="tenant-panel tenant-post-job__main">
        <form method="POST" action="{{ route('tenant.post-job.store') }}" class="tenant-post-job-form">
          @csrf

          <div class="tenant-panel__head tenant-form-header">
            <h2 class="tenant-form-title">Submit a vacancy</h2>
            <p>Your job will be saved as a draft first. Publishing and payment can be completed in the next step later.</p>
          </div>

          <div class="tenant-post-job-form__section">
            <div class="tenant-form-section-head">
              <h2 class="tenant-form-section-title">Job details</h2>
            </div>

            <label>
              Job title
              <input name="title" value="{{ old('title') }}" placeholder="Senior Laravel Developer" required autofocus>
              @error('title')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
            </label>

            <label>
              Location
              <input name="location" value="{{ old('location') }}" placeholder="Amsterdam or remote" required>
              @error('location')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
            </label>

            <div class="tenant-post-job-form__half-grid">
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
                <div class="tenant-multiselect__menu" role="listbox" aria-multiselectable="true" data-multiselect-menu>
                  @foreach($jobTypes as $jobType)
                    <label class="tenant-multiselect__option">
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
                @error('employment_type')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
                @error('employment_type.*')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
              </div>
            </div>

            <div class="tenant-post-job-form__field tenant-rich-text" data-quill-field>
              <label for="job-description">Job description</label>
              <textarea
                id="job-description"
                name="description"
                rows="8"
                placeholder="Describe responsibilities, requirements and benefits."
                required
                data-quill-source
              >{{ old('description') }}</textarea>
              <div
                class="tenant-rich-text__editor"
                data-quill-editor
                data-placeholder="Describe responsibilities, requirements and benefits."
              ></div>
              @error('description')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
            </div>
          </div>

          <div class="tenant-post-job-form__section">
            <div class="tenant-form-section-head">
              <h2 class="tenant-form-section-title">Contact details</h2>
            </div>

            <div class="tenant-post-job-form__grid">
              <label>
                First name
                <input name="contact_first_name" value="{{ old('contact_first_name') }}" placeholder="Jane" required>
                @error('contact_first_name')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
              </label>

              <label>
                Last name
                <input name="contact_last_name" value="{{ old('contact_last_name') }}" placeholder="Doe" required>
                @error('contact_last_name')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
              </label>
            </div>

            <div class="tenant-post-job-form__grid">
              <label>
                Phone number
                <input name="contact_phone" value="{{ old('contact_phone') }}" placeholder="+1 555 123 4567">
                @error('contact_phone')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
              </label>

              <label>
                Email address
                <input name="contact_email" type="email" value="{{ old('contact_email') }}" placeholder="jane@example.com" required>
                @error('contact_email')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
              </label>
            </div>
          </div>

          <div class="tenant-post-job-account">
            <div class="tenant-form-section-head">
              <h2 class="tenant-form-section-title">Create account</h2>
            </div>

            <label class="tenant-post-job-form__check">
              <input type="checkbox" name="create_account" value="1" @checked(old('create_account'))>
              <span>
                Create an employer account with this submission
                <small>Use the password fields below if you want an account for managing this job later.</small>
              </span>
            </label>

            <div class="tenant-post-job-form__grid">
              <label>
                Password
                <input name="password" type="password" autocomplete="new-password">
                @error('password')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
              </label>

              <label>
                Confirm password
                <input name="password_confirmation" type="password" autocomplete="new-password">
              </label>
            </div>
          </div>

          <button class="tenant-btn tenant-btn--primary tenant-post-job-form__submit" type="submit">
            Submit job
          </button>
        </form>
      </article>

      <aside class="tenant-panel tenant-post-job__aside">
        <p class="tenant-eyebrow">Next step</p>
        <h2 class="tenant-form-section-title">Draft first</h2>
        <p>Submitted jobs are stored as drafts. The payment step will be connected later, before publishing.</p>
        <div class="tenant-post-job__summary">
          <span>Default job types</span>
          <strong>{{ count($jobTypes) }}</strong>
        </div>
      </aside>
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
      document.querySelectorAll('[data-multiselect]').forEach((multiselect) => {
        const button = multiselect.querySelector('[data-multiselect-button]');
        const options = Array.from(multiselect.querySelectorAll('[data-multiselect-option]'));

        if (!button || !options.length) {
          return;
        }

        const updateButton = () => {
          const selected = options
            .filter((option) => option.checked)
            .map((option) => option.value);

          button.textContent = selected.length ? selected.join(', ') : 'Select job types';
        };

        button.addEventListener('click', () => {
          const isOpen = multiselect.classList.toggle('is-open');
          button.setAttribute('aria-expanded', String(isOpen));
        });

        options.forEach((option) => {
          option.addEventListener('change', updateButton);
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
