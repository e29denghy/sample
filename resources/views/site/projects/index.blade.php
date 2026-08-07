@extends('layouts.site')
@section('title', '项目 · 程序员的个人修养 / 工程现场')
@section('content')
<section class="shell page-intro page-intro-grid"><div><p class="eyebrow">PROJECTS / 项目档案</p><h1>代表性项目与<br>可验证的工程现场。</h1></div><p>每个项目都从问题和约束开始，以决策、证据和结果收束。这里展示的是工程脉络，不是技术栈清单。</p></section><section class="shell section-block"><div class="card-grid card-grid-three">@forelse($projects as $project)<article class="project-card"><div class="card-topline"><p class="card-kicker">{{ $project->status }}</p><span aria-hidden="true">↗</span></div><h2><a href="{{ route('projects.show', $project->slug) }}">{{ $project->name }}</a></h2><p>{{ $project->summary }}</p><a class="card-cover-link" aria-label="打开 {{ $project->name }} 项目档案" href="{{ route('projects.show', $project->slug) }}"></a><span class="text-link">打开项目档案 →</span></article>@empty<div class="empty-state">暂无公开项目。</div>@endforelse</div><div class="pagination">{{ $projects->links() }}</div></section>
@endsection
