@extends('layouts.app')

@php
  $package = $package ?? null;
  $isEditing = $package instanceof \App\Models\TenantPackage;
  $formTitle = $isEditing ? 'Edit package' : 'Add packages';
  $formIntro = $isEditing ? 'Update this package for the pricing page and post-a-job form.' : 'Create a package employers can choose from when they submit a job.';
  $formAction = $isEditing ? route('client.packages.update', $package) : route('client.packages.store');
  $submitLabel = $isEditing ? 'Save package' : 'Add package';
  $selectedTenantId = (string) old('tenant_id', $package?->tenant_id ?? $tenants->first()?->id);
@endphp

@section('title', $formTitle.' | Client dashboard')
@section('meta_description', $isEditing ? 'Edit a job posting package for a job board environment.' : 'Add job posting packages for job board environments.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('client.partials.navigation')
@endsection

@section('content')
      @if($errors->any())
        <section class="dash-card dash-card--danger">
          <strong>Package could not be {{ $isEditing ? 'saved' : 'added' }}.</strong>
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
                <h2>{{ $formTitle }}</h2>
                <p>{{ $formIntro }}</p>
              </div>
              <a class="dash-link" href="{{ route('client.packages.index') }}">Back to packages</a>
            </div>

            @if(! $packageTableReady)
              <div class="dash-empty">
                <h3>Package setup is not ready yet</h3>
                <p>Run the latest database migrations before adding packages.</p>
              </div>
            @elseif($tenants->isEmpty())
              <div class="dash-empty">
                <h3>No environments yet</h3>
                <p>Create an environment before adding packages.</p>
                <div class="dash-actions">
                  <a class="dash-link" href="{{ route('client.environments.create') }}">Create environment</a>
                </div>
              </div>
            @else
              <form class="domain-form" method="POST" action="{{ $formAction }}">
                @csrf
                @if($isEditing)
                  @method('PATCH')
                @endif
                <input type="hidden" name="tenant_id" value="{{ $selectedTenantId }}">

                <div class="domain-form__grid domain-form__grid--single">
                  <label class="domain-field">
                    <span>Package name</span>
                    <input
                      type="text"
                      name="name"
                      value="{{ old('name', $package?->name) }}"
                      placeholder="For example: Featured job"
                      autocomplete="off"
                      required
                    >
                  </label>
                </div>

                <div class="domain-form__grid">
                  <label class="domain-field">
                    <span>Price</span>
                    <input
                      type="number"
                      name="price"
                      value="{{ old('price', $package?->price) }}"
                      min="0"
                      step="0.01"
                      placeholder="99.00"
                      required
                    >
                  </label>

                  <label class="domain-field">
                    <span>Currency</span>
                    <input
                      type="text"
                      name="currency"
                      value="{{ old('currency', $package?->currency ?? 'EUR') }}"
                      maxlength="3"
                      placeholder="EUR"
                      autocomplete="off"
                      required
                    >
                  </label>
                </div>

                <div class="domain-form__grid domain-form__grid--single">
                  <label class="domain-field">
                    <span>Days online</span>
                    <input
                      type="number"
                      name="online_days"
                      value="{{ old('online_days', $package?->online_days) }}"
                      min="1"
                      step="1"
                      placeholder="30"
                      required
                    >
                  </label>
                </div>

                @if($packageDescriptionColumnReady ?? false)
                  <div class="domain-form__grid domain-form__grid--single">
                    <div class="domain-field domain-rich-text" data-quill-field>
                      <label for="package-description">Package description</label>
                      <textarea
                        id="package-description"
                        name="description"
                        rows="8"
                        data-quill-source
                      >{{ old('description', $package?->description) }}</textarea>
                      <div
                        class="richtext-field"
                        data-quill-editor
                      ></div>
                      @error('description')<span class="domain-field__error">{{ $message }}</span>@enderror
                    </div>
                  </div>
                @endif

                <div class="dash-actions dash-actions--spaced">
                  <button class="dash-btn dash-btn--primary" type="submit">
                    <i class="ph {{ $isEditing ? 'ph-check' : 'ph-plus' }}" aria-hidden="true"></i>
                    {{ $submitLabel }}
                  </button>
                  <a class="dash-link" href="{{ route('client.packages.index') }}">Cancel</a>
                </div>
              </form>
            @endif
          </section>
        </main>

        <aside class="dash-form-layout__aside">
          <section class="dash-card dash-form-side">
            <h2>Post-a-job packages</h2>
            <p>Packages added here appear in the package selector on the tenant post-a-job form and on the pricing page.</p>
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
