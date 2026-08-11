<?php

namespace App\Http\Controllers;

use App\Models\BillingPlan;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function index(Request $request): View
    {
        return view('dashboard.billing.index', [
            'user' => $request->user()->load('billingPlan'),
            'plans' => BillingPlan::query()->where('is_active', true)->orderBy('monthly_price_cents')->get(),
        ]);
    }

    public function selectPlan(Request $request)
    {
        $validated = $request->validate([
            'plan_key' => ['required', 'exists:billing_plans,key'],
        ]);

        $plan = BillingPlan::where('key', $validated['plan_key'])->firstOrFail();
        $user = $request->user();

        if ($plan->stripe_price_id && config('cashier.key') && config('cashier.secret')) {
            return $user
                ->newSubscription('default', $plan->stripe_price_id)
                ->trialDays(14)
                ->checkout([
                    'success_url' => route('billing.success').'?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('billing.index'),
                ]);
        }

        $user->forceFill([
            'billing_plan_id' => $plan->id,
            'billing_status' => 'trial',
            'onboarding_step' => $user->ownedTenants()->exists() ? 'jobs' : 'environment',
        ])->save();

        $user->ownedTenants()->update([
            'plan' => $plan->key,
            'billing_status' => 'trial',
        ]);

        return redirect()
            ->route('onboarding.index')
            ->with('status', 'Your package has been saved. Stripe checkout will be used once price IDs and Stripe keys are configured.');
    }

    public function success(Request $request): RedirectResponse
    {
        $user = $request->user();

        $user->forceFill([
            'billing_status' => 'active',
            'onboarding_step' => $user->ownedTenants()->exists() ? 'jobs' : 'environment',
        ])->save();

        $user->ownedTenants()->update([
            'billing_status' => 'active',
            'status' => Tenant::STATUS_ACTIVE,
            'subscribed_at' => now(),
        ]);

        return redirect()
            ->route('onboarding.index')
            ->with('status', 'Payment received. Your license is active.');
    }
}
