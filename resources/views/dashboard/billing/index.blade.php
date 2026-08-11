@extends('layouts.app')

@section('title', 'Billing and license | JobBoardSoftware')
@section('meta_description', 'Manage the SaaS package and billing status for your job board software.')

@section('content')
<section class="dash-page">
  <div class="dash-shell dash-app">
    @include('dashboard.partials.navigation')

    <div class="dash-content">
    <header class="dash-topbar">
      <div>
        <p class="dash-eyebrow">Billing</p>
        <h1 class="dash-title">License and package</h1>
        <p class="dash-subtitle">Choose the package you want to use to manage job board environments. Stripe checkout is used automatically once price IDs are configured.</p>
      </div>
      <aside class="dash-user">
        <strong>{{ $user->billingPlan?->name ?? 'No package selected' }}</strong>
        <span>Status: {{ ucfirst($user->billing_status ?? 'trial') }}</span>
        <span>{{ $user->email }}</span>
      </aside>
    </header>

    @if(session('status'))
      <section class="dash-card dash-card--success"><strong>{{ session('status') }}</strong></section>
    @endif

    <div class="dash-plan-grid">
      @foreach($plans as $plan)
        <article class="dash-plan-card {{ $user->billing_plan_id === $plan->id ? 'is-selected' : '' }}">
          <div>
            <h2>{{ $plan->name }}</h2>
            <p>{{ $plan->description }}</p>
            <strong>{{ $plan->formattedMonthlyPrice() }}</strong>
          </div>
          <ul>
            @foreach($plan->features ?? [] as $feature)
              <li><i class="ph ph-check"></i>{{ $feature }}</li>
            @endforeach
          </ul>
          <form method="POST" action="{{ route('billing.plan.select') }}">
            @csrf
            <input type="hidden" name="plan_key" value="{{ $plan->key }}">
            <button class="dash-btn {{ $user->billing_plan_id === $plan->id ? 'dash-btn--ghost' : 'dash-btn--primary' }}" type="submit">
              {{ $user->billing_plan_id === $plan->id ? 'Current package' : 'Choose package' }}
            </button>
          </form>
        </article>
      @endforeach
    </div>
    </div>
  </div>
</section>
@endsection

@push('styles')
  @include('dashboard.partials.styles')
  <style>
    .dash-card--success {
      border-color: var(--color-primary-muted);
      background: var(--color-primary-soft);
      color: var(--color-primary-strong);
    }

    .dash-plan-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 18px;
    }

    .dash-plan-card {
      display: grid;
      gap: 18px;
      align-content: space-between;
      min-height: 360px;
      padding: 24px;
      border: 1px solid var(--color-border);
      border-radius: var(--radius-default);
      background: #ffffff;
    }

    .dash-plan-card.is-selected {
      border-color: var(--color-primary-muted);
      background: var(--color-primary-soft);
    }

    .dash-plan-card h2,
    .dash-plan-card p {
      margin: 0;
    }

    .dash-plan-card h2 {
      font-size: 24px;
    }

    .dash-plan-card strong {
      display: block;
      margin-top: 14px;
      color: var(--color-primary-strong);
      font-family: var(--font-ui);
      font-size: 24px;
    }

    .dash-plan-card ul {
      display: grid;
      gap: 8px;
      margin: 0;
      padding: 0;
      list-style: none;
      color: var(--color-text-muted);
      font-size: 14px;
    }

    .dash-plan-card li {
      display: flex;
      gap: 8px;
    }

    .dash-plan-card i {
      color: var(--color-primary-strong);
      font-size: 18px;
    }

    @media (max-width: 980px) {
      .dash-plan-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
@endpush
