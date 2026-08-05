<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        return view('site.projects.index', [
            'projects' => Project::public()->orderByDesc('is_featured')->orderBy('sort_order')->paginate(12),
        ]);
    }

    public function show(string $slug): View
    {
        $project = Project::public()->with(['articles.publishedRevision', 'articles.tags'])->where('slug', $slug)->firstOrFail();

        return view('site.projects.show', compact('project'));
    }
}
