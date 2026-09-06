<article class="article-card">
    <div class="article-card-date"><time datetime="{{ $article->published_at?->toIso8601String() }}">{{ $article->published_at?->format('m.d') }}</time><span>{{ $article->published_at?->format('Y') }}</span></div>
    <div class="article-card-content"><h3><a href="{{ route('articles.show', $article->slug) }}">{{ $article->publishedRevision->title }}</a></h3><p>{{ $article->publishedRevision->excerpt }}</p><div class="tag-row">@foreach($article->tags as $tag)<a href="{{ route('tags.show', $tag->slug) }}"># {{ $tag->name }}</a>@endforeach</div></div>
    <a class="article-card-arrow" aria-label="阅读《{{ $article->publishedRevision->title }}》" href="{{ route('articles.show', $article->slug) }}">↗</a>
</article>
