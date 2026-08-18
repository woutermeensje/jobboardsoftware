@extends('layouts.app')

@php
  $landingPage = $landingPage ?? null;
  $isEditing = $landingPage instanceof \App\Models\LandingPage;
  $formTitle = $isEditing ? 'Edit landing page' : 'Add landing page';
  $formAction = $isEditing ? route('client.marketing.landingpagina.update', $landingPage) : route('client.marketing.landingpagina.store');
  $submitLabel = $isEditing ? 'Save landing page' : 'Create landing page';
  $selectedTenantId = old('tenant_id', $landingPage?->tenant_id ?? $tenants->first()?->id);
@endphp

@section('title', $formTitle.' | Client dashboard')
@section('meta_description', 'Create a new landing page for your job board.')
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('client.partials.navigation')
@endsection

@section('content')
      @if($errors->any())
        <section class="dash-card dash-card--danger">
          <strong>Check the landing page details.</strong>
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
                <p>Publish a custom page on your job board.</p>
              </div>
              <a class="dash-link" href="{{ route('client.marketing.landingpagina') }}">Back to landing pages</a>
            </div>

            <form class="tenant-form" method="POST" action="{{ $formAction }}">
              @csrf
              @if($isEditing)
                @method('PATCH')
              @endif

              <div class="tenant-form__grid">
                <label>
                  Environment
                  <select name="tenant_id" required>
                    @foreach($tenants as $tenant)
                      <option value="{{ $tenant->id }}" @selected($selectedTenantId === $tenant->id)>
                        {{ $tenant->name }} ({{ $tenant->slug }})
                      </option>
                    @endforeach
                  </select>
                </label>

                <label>
                  Status
                  <select name="status" required>
                    <option value="{{ \App\Models\LandingPage::STATUS_DRAFT }}" @selected(old('status', $landingPage?->status ?? 'draft') === 'draft')>Draft</option>
                    <option value="{{ \App\Models\LandingPage::STATUS_PUBLISHED }}" @selected(old('status', $landingPage?->status) === 'published')>Published</option>
                  </select>
                </label>
              </div>

              <label>
                Title
                <input type="text" name="title" value="{{ old('title', $landingPage?->title) }}" placeholder="About us" required>
                @error('title')<span class="tenant-form__error">{{ $message }}</span>@enderror
              </label>

              <label>
                Meta description
                <input type="text" name="meta_description" value="{{ old('meta_description', $landingPage?->meta_description) }}" maxlength="500" placeholder="Shown in search results and social previews">
                @error('meta_description')<span class="tenant-form__error">{{ $message }}</span>@enderror
              </label>

              <div class="tenant-form__field tenant-rich-text" data-quill-field>
                <label for="landingpage-content">Content</label>
                <textarea id="landingpage-content" name="content" rows="10" required data-quill-source>{{ old('content', $landingPage?->content) }}</textarea>
                <div class="richtext-field tenant-rich-text__editor" data-quill-editor></div>
                @error('content')<span class="tenant-form__error">{{ $message }}</span>@enderror
              </div>

              <div class="dash-actions dash-actions--spaced">
                <button class="tenant-btn tenant-btn--primary" type="submit">
                  <i class="ph ph-{{ $isEditing ? 'floppy-disk' : 'plus' }}" aria-hidden="true"></i>
                  {{ $submitLabel }}
                </button>
              </div>
            </form>
          </section>
        </main>

        <aside class="dash-form-layout__aside">
          <section class="dash-card dash-form-side">
            <h2>Landing page tips</h2>
            <p>Landing pages are ideal for campaigns, partner pages or extra information that doesn't belong in the main menu.</p>
            <ul>
              <li>Save as draft to prepare a page before publishing.</li>
              <li>The page gets its own URL automatically, based on the title.</li>
              <li>You can find every page back on the Landing pages overview.</li>
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
