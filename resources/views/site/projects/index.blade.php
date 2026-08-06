@extends('layouts.site')
@section('title', '项目 · 程序员的个人修养 / 工程现场')
@section('content')
<section class="shell page-intro"><p class="eyebrow">PROJECTS</p><h1>代表性项目与可验证的工程现场。</h1><p>每个项目都从问题、约束、决策、证据和结果开始，而不是从技术名词开始。</p></section><section class="shell section-block"><div class="card-grid card-grid-three">@forelse($projects as $project)<article class="project-card"><p class="card-kicker">{{ $project->status }}</p><h2><a href="{{ route('projects.show', $project->slug) }}">{{ $project->name }}</a></h2><p>{{ $project->summary }}</p><a class="text-link" href="{{ route('projects.show', $project->slug) }}">打开项目档案 →</a></article>@empty<div class="empty-state">暂无公开项目。</div>@endforelse</div>{{ $projects->links() }}</section>
@endsection
