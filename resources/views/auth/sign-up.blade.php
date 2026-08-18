@extends($layout ?? 'layouts.app')

@php
  $brandTitle = $brandName ?? 'JobBoardSoftware';
  $pageTitle = $title ?? 'Sign up';
  $backUrl = $backUrl ?? route('welcome');
  $backLabel = $backLabel ?? 'Back to website';
  $plans = $plans ?? collect();
  $selectedPlanId = (string) old('billing_plan_id', optional($plans->first())->id);
@endphp

@section('title', $pageTitle.' | '.$brandTitle)

@section('content')
<section class="auth-page auth-page--signup">
  <div class="auth-shell auth-shell--signup">
    <div class="signup-hero">
      <div>
        @if(! empty($eyebrow))
          <p class="auth-eyebrow">{{ $eyebrow }}</p>
        @endif
        <h1>{{ $title }}</h1>
        @if(! empty($subtitle))
          <p>{{ $subtitle }}</p>
        @endif
      </div>

      <div class="signup-hero__actions">
        <a class="auth-link" href="{{ $loginUrl }}">I already have an account</a>
        <a class="auth-link" href="{{ $backUrl }}">{{ $backLabel }}</a>
      </div>
    </div>

    <ol class="signup-steps" aria-label="Sign up steps">
      <li>
        <span>1</span>
        <strong>Company contact info</strong>
      </li>
      <li>
        <span>2</span>
        <strong>Plan</strong>
      </li>
      <li>
        <span>3</span>
        <strong>Payment details</strong>
      </li>
    </ol>

    <form method="POST" action="{{ $action }}" class="signup-flow">
      @csrf

      <div class="signup-flow__main">
        <section class="signup-section" aria-labelledby="company-contact-heading">
          <div class="signup-section__head">
            <span class="signup-section__icon" aria-hidden="true">
              <i class="ph ph-buildings"></i>
            </span>
            <div>
              <p class="signup-section__kicker">Step 1</p>
              <h2 id="company-contact-heading">Company contact info</h2>
            </div>
          </div>

          <div class="auth-grid auth-grid--two">
            <div class="auth-field">
              <label class="auth-label" for="first_name">First name</label>
              <input id="first_name" class="auth-input" name="first_name" type="text" value="{{ old('first_name') }}" autocomplete="given-name" required autofocus>
              @error('first_name')
                <p class="auth-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="auth-field">
              <label class="auth-label" for="last_name">Last name</label>
              <input id="last_name" class="auth-input" name="last_name" type="text" value="{{ old('last_name') }}" autocomplete="family-name" required>
              @error('last_name')
                <p class="auth-error">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div class="auth-field">
            <label class="auth-label" for="company_name">Company name</label>
            <input id="company_name" class="auth-input" name="company_name" type="text" value="{{ old('company_name') }}" autocomplete="organization" required>
            @error('company_name')
              <p class="auth-error">{{ $message }}</p>
            @enderror
          </div>

          <div class="auth-grid auth-grid--two">
            <div class="auth-field">
              <label class="auth-label" for="email">Work email</label>
              <input id="email" class="auth-input" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
              @error('email')
                <p class="auth-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="auth-field">
              <label class="auth-label" for="phone_number">Phone number</label>
              <input id="phone_number" class="auth-input" name="phone_number" type="tel" value="{{ old('phone_number') }}" autocomplete="tel" required>
              @error('phone_number')
                <p class="auth-error">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div class="auth-field">
            <label class="auth-label" for="heard_about_us">Where did you hear about us?</label>
            <input id="heard_about_us" class="auth-input" name="heard_about_us" type="text" value="{{ old('heard_about_us') }}" required>
            @error('heard_about_us')
              <p class="auth-error">{{ $message }}</p>
            @enderror
          </div>

          <div class="auth-grid auth-grid--two">
            <div class="auth-field">
              <label class="auth-label" for="password">Password</label>
              <input id="password" class="auth-input" name="password" type="password" autocomplete="new-password" required>
              @error('password')
                <p class="auth-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="auth-field">
              <label class="auth-label" for="password_confirmation">Confirm password</label>
              <input id="password_confirmation" class="auth-input" name="password_confirmation" type="password" autocomplete="new-password" required>
            </div>
          </div>
        </section>

        <section class="signup-plan-section" aria-labelledby="plan-heading">
          <div class="signup-section__head">
            <span class="signup-section__icon" aria-hidden="true">
              <i class="ph ph-stack"></i>
            </span>
            <div>
              <p class="signup-section__kicker">Step 2</p>
              <h2 id="plan-heading">Plan</h2>
            </div>
          </div>

          <div class="signup-plan-grid">
            @forelse($plans as $plan)
              @php
                $planId = (string) $plan->id;
                $features = collect($plan->features ?? [])->take(4);
                $monthlyPrice = $plan->monthly_price_cents === 0 ? 'Custom' : $plan->formattedMonthlyPrice();
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
                <span>Add billing plans in the admin area before opening sign-up.</span>
              </div>
            @endforelse
          </div>

          @error('billing_plan_id')
            <p class="auth-error">{{ $message }}</p>
          @enderror
        </section>
      </div>

      <aside class="signup-payment" aria-labelledby="payment-heading">
        <div class="signup-section__head">
          <span class="signup-section__icon" aria-hidden="true">
            <i class="ph ph-credit-card"></i>
          </span>
          <div>
            <p class="signup-section__kicker">Step 3</p>
            <h2 id="payment-heading">Payment details</h2>
          </div>
        </div>

        <div class="signup-payment__summary">
          <div>
            <span>Checkout</span>
            <strong>Secure Stripe payment</strong>
          </div>
          <div>
            <span>Subscription</span>
            <strong>Monthly billing</strong>
          </div>
          <div>
            <span>Next</span>
            <strong>Job board setup</strong>
          </div>
        </div>

        <button class="auth-button auth-button--primary" type="submit" @disabled($plans->isEmpty())>
          <i class="ph ph-lock-key"></i>
          Create account and continue
        </button>

        <p class="signup-payment__note">Payment details are entered on Stripe Checkout after this step.</p>
      </aside>
    </form>
  </div>
</section>
@endsection

@push('styles')
  @include('auth.partials.styles')
@endpush
