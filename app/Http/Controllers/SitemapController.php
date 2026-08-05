<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Project;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        return response()->view('feeds.sitemap', [
            'articles' => Article::published()->latest('published_at')->get(),
            'projects' => Project::public()->latest('published_at')->get(),
        ], 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
