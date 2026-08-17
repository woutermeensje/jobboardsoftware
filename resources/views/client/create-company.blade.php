@extends('layouts.app')

@php
  $company = $company ?? null;
  $isEditing = $company instanceof \App\Models\TenantCompany;
  $formTitle = $isEditing ? 'Edit company' : 'Create company';
  $formIntro = $isEditing ? 'Update this company profile and save the changes.' : 'Add a company profile and upload a logo for your account.';
  $formAction = $isEditing ? route('client.companies.update', $company) : route('client.companies.store');
  $submitLabel = $isEditing ? 'Save company' : 'Create company';
  $selectedTenantId = old('tenant_id', $company?->tenant_id ?? $tenants->first()?->id);
@endphp

@section('title', $formTitle.' | Client dashboard')
@section('meta_description', $isEditing ? 'Edit a company profile for a job board environment.' : 'Create a company profile with a logo for a job board environment.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('client.partials.navigation')
@endsection

@section('content')
      @if(session('status'))
        <section class="dash-card dash-card--success">
          {{ session('status') }}
        </section>
      @endif

      @if($errors->any())
        <section class="dash-card dash-card--danger">
          <strong>Check the company details.</strong>
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
                  <h2>{{ $formTitle }}</h2>
                  <p>Add a company profile and upload a logo for your account.</p>
                </div>
                <a class="dash-link" href="{{ route('client.companies.index') }}">Back to companies</a>
              </div>

              <div class="dash-empty-state">
                <h3>No environments yet</h3>
                <p>Create an environment before adding company profiles.</p>
                <a class="dash-link" href="{{ route('client.environments.create') }}">Create environment</a>
              </div>
            @else
              <form class="tenant-form tenant-company-form" method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
                @csrf
                @if($isEditing)
                  @method('PATCH')
                @endif
                <input type="hidden" name="tenant_id" value="{{ $selectedTenantId }}">

                <div class="tenant-form-header tenant-company-form__header">
                  <div>
                    <h2 class="tenant-form-title">{{ $formTitle }}</h2>
                    <p class="tenant-form-intro">{{ $formIntro }}</p>
                  </div>
                  <a class="dash-link" href="{{ route('client.companies.index') }}">Back to companies</a>
                </div>

                <section class="tenant-form-section-block tenant-form__section" aria-labelledby="company-details-title">
                  <div class="tenant-form-section-head">
                    <h2 id="company-details-title" class="tenant-form-section-title">Company details</h2>
                  </div>

                  @error('tenant_id')<span class="tenant-form__error">{{ $message }}</span>@enderror

                  <label>
                    Organization name
                    <input name="organization_name" value="{{ old('organization_name', $company?->organization_name) }}" required>
                    @error('organization_name')<span class="tenant-form__error">{{ $message }}</span>@enderror
                  </label>

                  <label>
                    Company name (for job posts)
                    <input name="name" value="{{ old('name', $company?->name) }}" required>
                    @error('name')<span class="tenant-form__error">{{ $message }}</span>@enderror
                  </label>

                  @if($companyUrlColumnReady ?? true)
                    <label>
                      Company website URL
                      <input type="url" name="company_url" value="{{ old('company_url', $company?->company_url) }}">
                      <span class="input-description">Add a homepage, about page, or another relevant company page for this company.</span>
                      @error('company_url')<span class="tenant-form__error">{{ $message }}</span>@enderror
                    </label>
                  @endif
                </section>

                <section class="tenant-form-section-block tenant-form__section" aria-labelledby="company-logo-title">
                  <div class="tenant-form-section-head">
                    <h2 id="company-logo-title" class="tenant-form-section-title">Company logo</h2>
                  </div>

                  <label class="tenant-form__field">
                    Logo file
                    <span class="tenant-logo-upload tenant-logo-upload--with-filename" data-file-picker>
                      <span class="tenant-file-picker__button">Choose file</span>
                      <span class="tenant-file-picker__filename" data-file-name data-empty-label="No file selected">No file selected</span>
                      <input type="file" name="logo" accept=".jpg,.jpeg,.png,.webp,.svg,image/jpeg,image/png,image/webp,image/svg+xml">
                    </span>
                    <span class="input-description">Upload a PNG, JPG, WebP or SVG logo. Maximum file size: 2 MB.</span>
                    @if($isEditing && $company?->logo_path)
                      <span class="input-description">Current logo is saved. Choose a new file only if you want to replace it.</span>
                    @endif
                    @error('logo')<span class="tenant-form__error">{{ $message }}</span>@enderror
                  </label>
                </section>

                <section class="tenant-form-section-block tenant-form__section" aria-labelledby="company-contact-title">
                  <div class="tenant-form-section-head">
                    <h2 id="company-contact-title" class="tenant-form-section-title">Contact details</h2>
                  </div>

                  <div class="tenant-form__grid">
                    <label>
                      First name
                      <input name="contact_first_name" value="{{ old('contact_first_name', $company?->contact_first_name) }}">
                      @error('contact_first_name')<span class="tenant-form__error">{{ $message }}</span>@enderror
                    </label>

                    <label>
                      Last name
                      <input name="contact_last_name" value="{{ old('contact_last_name', $company?->contact_last_name) }}">
                      @error('contact_last_name')<span class="tenant-form__error">{{ $message }}</span>@enderror
                    </label>
                  </div>

                  <div class="tenant-form__grid">
                    <label>
                      Email address
                      <input type="email" name="contact_email" value="{{ old('contact_email', $company?->contact_email) }}">
                      @error('contact_email')<span class="tenant-form__error">{{ $message }}</span>@enderror
                    </label>

                    <label>
                      Phone number
                      <input name="contact_phone" value="{{ old('contact_phone', $company?->contact_phone) }}">
                      @error('contact_phone')<span class="tenant-form__error">{{ $message }}</span>@enderror
                    </label>
                  </div>
                </section>

                <section class="tenant-form-section-block tenant-form__section" aria-labelledby="company-description-title">
                  <div class="tenant-form-section-head">
                    <h2 id="company-description-title" class="tenant-form-section-title">Company description</h2>
                  </div>

                  <div class="tenant-form__field tenant-rich-text" data-quill-field>
                    <label for="company-description">Description</label>
                    <textarea
                      id="company-description"
                      name="description"
                      rows="6"
                      data-quill-source
                    >{{ old('description', $company?->description) }}</textarea>
                    <div
                      class="richtext-field tenant-rich-text__editor"
                      data-quill-editor
                    ></div>
                    @error('description')<span class="tenant-form__error">{{ $message }}</span>@enderror
                  </div>
                </section>

                <div class="tenant-company-form__actions">
                  <button class="tenant-btn tenant-btn--primary tenant-post-job-form__submit" type="submit">{{ $submitLabel }}</button>
                  <a class="dash-link" href="{{ route('client.companies.index') }}">Cancel</a>
                </div>
              </form>
            @endif
          </section>
        </main>

        <aside class="dash-form-layout__aside">
          <section class="dash-card dash-form-side">
            <h2>Company description</h2>
            <p>Company descriptions are stored in your account and can be reused across jobs.</p>
            <ul>
              <li>The organization name is used for account context.</li>
              <li>The company name is shown on job posts.</li>
              <li>Logos support PNG, JPG, WebP and SVG files.</li>
              <li>The maximum logo file size is 2 MB.</li>
              <li>Contact details stay linked to this company profile.</li>
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
