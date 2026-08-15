<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\JobApplication;
use App\Models\TenantJob;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tenants = $user->ownedTenants()
            ->with(['domains'])
            ->withCount(['jobs', 'applications'])
            ->latest()
            ->get();

        $tenantIds = $tenants->pluck('id');

        return view('client.dashboard', [
            'user' => $user,
            'tenants' => $tenants,
            'domains' => Domain::query()->whereIn('tenant_id', $tenantIds)->latest()->take(6)->get(),
            'jobs' => TenantJob::query()->whereIn('tenant_id', $tenantIds)->latest()->take(6)->get(),
            'applications' => JobApplication::query()->whereIn('tenant_id', $tenantIds)->latest()->take(6)->get(),
        ]);
    }

    public function section(Request $request, string $section): View
    {
        $sectionData = $this->sections()[$section] ?? [
            'title' => str($section)->headline()->toString(),
            'description' => 'This custom client dashboard section is ready to be designed.',
        ];

        return view('client.section', [
            'user' => $request->user(),
            'section' => $sectionData,
        ]);
    }

    /**
     * @return array<string, array{title: string, description: string}>
     */
    private function sections(): array
    {
        return [
            'environments' => [
                'title' => 'Environments',
                'description' => 'Design the custom overview and settings for a user job board environment here.',
            ],
            'create-environment' => [
                'title' => 'Create environment',
                'description' => 'Build the custom flow for creating a new job board environment here.',
            ],
            'jobs' => [
                'title' => 'Jobs',
                'description' => 'Build the custom job management screens here.',
            ],
            'create-job' => [
                'title' => 'Create job',
                'description' => 'Build the custom job creation form here.',
            ],
            'domains' => [
                'title' => 'Domains',
                'description' => 'Build the custom domain connection and DNS verification screens here.',
            ],
            'create-domain' => [
                'title' => 'Add domain',
                'description' => 'Build the custom form for connecting a domain here.',
            ],
            'applications' => [
                'title' => 'Applications',
                'description' => 'Build the custom applicant management screens here.',
            ],
            'billing' => [
                'title' => 'Billing',
                'description' => 'Build the custom billing and package management screens here.',
            ],
            'marketing' => [
                'title' => 'Marketing',
                'description' => 'Build the custom marketing overview here.',
            ],
            'landingpagina' => [
                'title' => 'Add landingpagina',
                'description' => 'Build the custom landing page editor here.',
            ],
            'socials' => [
                'title' => 'Add socials',
                'description' => 'Build the custom social channel settings here.',
            ],
            'jobs-settings' => [
                'title' => 'Jobs settings',
                'description' => 'Build the custom job settings overview here.',
            ],
            'sector' => [
                'title' => 'Add sector',
                'description' => 'Build the custom sector management screen here.',
            ],
            'categorie' => [
                'title' => 'Add categorie',
                'description' => 'Build the custom category management screen here.',
            ],
            'job-type' => [
                'title' => 'Add job type',
                'description' => 'Build the custom job type management screen here.',
            ],
            'organization-type' => [
                'title' => 'Add organization type',
                'description' => 'Build the custom organization type management screen here.',
            ],
            'companies' => [
                'title' => 'Companies',
                'description' => 'Build the custom company management overview here.',
            ],
            'create-company' => [
                'title' => 'Add company',
                'description' => 'Build the custom company creation form here.',
            ],
        ];
    }
}
