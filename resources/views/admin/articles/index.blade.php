@extends('layouts.admin')
@section('title', '文章')
@section('content')
<div class="admin-heading"><div><p class="eyebrow">ARTICLES</p><h1>文章修订</h1></div><a class="button button-primary" href="{{ route('admin.articles.create') }}">新建文章</a></div>
<div class="admin-panel table-wrap"><table class="admin-table"><thead><tr><th>标题</th><th>状态</th><th>公开版本</th><th>更新时间</th><th></th></tr></thead><tbody>@forelse($articles as $article)<tr><td><a href="{{ route('admin.articles.edit', $article) }}">{{ $article->draftRevision?->title ?: $article->publishedRevision?->title ?: '未命名' }}</a><small>{{ $article->slug }}</small></td><td><span class="status-pill status-{{ $article->status }}">{{ $article->status }}</span></td><td>{{ $article->publishedRevision ? 'v'.$article->publishedRevision->version : '—' }}</td><td>{{ $article->updated_at?->format('Y-m-d H:i') }}</td><td><a href="{{ route('admin.articles.edit', $article) }}">编辑</a></td></tr>@empty<tr><td colspan="5">还没有文章。</td></tr>@endforelse</tbody></table></div>{{ $articles->links() }}
@endsection
