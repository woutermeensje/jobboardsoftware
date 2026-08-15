@extends('layouts.tenant')

@section('title', 'Post a job | '.$brandName)
@section('meta_description', 'Submit a job for this job board.')

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
        <form method="POST" action="{{ route('tenant.post-job.store') }}" class="tenant-post-job-form" enctype="multipart/form-data">
          @csrf

          <div class="tenant-panel__head tenant-form-header">
            <p class="tenant-eyebrow">Post a job</p>
            <h2 class="tenant-form-title">Submit a vacancy</h2>
            <p>Your job will be saved as a draft first. Publishing and payment can be completed in the next step later.</p>
          </div>

          <div class="tenant-post-job-form__section">
            <div class="tenant-form-section-head">
              <h2 class="tenant-form-section-title">Job details</h2>
            </div>

            <div class="tenant-post-job-form__logo-title-grid">
              <label class="tenant-post-job-form__logo-field">
                Company logo
                <span class="tenant-logo-upload" data-file-picker>
                  <i class="ph ph-image-square" aria-hidden="true"></i>
                  <span class="tenant-logo-upload__copy">
                    Upload logo
                    <small>PNG, JPG, WebP or SVG. Max 2 MB.</small>
                  </span>
                  <span class="tenant-file-picker__button">Choose file</span>
                  <span class="tenant-file-picker__filename" data-file-name data-empty-label="No file selected">No file selected</span>
                  <input type="file" name="company_logo" accept=".jpg,.jpeg,.png,.webp,.svg,image/jpeg,image/png,image/webp,image/svg+xml">
                </span>
                @error('company_logo')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
              </label>

              <label>
                Job title
                <input name="title" value="{{ old('title') }}" placeholder="Senior Laravel Developer" required autofocus>
                @error('title')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
              </label>
            </div>

            <div class="tenant-post-job-form__company-grid">
              <label>
                Select company
                <select name="tenant_company_id">
                  <option value="">Add a new company</option>
                  @foreach($companies as $company)
                    <option value="{{ $company->id }}" @selected((string) old('tenant_company_id') === (string) $company->id)>{{ $company->name }}</option>
                  @endforeach
                </select>
                @error('tenant_company_id')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
              </label>

              <label>
                Company name
                <input name="company_name" value="{{ old('company_name') }}" placeholder="Enter company name if not listed">
                @error('company_name')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
              </label>
            </div>

            <div class="tenant-post-job-form__grid">
              <label>
                Category
                <input name="category" value="{{ old('category') }}" list="tenant-job-categories" placeholder="Development" required>
                @error('category')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
              </label>

              <label>
                Job type
                <select name="employment_type" required>
                  <option value="">Select a job type</option>
                  @foreach($jobTypes as $jobType)
                    <option value="{{ $jobType }}" @selected(old('employment_type') === $jobType)>{{ $jobType }}</option>
                  @endforeach
                </select>
                @error('employment_type')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
              </label>
            </div>

            <datalist id="tenant-job-categories">
              @foreach($categories as $category)
                <option value="{{ $category }}"></option>
              @endforeach
            </datalist>

            <div class="tenant-post-job-form__grid">
              <label>
                Location
                <input name="location" value="{{ old('location') }}" placeholder="Amsterdam or remote" required>
                @error('location')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
              </label>

              <label>
                Salary range
                <input name="salary_range" value="{{ old('salary_range') }}" placeholder="Optional">
                @error('salary_range')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
              </label>
            </div>

            <div class="tenant-post-job-form__field tenant-rich-text" data-quill-field>
              <label for="job-intro">Short intro</label>
              <textarea
                id="job-intro"
                name="intro"
                rows="3"
                maxlength="1000"
                placeholder="Summarize the role in one or two sentences."
                data-quill-source
              >{{ old('intro') }}</textarea>
              <div
                class="tenant-rich-text__editor tenant-rich-text__editor--short"
                data-quill-editor
                data-placeholder="Summarize the role in one or two sentences."
              ></div>
              @error('intro')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
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
                Contact name
                <input name="contact_name" value="{{ old('contact_name') }}" placeholder="Jane Doe" required>
                @error('contact_name')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
              </label>

              <label>
                Email address
                <input name="contact_email" type="email" value="{{ old('contact_email') }}" placeholder="jane@example.com" required>
                @error('contact_email')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
              </label>
            </div>

            <label>
              Phone number
              <input name="contact_phone" value="{{ old('contact_phone') }}" placeholder="+1 555 123 4567">
              @error('contact_phone')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
            </label>
          </div>

          <div class="tenant-post-job-form__section tenant-post-job-form__account">
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
  </script>
@endpush
