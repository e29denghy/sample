<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $articles = Article::published()
            ->with(['publishedRevision', 'tags', 'projects'])
            ->when($request->filled('tag'), fn ($query) => $query->whereHas('tags', fn ($tags) => $tags->where('slug', (string) $request->string('tag'))))
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('site.articles.index', compact('articles'));
    }

    public function show(string $slug): View|RedirectResponse
    {
        $article = Article::published()
            ->with(['publishedRevision.coverMedia', 'tags', 'projects'])
            ->where('slug', $slug)
            ->first();

        if (! $article) {
            $redirect = ArticleRedirect::where('from_slug', $slug)->first();

            if ($redirect) {
                return redirect()->route('articles.show', $redirect->to_slug, 301);
            }

            abort(404);
        }

        return view('site.articles.show', compact('article'));
    }
}
