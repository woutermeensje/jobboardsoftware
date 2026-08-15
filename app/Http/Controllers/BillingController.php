<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Support\AdminActionNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BillingController extends Controller
{
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
            ->route('client.billing')
            ->with('status', 'Payment received. Your license is active.');
    }
}
