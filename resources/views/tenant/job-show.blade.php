@extends('layouts.tenant')

@section('title', $job->title.' | '.($tenant->name ?? 'Jobboard'))
@section('meta_description', $job->intro ?? 'View this job and apply directly.')

@section('content')
  <main class="tenant-shell tenant-shell--detail">
    @if(session('status'))
      <section class="tenant-alert">{{ session('status') }}</section>
    @endif

    <article class="tenant-panel tenant-detail">
      <a class="tenant-back" href="{{ route('tenant.jobs') }}">Back to jobs</a>
      <p class="tenant-eyebrow">{{ $job->department }}</p>
      <h1>{{ $job->title }}</h1>
      <p class="tenant-detail__meta">{{ $job->location }} - {{ $job->employment_type }} @if($job->salary_range)- {{ $job->salary_range }}@endif</p>
      @if($job->intro)
        <p class="tenant-detail__intro">{{ $job->intro }}</p>
      @endif
      <div class="tenant-detail__body">
        {!! nl2br(e($job->description)) !!}
      </div>
    </article>

    <aside class="tenant-panel tenant-apply">
      <p class="tenant-eyebrow">Apply</p>
      <h2>Apply for this job</h2>
      <form method="POST" action="{{ route('tenant.jobs.apply', $job) }}" enctype="multipart/form-data">
        @csrf
        <label>
          Name
          <input name="name" value="{{ old('name') }}" required>
          @error('name')<span>{{ $message }}</span>@enderror
        </label>
        <label>
          Email address
          <input name="email" type="email" value="{{ old('email') }}" required>
          @error('email')<span>{{ $message }}</span>@enderror
        </label>
        <label>
          Phone
          <input name="phone" value="{{ old('phone') }}">
        </label>
        <label>
          Motivation
          <textarea name="motivation" rows="5">{{ old('motivation') }}</textarea>
        </label>
        <label>
          Upload CV
          <input name="cv" type="file" accept=".pdf,.doc,.docx">
          @error('cv')<span>{{ $message }}</span>@enderror
        </label>
        <button class="tenant-btn tenant-btn--primary" type="submit">Submit application</button>
      </form>
    </aside>
  </main>
@endsection
