<?php

namespace App\Http\Controllers;

use App\Models\BillingPlan;
use App\Models\Tenant;
use App\Models\User;
use App\Support\AdminActionNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BillingController extends Controller
{
    public function checkout(Request $request)
    {
        $user = $request->user();
        $plan = $user->billingPlan;

        if (! $plan instanceof BillingPlan || ! $plan->is_active) {
            return redirect()
                ->route('client.billing')
                ->with('status', 'Choose a plan before continuing to payment.');
        }

        if ($user->subscribed('default')) {
            return redirect()
                ->route('client.billing')
                ->with('status', 'Your subscription is already active.');
        }

        if ($plan->monthly_price_cents === 0) {
            return $this->activateFreePlan($user);
        }

        $stripePriceId = $this->stripePriceIdFor($plan);

        if (! config('cashier.secret') || ! $stripePriceId) {
            return redirect()
                ->route('client.billing')
                ->with('status', 'Your account was created. Stripe checkout will start once the API keys and plan price IDs are configured.');
        }

        $user->forceFill([
            'onboarding_step' => 'billing',
        ])->save();

        $subscription = $user
            ->newSubscription('default', $stripePriceId)
            ->withMetadata([
                'user_id' => (string) $user->id,
                'billing_plan_id' => (string) $plan->id,
                'billing_plan_key' => $plan->key,
            ])
            ->allowPromotionCodes();

        if ($trialDays = $this->freeTrialDays()) {
            $subscription->trialDays($trialDays);
        }

        return $subscription->checkout([
            'success_url' => route('billing.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('client.billing'),
            'billing_address_collection' => 'auto',
            'tax_id_collection' => ['enabled' => true],
        ], [
            'name' => $user->company_name ?: $user->name,
            'email' => $user->email,
        ]);
    }

    public function success(Request $request): RedirectResponse
    {
        $user = $request->user();
        $trialDays = $this->freeTrialDays();
        $isTrial = $trialDays > 0;

        $user->forceFill([
            'billing_status' => $isTrial ? 'trial' : 'active',
            'onboarding_step' => $user->ownedTenants()->exists() ? 'jobs' : 'environment',
        ])->save();

        $user->ownedTenants()->update([
            'billing_status' => $isTrial ? 'trial' : 'active',
            'status' => $isTrial ? Tenant::STATUS_TRIAL : Tenant::STATUS_ACTIVE,
            'subscribed_at' => now(),
            'trial_ends_at' => $isTrial ? now()->addDays($trialDays) : null,
        ]);

        app(AdminActionNotifier::class)->notify($isTrial ? 'Trial started' : 'License activated', [
            'billing_status' => $user->billing_status,
            'onboarding_step' => $user->onboarding_step,
            'free_trial_days' => $trialDays,
            'tenant_count' => $user->ownedTenants()->count(),
        ], $user);

        return redirect()
            ->route('client.billing')
            ->with('status', $isTrial
                ? 'Your free trial is active for '.$trialDays.' days.'
                : 'Payment received. Your license is active.');
    }

    private function stripePriceIdFor(BillingPlan $plan): ?string
    {
        $priceId = $plan->stripe_price_id ?: config('services.stripe.prices.'.$plan->key);

        return is_string($priceId) && Str::startsWith($priceId, 'price_') ? $priceId : null;
    }

    private function activateFreePlan(User $user): RedirectResponse
    {
        $trialDays = $this->freeTrialDays();
        $isTrial = $trialDays > 0;

        $user->forceFill([
            'billing_status' => $isTrial ? 'trial' : 'active',
            'onboarding_step' => $user->ownedTenants()->exists() ? 'jobs' : 'environment',
        ])->save();

        $user->ownedTenants()->update([
            'billing_status' => $isTrial ? 'trial' : 'active',
            'status' => $isTrial ? Tenant::STATUS_TRIAL : Tenant::STATUS_ACTIVE,
            'trial_ends_at' => $isTrial ? now()->addDays($trialDays) : null,
        ]);

        app(AdminActionNotifier::class)->notify($isTrial ? 'Trial started' : 'Free plan activated', [
            'billing_status' => $user->billing_status,
            'onboarding_step' => $user->onboarding_step,
            'free_trial_days' => $trialDays,
            'tenant_count' => $user->ownedTenants()->count(),
        ], $user);

        return redirect()
            ->route('client.environments.index')
            ->with('status', $isTrial
                ? 'Your free trial is active for '.$trialDays.' days.'
                : 'Your free plan is active.');
    }

    private function freeTrialDays(): int
    {
        return (int) config('billing.free_trial_days', 14);
    }
}
