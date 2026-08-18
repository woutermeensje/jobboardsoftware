@extends('layouts.app')

@section('title', 'Landing pages | Client dashboard')
@section('meta_description', 'Manage marketing landing pages for your job board environments.')
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

      <section class="dash-panel">
        <div class="dash-panel__head">
          <div>
            <h2>Landing pages</h2>
            <p>Manage the pages on your job board that aren't part of the main menu.</p>
          </div>
          @if($tenants->isNotEmpty())
            <a class="dash-link" href="{{ route('client.marketing.landingpagina.create') }}">Add landingpage</a>
          @endif
        </div>
      </section>

      @if($tenants->isEmpty())
        <section class="dash-panel">
          <div class="dash-empty">
            <h3>No environments yet</h3>
            <p>Create an environment before managing landing pages.</p>
            <div class="dash-actions">
              <a class="dash-link" href="{{ route('client.environments.create') }}">Create environment</a>
            </div>
          </div>
        </section>
      @else
        <div class="dash-form-layout">
          <main class="dash-form-layout__main">
            <section class="dash-panel dash-panel--list">
              <div class="dash-panel__head">
                <div>
                  <h2>Pages overview</h2>
                  <p>All frontend pages, excluding the ones already in your main menu.</p>
                </div>
              </div>

              <div class="dash-panel__body">
                @if($pages->isEmpty())
                  <div class="dash-empty">
                    <h3>No pages found</h3>
                    <p>Add a landing page to get started.</p>
                  </div>
                @else
                  <table class="dash-table">
                    <thead>
                      <tr>
                        <th>Page</th>
                        <th>Environment</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($pages as $page)
                        <tr>
                          <td>
                            <span class="dash-cell-title">{{ $page['title'] }}</span>
                            @if($page['url'])
                              <span class="dash-cell-meta">{{ $page['url'] }}</span>
                            @endif
                          </td>
                          <td>{{ $page['tenant_name'] }}</td>
                          <td>{{ $page['type'] === 'custom' ? 'Landing page' : 'System page' }}</td>
                          <td>
                            @if($page['status'])
                              <span class="dash-status">{{ ucfirst($page['status']) }}</span>
                            @else
                              <span class="dash-cell-meta">&mdash;</span>
                            @endif
                          </td>
                          <td>
                            <div class="dash-actions">
                              @if($page['url'])
                                <a class="dash-link" href="{{ $page['url'] }}" target="_blank" rel="noopener">View</a>
                              @endif

                              @if($page['landing_page'])
                                <a class="dash-link" href="{{ route('client.marketing.landingpagina.edit', $page['landing_page']) }}">Edit</a>

                                <form method="POST" action="{{ route('client.marketing.landingpagina.destroy', $page['landing_page']) }}" onsubmit="return confirm('Delete {{ addslashes($page['title']) }}? This cannot be undone.');">
                                  @csrf
                                  @method('DELETE')
                                  <button class="dash-btn dash-btn--ghost btn-sm dash-btn--danger" type="submit">Delete</button>
                                </form>
                              @endif
                            </div>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                @endif
              </div>
            </section>
          </main>

          <aside class="dash-form-layout__aside">
            <section class="dash-card dash-form-side">
              <h2>About landing pages</h2>
              <p>Landing pages let you publish custom marketing content on your job board without adding it to the main menu &mdash; ideal for campaigns or one-off pages.</p>
              <ul>
                <li>New pages start as a draft until you publish them.</li>
                <li>Pages already in your main menu (Jobs, Companies, Pricing, Job alerts, Newsletter, Contact) aren't listed here.</li>
                <li>Each published page gets its own URL on your job board.</li>
              </ul>
            </section>
          </aside>
        </div>
      @endif
@endsection
