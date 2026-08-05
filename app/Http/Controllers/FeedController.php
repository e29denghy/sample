<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    public static function absoluteHtml(string $html): string
    {
        $baseUrl = rtrim(config('app.url'), '/');

        return preg_replace('/(?P<attribute>src|href)="\/(?P<path>[^"]*)"/i', '$attribute="'.$baseUrl.'/$path"', $html) ?: $html;
    }

    public function rss(Request $request): Response
    {
        $articles = Article::published()->with(['publishedRevision', 'tags'])->latest('published_at')->limit(20)->get();
        $lastModified = $articles->max('published_at') ?: now();
        $body = view('feeds.rss', compact('articles'))->render();

        return $this->conditional($request, $body, $lastModified, 'application/rss+xml; charset=UTF-8');
    }

    public function atom(Request $request): Response
    {
        $articles = Article::published()->with(['publishedRevision', 'tags'])->latest('published_at')->limit(20)->get();
        $lastModified = $articles->max('published_at') ?: now();
        $body = view('feeds.atom', compact('articles'))->render();

        return $this->conditional($request, $body, $lastModified, 'application/atom+xml; charset=UTF-8');
    }

    private function conditional(Request $request, string $body, mixed $lastModified, string $contentType): Response
    {
        $response = response($body, 200, [
            'Content-Type' => $contentType,
            'Cache-Control' => 'public, max-age=300',
        ]);
        $response->setEtag(md5($body));
        $response->setLastModified($lastModified);

        $response->isNotModified($request);

        return $response;
    }
}
