<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Projects/Index', [
            'projects' => Project::latest('updated_at')->paginate(20),
            'createUrl' => route('admin.projects.create'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Projects/Form', $this->formProps(new Project));
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->persist($request, new Project);
    }

    public function edit(Project $project): Response
    {
        return Inertia::render('Admin/Projects/Form', $this->formProps($project));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        return $this->persist($request, $project);
    }

    private function persist(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:projects,slug,'.$project->id],
            'status' => ['required', 'string', 'max:30'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'problem' => ['nullable', 'string'],
            'decisions' => ['nullable', 'string'],
            'evidence' => ['nullable', 'string'],
            'outcome' => ['nullable', 'string'],
            'source_url' => ['nullable', 'url:http,https', 'max:2048'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);
        $isPublic = $request->boolean('is_public');
        $data['is_public'] = $isPublic;
        $data['is_featured'] = $request->boolean('is_featured');
        $data['published_at'] = $isPublic ? ($project->published_at ?: now()) : null;

        DB::transaction(function () use ($project, $data, $request): void {
            $project->fill($data)->save();
            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => $project->wasRecentlyCreated ? 'project.created' : 'project.updated',
                'auditable_type' => Project::class,
                'auditable_id' => $project->id,
            ]);
        });

        return redirect()->route('admin.projects.edit', $project)->with('success', '项目档案已保存。');
    }

    private function formProps(Project $project): array
    {
        return [
            'project' => $project->exists ? $project : null,
            'formAction' => $project->exists ? route('admin.projects.update', $project) : route('admin.projects.store'),
            'formMethod' => $project->exists ? 'patch' : 'post',
        ];
    }
}
