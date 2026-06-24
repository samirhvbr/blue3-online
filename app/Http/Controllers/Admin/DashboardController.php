<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Component;
use App\Models\Incident;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'componentsCount' => Component::count(),
            'openIncidents' => Incident::whereNull('resolved_at')->count(),
            'recentIncidents' => Incident::with('component')->orderByDesc('started_at')->take(5)->get(),
        ]);
    }
}
