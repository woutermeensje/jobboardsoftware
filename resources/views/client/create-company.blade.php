@extends('layouts.app')

@section('title', 'Create company | Client dashboard')
@section('meta_description', 'Create a company profile with a logo for a job board environment.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('client.partials.navigation')
@endsection

@section('content')
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
            <div class="dash-panel__head">
              <div>
                <h2>Create company</h2>
                <p>Add a company profile and upload a logo for the selected environment.</p>
              </div>
              <a class="dash-link" href="{{ route('client.companies.index') }}">Back to companies</a>
            </div>

            @if($tenants->isEmpty())
              <div class="dash-empty-state">
                <h3>No environments yet</h3>
                <p>Create an environment before adding company profiles.</p>
                <a class="dash-link" href="{{ route('client.environments.create') }}">Create environment</a>
              </div>
            @else
              <form class="domain-form company-form" method="POST" action="{{ route('client.companies.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="domain-form__grid">
                  <label class="domain-field">
                    Environment
                    <select name="tenant_id" required>
                      <option value="">Select environment</option>
                      @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}" @selected(old('tenant_id') === $tenant->id)>{{ $tenant->name }}</option>
                      @endforeach
                    </select>
                    @error('tenant_id')<span class="domain-field__error">{{ $message }}</span>@enderror
                  </label>

                  <label class="domain-field">
                    Organization name
                    <input name="organization_name" value="{{ old('organization_name') }}" placeholder="Northwind Group" required>
                    @error('organization_name')<span class="domain-field__error">{{ $message }}</span>@enderror
                  </label>
                </div>

                <label class="domain-field">
                  Company name (for job posts)
                  <input name="name" value="{{ old('name') }}" placeholder="Northwind Hiring" required>
                  @error('name')<span class="domain-field__error">{{ $message }}</span>@enderror
                </label>

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
                    <input type="file" name="logo" accept=".jpg,.jpeg,.png,.webp,.svg,image/jpeg,image/png,image/webp,image/svg+xml">
                  </span>
                  @error('logo')<span class="domain-field__error">{{ $message }}</span>@enderror
                </label>

                <div class="domain-form__section-head">
                  <h3>Contact details</h3>
                </div>

                <div class="domain-form__grid">
                  <label class="domain-field">
                    First name
                    <input name="contact_first_name" value="{{ old('contact_first_name') }}" placeholder="Maya">
                    @error('contact_first_name')<span class="domain-field__error">{{ $message }}</span>@enderror
                  </label>

                  <label class="domain-field">
                    Last name
                    <input name="contact_last_name" value="{{ old('contact_last_name') }}" placeholder="Collins">
                    @error('contact_last_name')<span class="domain-field__error">{{ $message }}</span>@enderror
                  </label>
                </div>

                <div class="domain-form__grid">
                  <label class="domain-field">
                    Email address
                    <input type="email" name="contact_email" value="{{ old('contact_email') }}" placeholder="hiring@example.com">
                    @error('contact_email')<span class="domain-field__error">{{ $message }}</span>@enderror
                  </label>

                  <label class="domain-field">
                    Phone number
                    <input name="contact_phone" value="{{ old('contact_phone') }}" placeholder="+31 20 123 4567">
                    @error('contact_phone')<span class="domain-field__error">{{ $message }}</span>@enderror
                  </label>
                </div>

                <label class="domain-field">
                  Internal description
                  <textarea name="description" rows="4" placeholder="Short company description for internal use.">{{ old('description') }}</textarea>
                  @error('description')<span class="domain-field__error">{{ $message }}</span>@enderror
                </label>

                <div class="dash-actions">
                  <button class="dash-btn" type="submit">Create company</button>
                  <a class="dash-link" href="{{ route('client.companies.index') }}">Cancel</a>
                </div>
              </form>
            @endif
          </section>
        </main>

        <aside class="dash-form-layout__aside">
          <section class="dash-card dash-form-side">
            <h2>Company profile</h2>
            <p>Company profiles are stored for the selected environment and can be reused across jobs.</p>
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
