@extends('layouts.app')

@section('title', 'Create job | Client dashboard')
@section('meta_description', 'Create a job for a job board environment.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('client.partials.navigation')
@endsection

@php
  $selectedTenantId = (string) old('tenant_id', $tenants->first()?->id);
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
            @else
              <form class="tenant-form tenant-dashboard-form" method="POST" action="{{ route('client.jobs.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="tenant-form-header tenant-dashboard-form__header">
                  <div>
                    <h2 class="tenant-form-title">Create job</h2>
                    <p class="tenant-form-intro">Add a vacancy to one of your job board environments.</p>
                  </div>
                  <a class="dash-link" href="{{ route('client.jobs.index') }}">Back to jobs</a>
                </div>

                <section class="tenant-form-section-block tenant-form__section" aria-labelledby="job-publishing-title">
                  <div class="tenant-form-section-head">
                    <h2 id="job-publishing-title" class="tenant-form-section-title">Publishing</h2>
                  </div>

                  <div class="tenant-form__grid">
                    <label>
                      Environment
                      <select name="tenant_id" required>
                        @foreach($tenants as $tenant)
                          <option value="{{ $tenant->id }}" @selected($selectedTenantId === (string) $tenant->id)>
                            {{ $tenant->name }} ({{ $tenant->slug }})
                          </option>
                        @endforeach
                      </select>
                      @error('tenant_id')<span class="tenant-form__error">{{ $message }}</span>@enderror
                    </label>

                    <label>
                      Status
                      <select name="status" required>
                        <option value="{{ \App\Models\TenantJob::STATUS_DRAFT }}" @selected(old('status', \App\Models\TenantJob::STATUS_DRAFT) === \App\Models\TenantJob::STATUS_DRAFT)>Draft</option>
                        <option value="{{ \App\Models\TenantJob::STATUS_PUBLISHED }}" @selected(old('status') === \App\Models\TenantJob::STATUS_PUBLISHED)>Published</option>
                      </select>
                      @error('status')<span class="tenant-form__error">{{ $message }}</span>@enderror
                    </label>
                  </div>
                </section>

                <section class="tenant-form-section-block tenant-form__section" aria-labelledby="job-details-title">
                  <div class="tenant-form-section-head">
                    <h2 id="job-details-title" class="tenant-form-section-title">Job details</h2>
                  </div>

                  <div class="tenant-post-job-form__logo-title-grid">
                    <label class="tenant-form__field tenant-post-job-form__logo-field">
                      Company logo
                      <span class="tenant-logo-upload tenant-logo-upload--with-filename" data-file-picker>
                        <span class="tenant-file-picker__button">Choose file</span>
                        <span class="tenant-file-picker__filename" data-file-name data-empty-label="No file selected">No file selected</span>
                        <input type="file" name="company_logo" accept=".jpg,.jpeg,.png,.webp,.svg,image/jpeg,image/png,image/webp,image/svg+xml">
                      </span>
                      <span class="input-description">Upload a PNG, JPG, WebP or SVG logo. Maximum file size: 2 MB.</span>
                      @error('company_logo')<span class="tenant-form__error">{{ $message }}</span>@enderror
                    </label>

                    <label>
                      Job title
                      <input name="title" value="{{ old('title') }}" required autofocus>
                      @error('title')<span class="tenant-form__error">{{ $message }}</span>@enderror
                    </label>
                  </div>

                  <div class="tenant-form__grid">
                    <label>
                      Select company
                      <select name="tenant_company_id">
                        <option value="">Add a new company</option>
                        @foreach($tenants as $tenant)
                          @php
                            $tenantCompanies = $companies->where('tenant_id', $tenant->id);
                          @endphp
                          @if($tenantCompanies->isNotEmpty())
                            <optgroup label="{{ $tenant->name }}">
                              @foreach($tenantCompanies as $company)
                                <option value="{{ $company->id }}" @selected((string) old('tenant_company_id') === (string) $company->id)>{{ $company->name }}</option>
                              @endforeach
                            </optgroup>
                          @endif
                        @endforeach
                      </select>
                      @error('tenant_company_id')<span class="tenant-form__error">{{ $message }}</span>@enderror
                    </label>

                    <label>
                      Company name
                      <input name="company_name" value="{{ old('company_name') }}">
                      @error('company_name')<span class="tenant-form__error">{{ $message }}</span>@enderror
                    </label>
                  </div>

                  <div class="tenant-form__grid">
                    <label>
                      Category
                      <input name="category" value="{{ old('category') }}" list="client-job-categories" required>
                      @error('category')<span class="tenant-form__error">{{ $message }}</span>@enderror
                    </label>

                    <label>
                      Job type
                      <select name="employment_type" required>
                        <option value="">Select a job type</option>
                        @foreach($jobTypes as $jobType)
                          <option value="{{ $jobType }}" @selected(old('employment_type') === $jobType)>{{ $jobType }}</option>
                        @endforeach
                      </select>
                      @error('employment_type')<span class="tenant-form__error">{{ $message }}</span>@enderror
                    </label>
                  </div>

                  <datalist id="client-job-categories">
                    @foreach($categories as $category)
                      <option value="{{ $category }}"></option>
                    @endforeach
                  </datalist>

                  <div class="tenant-form__grid">
                    <label>
                      Location
                      <input name="location" value="{{ old('location') }}" required>
                      @error('location')<span class="tenant-form__error">{{ $message }}</span>@enderror
                    </label>

                    <label>
                      Salary range
                      <input name="salary_range" value="{{ old('salary_range') }}">
                      @error('salary_range')<span class="tenant-form__error">{{ $message }}</span>@enderror
                    </label>
                  </div>

                  <div class="tenant-form__field tenant-rich-text" data-quill-field>
                    <label for="dashboard-job-intro">Short intro</label>
                    <textarea
                      id="dashboard-job-intro"
                      name="intro"
                      rows="3"
                      maxlength="3000"
                      data-quill-source
                    >{{ old('intro') }}</textarea>
                    <div
                      class="richtext-field tenant-rich-text__editor tenant-rich-text__editor--short"
                      data-quill-editor
                    ></div>
                    @error('intro')<span class="tenant-form__error">{{ $message }}</span>@enderror
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
                      Contact name
                      <input name="contact_name" value="{{ old('contact_name') }}">
                      @error('contact_name')<span class="tenant-form__error">{{ $message }}</span>@enderror
                    </label>

                    <label>
                      Email address
                      <input type="email" name="contact_email" value="{{ old('contact_email') }}">
                      @error('contact_email')<span class="tenant-form__error">{{ $message }}</span>@enderror
                    </label>
                  </div>

                  <div class="tenant-form__grid">
                    <label>
                      Phone number
                      <input name="contact_phone" value="{{ old('contact_phone') }}">
                      @error('contact_phone')<span class="tenant-form__error">{{ $message }}</span>@enderror
                    </label>

                    <label>
                      Closing date
                      <input type="date" name="closes_at" value="{{ old('closes_at') }}">
                      @error('closes_at')<span class="tenant-form__error">{{ $message }}</span>@enderror
                    </label>
                  </div>
                </section>

                <div class="tenant-dashboard-form__actions">
                  <button class="tenant-btn tenant-btn--primary tenant-post-job-form__submit" type="submit">
                    Create job
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
            <p>Jobs are saved to the selected environment and appear on that tenant job board when published.</p>
            <ul>
              <li>Draft jobs stay hidden from the public job board.</li>
              <li>Published jobs receive a publication date immediately.</li>
              <li>Company data can be reused from saved company profiles.</li>
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
  </script>
@endpush
