<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\OutboxEvent;
use App\Models\Project;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'articleCounts' => Article::query()->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status'),
            'projectCount' => Project::count(),
            'pendingEvents' => OutboxEvent::where('status', 'pending')->count(),
        ]);
    }
}
