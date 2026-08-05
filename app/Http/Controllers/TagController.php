<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\View\View;

class TagController extends Controller
{
    public function show(string $slug): View
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();
        $articles = $tag->articles()->published()->with(['publishedRevision', 'tags', 'projects'])->latest('published_at')->paginate(12);

        return view('site.tags.show', compact('tag', 'articles'));
    }
}
