@extends('layouts.admin')
@section('title', '项目')
@section('content')
<div class="admin-heading"><div><p class="eyebrow">PROJECTS</p><h1>项目档案</h1></div><a class="button button-primary" href="{{ route('admin.projects.create') }}">新建项目</a></div><div class="admin-panel table-wrap"><table class="admin-table"><thead><tr><th>名称</th><th>状态</th><th>公开</th><th>更新时间</th><th></th></tr></thead><tbody>@forelse($projects as $project)<tr><td><a href="{{ route('admin.projects.edit', $project) }}">{{ $project->name }}</a><small>{{ $project->slug }}</small></td><td>{{ $project->status }}</td><td>{{ $project->is_public ? '是' : '否' }}</td><td>{{ $project->updated_at?->format('Y-m-d H:i') }}</td><td><a href="{{ route('admin.projects.edit', $project) }}">编辑</a></td></tr>@empty<tr><td colspan="5">还没有项目档案。</td></tr>@endforelse</tbody></table></div>{{ $projects->links() }}
@endsection
