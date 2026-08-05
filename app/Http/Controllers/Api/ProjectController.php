<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Project::public()->orderByDesc('is_featured')->orderBy('sort_order')->get()->map(fn ($project): array => $this->payload($project)),
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        return response()->json($this->payload(Project::public()->with('articles.publishedRevision')->where('slug', $slug)->firstOrFail(), true));
    }

    private function payload(Project $project, bool $detail = false): array
    {
        $payload = [
            'id' => $project->public_id,
            'name' => $project->name,
            'slug' => $project->slug,
            'status' => $project->status,
            'summary' => $project->summary,
            'published_at' => $project->published_at?->toIso8601String(),
            'canonical_url' => route('projects.show', $project->slug),
        ];

        if ($detail) {
            $payload += [
                'problem' => $project->problem,
                'decisions' => $project->decisions,
                'evidence' => $project->evidence,
                'outcome' => $project->outcome,
                'articles' => $project->articles->map(fn ($article): array => [
                    'id' => $article->public_id,
                    'slug' => $article->slug,
                    'title' => $article->publishedRevision->title,
                ])->values(),
            ];
        }

        return $payload;
    }
}
