<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\OutboxEvent;
use App\Models\Project;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'articleCounts' => Article::query()->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status')->all(),
            'projectCount' => Project::count(),
            'pendingEvents' => OutboxEvent::where('status', 'pending')->count(),
            'urls' => [
                'projectsCreate' => route('admin.projects.create'),
                'articlesCreate' => route('admin.articles.create'),
            ],
        ]);
    }
}
