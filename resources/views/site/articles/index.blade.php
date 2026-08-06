@extends('layouts.site')
@section('title', '文章 · 程序员的个人修养 / 工程现场')
@section('content')
<section class="shell page-intro"><p class="eyebrow">ARTICLES</p><h1>把问题、约束、决策和验证记录下来。</h1><p>按工程问题组织内容，技术栈作为标签，而不是目录的终点。</p></section>
<section class="shell section-block"><div class="article-list">@forelse($articles as $article)@include('site.articles._card', ['article' => $article])@empty<div class="empty-state">暂无公开文章。</div>@endforelse</div><div class="pagination">{{ $articles->links() }}</div></section>
@endsection
