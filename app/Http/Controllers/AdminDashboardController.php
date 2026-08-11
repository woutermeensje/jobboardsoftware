<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\JobApplication;
use App\Models\Tenant;
use App\Models\TenantJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.dashboard', [
            'user' => $request->user(),
            'stats' => [
                'users' => User::count(),
                'tenants' => Tenant::count(),
                'domains' => Domain::count(),
                'jobs' => TenantJob::count(),
                'applications' => JobApplication::count(),
            ],
            'tenants' => Tenant::with(['owner', 'domains'])->latest()->take(8)->get(),
            'domains' => Domain::with('tenant')->latest()->take(8)->get(),
        ]);
    }
}
