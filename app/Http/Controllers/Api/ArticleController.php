<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $articles = Article::published()
            ->with(['publishedRevision.coverMedia', 'tags', 'projects'])
            ->when($request->filled('tag'), fn ($query) => $query->whereHas('tags', fn ($tags) => $tags->where('slug', (string) $request->string('tag'))))
            ->latest('published_at')
            ->cursorPaginate(20);
        $payload = [
            'data' => $articles->map(fn (Article $article): array => $this->payload($article, false))->values(),
            'next_cursor' => $articles->nextCursor()?->encode(),
        ];

        return $this->conditionalJson($request, $payload);
    }

    public function show(Request $request, string $identifier): JsonResponse
    {
        $article = Article::published()
            ->with(['publishedRevision.coverMedia', 'tags', 'projects'])
            ->where(fn ($query) => $query->where('public_id', $identifier)->orWhere('slug', $identifier))
            ->firstOrFail();

        return $this->conditionalJson($request, $this->payload($article, true), $article->publishedRevision->content_hash);
    }

    private function payload(Article $article, bool $includeHtml): array
    {
        $revision = $article->publishedRevision;

        return [
            'id' => $article->public_id,
            'slug' => $article->slug,
            'title' => $revision->title,
            'excerpt' => $revision->excerpt,
            'published_at' => $article->published_at?->toIso8601String(),
            'updated_at' => $article->updated_at?->toIso8601String(),
            'verified_at' => $revision->verified_at?->toIso8601String(),
            'revision_version' => $revision->version,
            'content_hash' => $revision->content_hash,
            'html' => $includeHtml ? $revision->rendered_html : null,
            'canonical_url' => $article->canonicalUrl(),
            'share_title' => $revision->seo_title ?: $revision->title,
            'share_image' => $revision->coverMedia?->url(),
            'tags' => $article->tags->map(fn ($tag): array => ['name' => $tag->name, 'slug' => $tag->slug])->values(),
            'projects' => $article->projects->map(fn ($project): array => ['name' => $project->name, 'slug' => $project->slug])->values(),
        ];
    }

    private function conditionalJson(Request $request, array $payload, ?string $etag = null): JsonResponse
    {
        $etag ??= hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $response = response()->json($payload)->setEtag($etag);

        $response->isNotModified($request);

        return $response;
    }
}
