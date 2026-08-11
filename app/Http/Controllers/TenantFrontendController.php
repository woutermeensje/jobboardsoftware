<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\TenantJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TenantFrontendController extends Controller
{
    public function home(Request $request): View
    {
        $tenant = tenant();
        $jobs = $this->jobsQuery($request)->get();

        return view('tenant.jobboard', [
            'tenant' => $tenant,
            'jobs' => $jobs,
            'departments' => TenantJob::where('tenant_id', $tenant->id)->whereNotNull('department')->distinct()->pluck('department'),
            'focus' => null,
        ]);
    }

    public function jobs(Request $request): View
    {
        return $this->home($request);
    }

    public function showJob(TenantJob $job): View
    {
        abort_unless($job->tenant_id === tenant('id') && $job->isPublished(), 404);

        return view('tenant.job-show', [
            'tenant' => tenant(),
            'job' => $job,
        ]);
    }

    public function apply(Request $request, TenantJob $job): RedirectResponse
    {
        abort_unless($job->tenant_id === tenant('id') && $job->isPublished(), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'motivation' => ['nullable', 'string', 'max:3000'],
            'cv' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        $cvPath = null;

        if ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store('applications/'.tenant('id'), 'public');
        }

        JobApplication::create([
            'tenant_id' => tenant('id'),
            'tenant_job_id' => $job->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'motivation' => $validated['motivation'] ?? null,
            'cv_path' => $cvPath,
            'status' => JobApplication::STATUS_NEW,
        ]);

        return redirect()
            ->route('tenant.jobs.show', $job)
            ->with('status', 'Your application has been received.');
    }

    public function contact(): View
    {
        return view('tenant.jobboard', [
            'tenant' => tenant(),
            'jobs' => $this->jobsQuery(request())->get(),
            'departments' => collect(),
            'focus' => 'contact',
        ]);
    }

    private function jobsQuery(Request $request)
    {
        return TenantJob::query()
            ->where('tenant_id', tenant('id'))
            ->where('status', TenantJob::STATUS_PUBLISHED)
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhere('department', 'like', '%'.$search.'%')
                        ->orWhere('location', 'like', '%'.$search.'%');
                });
            })
            ->when($request->filled('department'), fn ($query) => $query->where('department', $request->string('department')->toString()))
            ->latest('published_at');
    }
}
