<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TenantApplicationController extends Controller
{
    public function index(Request $request, Tenant $tenant): View
    {
        abort_unless((int) $tenant->owner_user_id === (int) $request->user()->id, 403);

        return view('dashboard.applications.index', [
            'tenant' => $tenant,
            'applications' => $tenant->applications()->with('job')->latest()->get(),
        ]);
    }

    public function update(Request $request, Tenant $tenant, JobApplication $application)
    {
        abort_unless((int) $tenant->owner_user_id === (int) $request->user()->id, 403);
        abort_unless($application->tenant_id === $tenant->id, 404);

        $validated = $request->validate([
            'status' => ['required', Rule::in([
                JobApplication::STATUS_NEW,
                JobApplication::STATUS_REVIEWED,
                JobApplication::STATUS_REJECTED,
                JobApplication::STATUS_HIRED,
            ])],
        ]);

        $application->update($validated);

        return back()->with('status', 'Sollicitatiestatus bijgewerkt.');
    }
}
