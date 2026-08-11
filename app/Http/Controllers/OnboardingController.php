<?php

namespace App\Http\Controllers;

use App\Models\BillingPlan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load(['billingPlan', 'ownedTenants.domains', 'ownedTenants.jobs']);
        $firstTenant = $user->ownedTenants->first();

        return view('dashboard.onboarding.index', [
            'user' => $user,
            'plans' => BillingPlan::where('is_active', true)->orderBy('monthly_price_cents')->get(),
            'tenant' => $firstTenant,
            'steps' => [
                'plan' => (bool) $user->billing_plan_id,
                'environment' => $user->ownedTenants->isNotEmpty(),
                'domain' => (bool) $firstTenant?->domains->count(),
                'jobs' => (bool) $firstTenant?->jobs->count(),
                'billing' => in_array($user->billing_status, ['trial', 'active'], true),
            ],
        ]);
    }
}
