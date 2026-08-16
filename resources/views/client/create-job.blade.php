@extends('layouts.app')

@section('title', 'Create job | Client dashboard')
@section('meta_description', 'Create a job for a job board environment.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('client.partials.navigation')
@endsection

@php
  $selectedTenantId = old('tenant_id', $tenants->first()?->id);
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
            <div class="dash-panel__head">
              <div>
                <h2>Create job</h2>
                <p>Add a vacancy to one of your job board environments.</p>
              </div>
              <a class="dash-link" href="{{ route('client.jobs.index') }}">Back to jobs</a>
            </div>

            @if($tenants->isEmpty())
              <div class="dash-empty-state">
                <h3>No environments yet</h3>
                <p>Create an environment before adding jobs.</p>
                <a class="dash-link" href="{{ route('client.environments.create') }}">Create environment</a>
              </div>
            @else
              <form class="domain-form dash-job-form" method="POST" action="{{ route('client.jobs.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="domain-form__grid">
                  <label class="domain-field">
                    Environment
                    <select name="tenant_id" required>
                      @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}" @selected($selectedTenantId === $tenant->id)>
                          {{ $tenant->name }} ({{ $tenant->slug }})
                        </option>
                      @endforeach
                    </select>
                    @error('tenant_id')<span class="domain-field__error">{{ $message }}</span>@enderror
                  </label>

                  <label class="domain-field">
                    Status
                    <select name="status" required>
                      <option value="{{ \App\Models\TenantJob::STATUS_DRAFT }}" @selected(old('status', \App\Models\TenantJob::STATUS_DRAFT) === \App\Models\TenantJob::STATUS_DRAFT)>Draft</option>
                      <option value="{{ \App\Models\TenantJob::STATUS_PUBLISHED }}" @selected(old('status') === \App\Models\TenantJob::STATUS_PUBLISHED)>Published</option>
                    </select>
                    @error('status')<span class="domain-field__error">{{ $message }}</span>@enderror
                  </label>
                </div>

                <div class="domain-form__section-head domain-form__section-head--first">
                  <h3>Job details</h3>
                </div>

                <div class="dash-job-form__logo-title-grid">
                  <label class="domain-field">
                    Company logo
                    <span class="company-logo-upload" data-file-picker>
                      <i class="ph ph-image-square" aria-hidden="true"></i>
                      <span>
                        Upload a PNG, JPG, WebP or SVG logo.
                        <small>Maximum file size: 2 MB.</small>
                      </span>
                      <span class="dash-file-picker__button">Choose file</span>
                      <span class="dash-file-picker__filename" data-file-name data-empty-label="No file selected">No file selected</span>
                      <input type="file" name="company_logo" accept=".jpg,.jpeg,.png,.webp,.svg,image/jpeg,image/png,image/webp,image/svg+xml">
                    </span>
                    @error('company_logo')<span class="domain-field__error">{{ $message }}</span>@enderror
                  </label>

                  <label class="domain-field">
                    Job title
                    <input name="title" value="{{ old('title') }}" placeholder="Senior Laravel Developer" required autofocus>
                    @error('title')<span class="domain-field__error">{{ $message }}</span>@enderror
                  </label>
                </div>

                <div class="domain-form__grid">
                  <label class="domain-field">
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
                    @error('tenant_company_id')<span class="domain-field__error">{{ $message }}</span>@enderror
                  </label>

                  <label class="domain-field">
                    Company name
                    <input name="company_name" value="{{ old('company_name') }}" placeholder="Enter company name if not listed">
                    @error('company_name')<span class="domain-field__error">{{ $message }}</span>@enderror
                  </label>
                </div>

                <div class="domain-form__grid">
                  <label class="domain-field">
                    Category
                    <input name="category" value="{{ old('category') }}" list="client-job-categories" placeholder="Development" required>
                    @error('category')<span class="domain-field__error">{{ $message }}</span>@enderror
                  </label>

                  <label class="domain-field">
                    Job type
                    <select name="employment_type" required>
                      <option value="">Select a job type</option>
                      @foreach($jobTypes as $jobType)
                        <option value="{{ $jobType }}" @selected(old('employment_type') === $jobType)>{{ $jobType }}</option>
                      @endforeach
                    </select>
                    @error('employment_type')<span class="domain-field__error">{{ $message }}</span>@enderror
                  </label>
                </div>

                <datalist id="client-job-categories">
                  @foreach($categories as $category)
                    <option value="{{ $category }}"></option>
                  @endforeach
                </datalist>

                <div class="domain-form__grid">
                  <label class="domain-field">
                    Location
                    <input name="location" value="{{ old('location') }}" placeholder="Amsterdam or remote" required>
                    @error('location')<span class="domain-field__error">{{ $message }}</span>@enderror
                  </label>

                  <label class="domain-field">
                    Salary range
                    <input name="salary_range" value="{{ old('salary_range') }}" placeholder="Optional">
                    @error('salary_range')<span class="domain-field__error">{{ $message }}</span>@enderror
                  </label>
                </div>

                <div class="domain-field dash-rich-text" data-quill-field>
                  <label for="dashboard-job-intro">Short intro</label>
                  <textarea
                    id="dashboard-job-intro"
                    name="intro"
                    rows="3"
                    maxlength="3000"
                    placeholder="Summarize the role in one or two sentences."
                    data-quill-source
                  >{{ old('intro') }}</textarea>
                  <div
                    class="richtext-field dash-rich-text__editor dash-rich-text__editor--short"
                    data-quill-editor
                    data-placeholder="Summarize the role in one or two sentences."
                  ></div>
                  @error('intro')<span class="domain-field__error">{{ $message }}</span>@enderror
                </div>

                <div class="domain-field dash-rich-text" data-quill-field>
                  <label for="dashboard-job-description">Job description</label>
                  <textarea
                    id="dashboard-job-description"
                    name="description"
                    rows="8"
                    placeholder="Describe responsibilities, requirements and benefits."
                    required
                    data-quill-source
                  >{{ old('description') }}</textarea>
                  <div
                    class="richtext-field dash-rich-text__editor"
                    data-quill-editor
                    data-placeholder="Describe responsibilities, requirements and benefits."
                  ></div>
                  @error('description')<span class="domain-field__error">{{ $message }}</span>@enderror
                </div>

                <div class="domain-form__section-head">
                  <h3>Contact details</h3>
                </div>

                <div class="domain-form__grid">
                  <label class="domain-field">
                    Contact name
                    <input name="contact_name" value="{{ old('contact_name') }}" placeholder="Jane Doe">
                    @error('contact_name')<span class="domain-field__error">{{ $message }}</span>@enderror
                  </label>

                  <label class="domain-field">
                    Email address
                    <input type="email" name="contact_email" value="{{ old('contact_email') }}" placeholder="jane@example.com">
                    @error('contact_email')<span class="domain-field__error">{{ $message }}</span>@enderror
                  </label>
                </div>

                <div class="domain-form__grid">
                  <label class="domain-field">
                    Phone number
                    <input name="contact_phone" value="{{ old('contact_phone') }}" placeholder="+31 20 123 4567">
                    @error('contact_phone')<span class="domain-field__error">{{ $message }}</span>@enderror
                  </label>

                  <label class="domain-field">
                    Closing date
                    <input type="date" name="closes_at" value="{{ old('closes_at') }}">
                    @error('closes_at')<span class="domain-field__error">{{ $message }}</span>@enderror
                  </label>
                </div>

                <div class="dash-actions">
                  <button class="dash-btn dash-btn--primary" type="submit">
                    <i class="ph ph-plus" aria-hidden="true"></i>
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
