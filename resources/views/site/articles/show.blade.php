@extends('layouts.site')
@php($revision = $article->publishedRevision)
@section('title', $revision->seo_title ?: $revision->title)
@section('description', $revision->seo_description ?: $revision->excerpt)
@section('canonical', $article->canonicalUrl())
@push('head')
    @include('shared._seo', ['title' => $revision->seo_title ?: $revision->title, 'description' => $revision->seo_description ?: $revision->excerpt, 'canonical' => $article->canonicalUrl(), 'ogType' => 'article', 'shareImage' => $revision->coverMedia?->url()])
    <script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'Article', 'headline' => $revision->title, 'datePublished' => $article->first_published_at?->toIso8601String(), 'dateModified' => $article->published_at?->toIso8601String(), 'url' => $article->canonicalUrl()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush
@section('content')
<article class="shell article-page">
    <header class="article-header"><a class="back-link" href="{{ route('articles.index') }}">← 返回文章</a><p class="eyebrow">ENGINEERING NOTE · v{{ $revision->version }}</p><h1>{{ $revision->title }}</h1><p class="article-lede">{{ $revision->excerpt }}</p><div class="article-card-meta"><time datetime="{{ $article->published_at?->toIso8601String() }}">发布于 {{ $article->first_published_at?->format('Y-m-d') }}</time><span>更新于 {{ $article->published_at?->format('Y-m-d') }}</span>@if($revision->verified_at)<span class="status-valid"><i></i> 验证于 {{ $revision->verified_at->format('Y-m-d') }}</span>@endif</div></header>
    <div class="article-body prose">{!! $revision->rendered_html !!}</div>
    <footer class="article-footer"><div><p class="footer-label">文章主题</p><div class="tag-row">@foreach($article->tags as $tag)<a href="{{ route('tags.show', $tag->slug) }}"># {{ $tag->name }}</a>@endforeach</div></div>@if($article->projects->isNotEmpty())<div><p class="footer-label">关联项目</p><p>@foreach($article->projects as $project)<a href="{{ route('projects.show', $project->slug) }}">{{ $project->name }}</a>@if(!$loop->last)、@endif @endforeach</p></div>@endif</footer>
</article>
@endsection
