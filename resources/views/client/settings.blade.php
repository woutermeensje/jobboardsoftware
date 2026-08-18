@extends('layouts.app')

@section('title', 'Settings | Client dashboard')
@section('meta_description', 'Manage appearance settings for your job board environments.')
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
          <strong>Settings could not be saved.</strong>
          <ul class="dash-message-list">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </section>
      @endif

      <section class="dash-panel">
        <div class="dash-panel__head">
          <div>
            <h2>Settings</h2>
            <p>Manage the primary color for each job board environment.</p>
          </div>
        </div>

        @if($tenants->isEmpty())
          <div class="dash-empty-state">
            <h3>No environments yet</h3>
            <p>Create an environment before changing job board settings.</p>
            <a class="dash-link" href="{{ route('client.environments.create') }}">Create environment</a>
          </div>
        @else
          <div class="tenant-settings-list">
            @foreach($tenants as $tenant)
              @php
                $settings = $tenant->settings ?? [];
                $primaryColor = $settings['primary_color'] ?? $settings['accent_color'] ?? '#2f5f80';
                $primaryColor = is_string($primaryColor) && preg_match('/^#[0-9a-fA-F]{6}$/', $primaryColor) ? $primaryColor : '#2f5f80';
                $homepageTitle = $settings['homepage_title'] ?? '';
                $homepageSubtitle = $settings['homepage_subtitle'] ?? '';
                $logoPath = $settings['logo_path'] ?? null;
                $logoUrl = $settings['logo_url'] ?? null;
                $logoUrl = ! $logoUrl && $logoPath ? \App\Support\PublicUploadStorage::url((string) $logoPath) : $logoUrl;
                $useOldValues = old('tenant_id') === $tenant->id;
              @endphp

              <form class="tenant-settings-card" method="POST" action="{{ route('client.settings.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">

                <div class="tenant-settings-card__copy">
                  <h3>{{ $tenant->name }}</h3>
                  <p>{{ $tenant->primaryDomain?->domain ?? $tenant->id }}</p>
                </div>

                <div class="tenant-settings-card__fields">
                  <label class="tenant-color-field">
                    <span>Primary color</span>
                    <span class="tenant-color-field__control">
                      <input type="color" value="{{ $primaryColor }}" data-color-picker>
                      <input
                        name="primary_color"
                        value="{{ $useOldValues ? old('primary_color', $primaryColor) : $primaryColor }}"
                        pattern="#[0-9a-fA-F]{6}"
                        maxlength="7"
                        required
                        data-color-code
                      >
                    </span>
                  </label>

                  <label class="tenant-settings-field tenant-settings-field--logo">
                    <span>Bedrijfslogo</span>
                    <span class="upload-box upload-box--compact" data-file-picker>
                      <input type="file" name="logo" accept=".jpg,.jpeg,.png,.webp,.svg,image/jpeg,image/png,image/webp,image/svg+xml">
                      <span class="upload-box__icon" aria-hidden="true"><i class="ph ph-image"></i></span>
                      <span class="upload-box__body">
                        <span class="upload-box__button">Bestand kiezen</span>
                        <span class="upload-box__filename" data-file-name data-empty-label="Geen bestand gekozen">Geen bestand gekozen</span>
                      </span>
                    </span>
                    <span class="input-description">Aanbevolen formaat: 440 x 120 px. Gebruik PNG, SVG, JPG of WebP; max. 2 MB.</span>
                    @if($logoUrl)
                      <span class="tenant-settings-logo-preview">
                        <img src="{{ $logoUrl }}" alt="{{ $tenant->name }} logo">
                      </span>
                    @endif
                  </label>

                  <label class="tenant-settings-field">
                    <span>Homepage Titel</span>
                    <input
                      name="homepage_title"
                      value="{{ $useOldValues ? old('homepage_title', $homepageTitle) : $homepageTitle }}"
                      maxlength="255"
                      placeholder="Search all jobs"
                    >
                  </label>

                  <label class="tenant-settings-field">
                    <span>Homepage subtitel</span>
                    <input
                      name="homepage_subtitle"
                      value="{{ $useOldValues ? old('homepage_subtitle', $homepageSubtitle) : $homepageSubtitle }}"
                      maxlength="500"
                      placeholder="Jobs, internships and roles at {{ $tenant->name }}."
                    >
                  </label>
                </div>

                <button class="tenant-btn tenant-btn--primary" type="submit">Save</button>
              </form>
            @endforeach
          </div>
        @endif
      </section>
@endsection

@push('scripts')
  <script>
    (() => {
      document.querySelectorAll('.tenant-color-field__control').forEach((control) => {
        const picker = control.querySelector('[data-color-picker]');
        const code = control.querySelector('[data-color-code]');

        if (!picker || !code) {
          return;
        }

        picker.addEventListener('input', () => {
          code.value = picker.value.toUpperCase();
        });

        code.addEventListener('input', () => {
          const value = code.value.trim();

          if (/^#[0-9a-fA-F]{6}$/.test(value)) {
            picker.value = value;
          }
        });
      });
    })();
  </script>
@endpush
