@extends('layouts.site')
@section('title', '文章 · 程序员的个人修养 / 工程现场')
@section('content')
<section class="shell page-intro page-intro-grid"><div><p class="eyebrow">ARTICLES / 工程文章</p><h1>把解决问题的过程，<br>变成可复用的记录。</h1></div><p>按工程问题组织内容。每篇文章都尽量说明约束、决策、证据与适用边界，而不只是罗列技术名词。</p></section>
<section class="shell section-block"><div class="article-list">@forelse($articles as $article)@include('site.articles._card', ['article' => $article])@empty<div class="empty-state">暂无公开文章。</div>@endforelse</div><div class="pagination">{{ $articles->links() }}</div></section>
@endsection
