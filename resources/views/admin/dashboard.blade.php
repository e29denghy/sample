@extends('layouts.admin')
@section('title', '概览')
@section('content')
<div class="admin-heading"><div><p class="eyebrow">ADMIN</p><h1>内容发布概览</h1></div><div class="hero-actions"><a class="button button-quiet" href="{{ route('admin.projects.create') }}">新建项目</a><a class="button button-primary" href="{{ route('admin.articles.create') }}">新建文章</a></div></div>
<div class="stat-grid"><div class="stat-card"><span>草稿</span><strong>{{ $articleCounts['draft'] ?? 0 }}</strong></div><div class="stat-card"><span>已发布</span><strong>{{ $articleCounts['published'] ?? 0 }}</strong></div><div class="stat-card"><span>项目</span><strong>{{ $projectCount }}</strong></div><div class="stat-card"><span>待处理事件</span><strong>{{ $pendingEvents }}</strong></div></div>
<section class="admin-panel"><h2>发布原则</h2><p>网站数据库是正文事实来源；文章修订冻结后才进入公开页面、RSS、Atom 和只读 API。公众号投递仍需单独人工确认。</p></section>
@endsection
