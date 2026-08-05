<article class="article-card">
    <div class="article-card-meta"><time datetime="{{ $article->published_at?->toIso8601String() }}">{{ $article->published_at?->format('Y-m-d') }}</time><span>v{{ $article->publishedRevision->version }}</span>@if($article->publishedRevision->verification_status === 'valid')<span class="status-valid">已验证</span>@endif</div>
    <h3><a href="{{ route('articles.show', $article->slug) }}">{{ $article->publishedRevision->title }}</a></h3>
    <p>{{ $article->publishedRevision->excerpt }}</p>
    <div class="tag-row">@foreach($article->tags as $tag)<a href="{{ route('tags.show', $tag->slug) }}">#{{ $tag->name }}</a>@endforeach</div>
</article>
