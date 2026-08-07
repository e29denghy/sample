@extends('layouts.site')
@section('title', '#{{ $tag->name }} · 程序员的个人修养 / 工程现场')
@section('content')
<section class="shell page-intro page-intro-grid"><div><p class="eyebrow">TAG / 主题</p><h1># {{ $tag->name }}</h1></div><p>这个主题下已经公开并保留版本边界的工程记录。</p></section><section class="shell section-block"><div class="article-list">@forelse($articles as $article)@include('site.articles._card', ['article' => $article])@empty<div class="empty-state">暂无公开文章。</div>@endforelse</div><div class="pagination">{{ $articles->links() }}</div></section>
@endsection
