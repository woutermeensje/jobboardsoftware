@extends('layouts.app')

@section('title', 'Billing en licentie | JobBoardSoftware')
@section('meta_description', 'Beheer het SaaS pakket en de billingstatus voor je jobboard software.')

@section('content')
<section class="dash-page">
  <div class="dash-shell">
    <header class="dash-topbar">
      <div>
        <p class="dash-eyebrow">Billing</p>
        <h1 class="dash-title">Licentie en pakket</h1>
        <p class="dash-subtitle">Kies het pakket waarmee je jobboard omgevingen wilt beheren. Stripe checkout wordt automatisch gebruikt zodra price IDs zijn ingesteld.</p>
      </div>
      <aside class="dash-user">
        <strong>{{ $user->billingPlan?->name ?? 'Geen pakket gekozen' }}</strong>
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
              {{ $user->billing_plan_id === $plan->id ? 'Huidig pakket' : 'Pakket kiezen' }}
            </button>
          </form>
        </article>
      @endforeach
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
      border-radius: 8px;
      background: #ffffff;
    }

    .dash-plan-card.is-selected {
      border-color: var(--color-primary-strong);
      box-shadow: var(--shadow-md);
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
