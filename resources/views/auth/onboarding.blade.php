@extends('layouts.app')

@php
  $brandTitle = 'JobBoardSoftware';
  $stepLabels = [
    'company' => 'Company contact info',
    'plan' => 'Plan',
    'payment' => 'Payment details',
  ];
  $stepKeys = array_keys($stepLabels);
  $currentIndex = array_search($step, $stepKeys, true);
  $selectedPlanId = (string) old('billing_plan_id', optional($selectedPlan)->id ?? optional($plans->first())->id);
  $trialDays = (int) config('billing.free_trial_days', 14);
  $selectedPlanIsFree = $selectedPlan && (int) $selectedPlan->monthly_price_cents === 0;
@endphp

@section('title', $title.' | '.$brandTitle)

@section('content')
<section class="auth-page auth-page--signup">
  <div class="auth-shell auth-shell--onboarding">
    <div class="signup-hero signup-hero--compact">
      <div>
        <p class="auth-eyebrow">Account verified</p>
        <h1>Finish your sign up</h1>
        <p>Complete the final setup steps for your job board account.</p>
      </div>
    </div>

    <section class="signup-section onboarding-card" aria-labelledby="onboarding-heading">
      @if(session('status'))
        <p class="auth-notice">{{ session('status') }}</p>
      @endif

      <ol class="signup-steps signup-steps--compact" aria-label="Sign up steps">
        @foreach($stepLabels as $stepKey => $label)
          @php
            $position = array_search($stepKey, $stepKeys, true);
            $stepState = $position === $currentIndex ? 'is-active' : ($position < $currentIndex ? 'is-complete' : '');
          @endphp
          <li class="{{ $stepState }}">
            <span>{{ $loop->iteration }}</span>
            <strong>{{ $label }}</strong>
          </li>
        @endforeach
      </ol>

      @if($step === 'company')
        <div class="signup-section__head">
          <span class="signup-section__icon" aria-hidden="true">
            <i class="ph ph-buildings"></i>
          </span>
          <div>
            <p class="signup-section__kicker">Step 1</p>
            <h2 id="onboarding-heading">Company contact info</h2>
          </div>
        </div>

        <form method="POST" action="{{ $action }}" class="auth-form">
          @csrf
          <input type="hidden" name="step" value="company">

          <div class="auth-grid auth-grid--two">
            <div class="auth-field">
              <label class="auth-label" for="first_name">First name</label>
              <input id="first_name" class="auth-input" name="first_name" type="text" value="{{ old('first_name', $user->first_name) }}" autocomplete="given-name" required autofocus>
              @error('first_name')
                <p class="auth-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="auth-field">
              <label class="auth-label" for="last_name">Last name</label>
              <input id="last_name" class="auth-input" name="last_name" type="text" value="{{ old('last_name', $user->last_name) }}" autocomplete="family-name" required>
              @error('last_name')
                <p class="auth-error">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div class="auth-field">
            <label class="auth-label" for="company_name">Company name</label>
            <input id="company_name" class="auth-input" name="company_name" type="text" value="{{ old('company_name', $user->company_name) }}" autocomplete="organization" required>
            @error('company_name')
              <p class="auth-error">{{ $message }}</p>
            @enderror
          </div>

          <div class="auth-grid auth-grid--two">
            <div class="auth-field">
              <label class="auth-label" for="email">Email address</label>
              <input id="email" class="auth-input" type="email" value="{{ $user->email }}" disabled>
            </div>

            <div class="auth-field">
              <label class="auth-label" for="phone_number">Phone number</label>
              <input id="phone_number" class="auth-input" name="phone_number" type="tel" value="{{ old('phone_number', $user->phone_number) }}" autocomplete="tel" required>
              @error('phone_number')
                <p class="auth-error">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div class="auth-actions">
            <button class="auth-button auth-button--primary" type="submit">
              Next
              <i class="ph ph-arrow-right"></i>
            </button>
          </div>
        </form>
      @elseif($step === 'plan')
        <div class="signup-section__head">
          <span class="signup-section__icon" aria-hidden="true">
            <i class="ph ph-stack"></i>
          </span>
          <div>
            <p class="signup-section__kicker">Step 2</p>
            <h2 id="onboarding-heading">Plan</h2>
          </div>
        </div>

        <form method="POST" action="{{ $action }}" class="auth-form">
          @csrf
          <input type="hidden" name="step" value="plan">

          <div class="signup-plan-grid">
            @forelse($plans as $plan)
              @php
                $planId = (string) $plan->id;
                $features = collect($plan->features ?? []);
                $monthlyPrice = $plan->formattedMonthlyPrice();
              @endphp

              <label class="signup-plan-card" for="billing_plan_{{ $plan->id }}">
                <input id="billing_plan_{{ $plan->id }}" type="radio" name="billing_plan_id" value="{{ $plan->id }}" @checked($selectedPlanId === $planId) required>
                <span class="signup-plan-card__body">
                  <span class="signup-plan-card__top">
                    <strong>{{ $plan->name }}</strong>
                    <span>{{ $monthlyPrice }}</span>
                  </span>
                  @if($plan->description)
                    <span class="signup-plan-card__description">{{ $plan->description }}</span>
                  @endif
                  @if($features->isNotEmpty())
                    <span class="signup-plan-card__features">
                      @foreach($features as $feature)
                        <span><i class="ph ph-check" aria-hidden="true"></i>{{ $feature }}</span>
                      @endforeach
                    </span>
                  @endif
                </span>
              </label>
            @empty
              <div class="signup-empty">
                <strong>Plans are being configured</strong>
                <span>Add billing plans in the admin area before opening sign up.</span>
              </div>
            @endforelse
          </div>

          @error('billing_plan_id')
            <p class="auth-error">{{ $message }}</p>
          @enderror

          <div class="auth-actions">
            <button class="auth-button auth-button--primary" type="submit" @disabled($plans->isEmpty())>
              Next
              <i class="ph ph-arrow-right"></i>
            </button>
          </div>
        </form>
      @else
        <div class="signup-section__head">
          <span class="signup-section__icon" aria-hidden="true">
            <i class="ph ph-credit-card"></i>
          </span>
          <div>
            <p class="signup-section__kicker">Step 3</p>
            <h2 id="onboarding-heading">Payment details</h2>
          </div>
        </div>

        <div class="signup-payment__summary">
          <div>
            <span>Company</span>
            <strong>{{ $user->company_name }}</strong>
          </div>
          <div>
            <span>Plan</span>
            <strong>{{ $selectedPlan?->name ?? 'Selected plan' }}</strong>
          </div>
          <div>
            <span>Trial</span>
            <strong>{{ $trialDays > 0 ? $trialDays.' days free' : 'No free trial' }}</strong>
          </div>
        </div>

        <form method="POST" action="{{ $action }}" class="auth-form">
          @csrf
          <input type="hidden" name="step" value="payment">

          <div class="auth-actions">
            <button class="auth-button auth-button--primary" type="submit">
              {{ $selectedPlanIsFree ? 'Start free trial' : 'Finish sign up' }}
              <i class="ph ph-arrow-right"></i>
            </button>
          </div>
        </form>

        <p class="signup-payment__note">
          {{ $selectedPlanIsFree ? 'No payment details are needed for the free trial.' : 'Payment details are entered securely on Stripe Checkout.' }}
        </p>
      @endif
    </section>
  </div>
</section>
@endsection

@push('styles')
  @include('auth.partials.styles')
@endpush
