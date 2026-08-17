@extends('layouts.app')

@section('title', $option['title'].' | Client dashboard')
@section('meta_description', $option['description'])
@section('layout', 'dashboard')
@section('dashboard_sidebar')
  @include('client.partials.navigation')
@endsection

@php
  $tenant = $tenants->first();
  $hasDefaultOptions = count($defaultOptions) > 0;
@endphp

@section('content')
      @if(session('status'))
        <section class="dash-card dash-card--success"><strong>{{ session('status') }}</strong></section>
      @endif

      @if($errors->any())
        <section class="dash-card dash-card--danger">
          <strong>{{ $option['singular'] }} could not be added.</strong>
          <ul class="dash-message-list">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </section>
      @endif

      <div class="dash-form-layout dash-options-layout">
        <main class="dash-form-layout__main">
          <section class="dash-panel dash-options-panel">
            <div class="dash-panel__head">
              <div>
                <h2>{{ $option['title'] }}</h2>
                <p>{{ $option['description'] }}</p>
              </div>
            </div>

            @if($tenants->isEmpty())
              <div class="dash-empty">
                <h3>No environments yet</h3>
                <p>Create an environment before adding {{ strtolower($option['title']) }}.</p>
                <div class="dash-actions">
                  <a class="dash-link" href="{{ route('client.environments.create') }}">Create environment</a>
                </div>
              </div>
            @else
              <form class="domain-form" method="POST" action="{{ route($option['store_route_name']) }}">
                @csrf
                <input type="hidden" name="tenant_id" value="{{ $tenant?->id }}">

                <div class="domain-form__grid domain-form__grid--single">
                  <label class="domain-field">
                    <span>{{ $option['field_label'] }}</span>
                    <input
                      type="text"
                      name="name"
                      value="{{ old('name') }}"
                      placeholder="{{ $option['placeholder'] }}"
                      autocomplete="off"
                      required
                    >
                  </label>
                </div>

                <div class="dash-actions dash-actions--spaced">
                  <button class="dash-btn dash-btn--primary" type="submit">
                    <i class="ph ph-plus" aria-hidden="true"></i>
                    {{ $option['button_label'] }}
                  </button>
                </div>
              </form>

              <div class="dash-options-list">
                <div class="dash-options-list__head">
                  <div>
                    <h3>{{ $option['available_title'] }}</h3>
                    <p>{{ $option['available_description'] }}</p>
                  </div>
                </div>

                <table class="dash-table">
                  <thead>
                    <tr>
                      @if($hasDefaultOptions)
                        <th>{{ $option['default_title'] ?? 'Default options' }}</th>
                      @endif
                      <th>Custom options</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($tenants as $tenant)
                      @php
                        $customOptions = collect($optionsByTenant[$tenant->id] ?? []);
                      @endphp
                      <tr>
                        @if($hasDefaultOptions)
                          <td>
                            <div class="dash-actions">
                              @foreach($defaultOptions as $defaultOption)
                                <span class="dash-status">{{ $defaultOption }}</span>
                              @endforeach
                            </div>
                          </td>
                        @endif
                        <td>
                          @if($customOptions->isEmpty())
                            <span class="dash-status dash-status--muted">{{ $option['empty_label'] }}</span>
                          @else
                            <div class="dash-actions">
                              @foreach($customOptions as $customOption)
                                <span class="dash-status dash-status--accent">{{ $customOption }}</span>
                              @endforeach
                            </div>
                          @endif
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif
          </section>
        </main>

        <aside class="dash-form-layout__aside">
          <section class="dash-card dash-form-side dash-options-aside">
            <h2>{{ $option['aside_title'] }}</h2>
            <p>{{ $option['aside_description'] }}</p>
          </section>
        </aside>
      </div>
@endsection
