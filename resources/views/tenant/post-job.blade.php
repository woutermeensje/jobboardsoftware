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
        <div class="tenant-panel__head">
          <p class="tenant-eyebrow">Post a job</p>
          <h1>Submit a vacancy</h1>
          <p>Your job will be saved as a draft first. Publishing and payment can be completed in the next step later.</p>
        </div>

        <form method="POST" action="{{ route('tenant.post-job.store') }}" class="tenant-post-job-form">
          @csrf

          <div class="tenant-post-job-form__section">
            <h2>Job details</h2>

            <label>
              Job title
              <input name="title" value="{{ old('title') }}" placeholder="Senior Laravel Developer" required autofocus>
              @error('title')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
            </label>

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

            <label>
              Short intro
              <textarea name="intro" rows="3" maxlength="500" placeholder="Summarize the role in one or two sentences.">{{ old('intro') }}</textarea>
              @error('intro')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
            </label>

            <label>
              Job description
              <textarea name="description" rows="8" placeholder="Describe responsibilities, requirements and benefits." required>{{ old('description') }}</textarea>
              @error('description')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
            </label>
          </div>

          <div class="tenant-post-job-form__section">
            <h2>Contact details</h2>

            <label>
              Company name
              <input name="company_name" value="{{ old('company_name') }}" placeholder="Company Inc." required>
              @error('company_name')<span class="tenant-post-job-form__error">{{ $message }}</span>@enderror
            </label>

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
        <h2>Draft first</h2>
        <p>Submitted jobs are stored as drafts. The payment step will be connected later, before publishing.</p>
        <div class="tenant-post-job__summary">
          <span>Default job types</span>
          <strong>{{ count($jobTypes) }}</strong>
        </div>
      </aside>
    </section>
  </main>
@endsection
