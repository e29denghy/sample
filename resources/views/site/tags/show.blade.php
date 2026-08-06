@extends('layouts.site')
@section('title', '#{{ $tag->name }} · 程序员的个人修养 / 工程现场')
@section('content')
<section class="shell page-intro"><p class="eyebrow">TAG</p><h1>#{{ $tag->name }}</h1><p>这个主题下的公开工程记录。</p></section><section class="shell section-block"><div class="article-list">@forelse($articles as $article)@include('site.articles._card', ['article' => $article])@empty<div class="empty-state">暂无公开文章。</div>@endforelse</div>{{ $articles->links() }}</section>
@endsection
