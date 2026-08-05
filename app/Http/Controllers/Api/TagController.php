<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Tag::whereHas('articles', fn ($query) => $query->published())->orderBy('name')->get(['name', 'slug']));
    }

    public function show(string $slug): JsonResponse
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        return response()->json([
            'name' => $tag->name,
            'slug' => $tag->slug,
            'articles' => $tag->articles()->published()->with('publishedRevision')->latest('published_at')->get()->map(fn ($article): array => [
                'id' => $article->public_id,
                'slug' => $article->slug,
                'title' => $article->publishedRevision->title,
                'excerpt' => $article->publishedRevision->excerpt,
                'published_at' => $article->published_at?->toIso8601String(),
            ])->values(),
        ]);
    }
}
