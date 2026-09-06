<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Project;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('site.home', [
            'spotlightArticle' => Article::published()->where('slug', 'fobo-realtime-voice-from-harness-to-production')->first(),
            'featuredProjects' => Project::public()->where('is_featured', true)->orderBy('sort_order')->limit(3)->get(),
            'latestArticles' => Article::published()->with(['publishedRevision', 'tags', 'projects'])->latest('published_at')->limit(5)->get(),
        ]);
    }
}
