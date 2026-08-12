<?php

namespace App\Http\Controllers;

use App\Models\BillingPlan;
use App\Models\Tenant;
use App\Support\AdminActionNotifier;
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
            app(AdminActionNotifier::class)->notify('Stripe checkout gestart', [
                'pakket' => $plan->name,
                'pakket_key' => $plan->key,
                'stripe_price_id' => $plan->stripe_price_id,
            ], $user);

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

        app(AdminActionNotifier::class)->notify('Pakket gekozen', [
            'pakket' => $plan->name,
            'pakket_key' => $plan->key,
            'bedrag_per_maand_cent' => $plan->monthly_price_cents,
            'billing_status' => $user->billing_status,
            'onboarding_step' => $user->onboarding_step,
        ], $user);

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

        app(AdminActionNotifier::class)->notify('Licentie geactiveerd', [
            'billing_status' => $user->billing_status,
            'onboarding_step' => $user->onboarding_step,
            'tenant_count' => $user->ownedTenants()->count(),
        ], $user);

        return redirect()
            ->route('onboarding.index')
            ->with('status', 'Payment received. Your license is active.');
    }
}
