@extends('layouts.site')

@section('title', '程序员的个人修养 / 工程现场')
@section('description', 'Production notes on AI, content systems and Laravel.')
@push('head')
    @include('shared._seo', ['title' => '程序员的个人修养 / 工程现场', 'description' => 'Production notes on AI, content systems and Laravel.', 'canonical' => route('home')])
@endpush

@section('content')
<section class="hero shell">
    <div class="hero-content">
        <p class="eyebrow">PRODUCTION NOTES · AI / CONTENT / LARAVEL</p>
        <h1><span class="hero-line hero-line-ink">把复杂流程，</span><span class="hero-line hero-line-accent">做成可验证的系统。</span></h1>
        <p class="hero-copy">记录 AI 教学、内容生产与 Laravel 工程中，从约束和决策到上线与复盘的真实过程。</p>
        <div class="hero-actions"><a class="button button-primary" href="{{ route('projects.index') }}">查看工程案例 <span aria-hidden="true">↗</span></a><a class="button button-quiet" href="{{ route('articles.index') }}">阅读最新文章</a></div>
        <ul class="capability-list" aria-label="关注领域"><li>AI 教学</li><li>内容发布</li><li>Laravel</li><li>工作流</li></ul>
    </div>
    <aside class="system-card" aria-label="工程交付流程">
        <div class="system-card-head"><div><span class="status-dot"></span> SYSTEM TRACE</div><span>READY</span></div>
        <ol class="workflow-list">
            <li><span class="workflow-index">01</span><div><strong>问题</strong><small>Problem</small></div><span class="workflow-state">DEFINE</span></li>
            <li><span class="workflow-index">02</span><div><strong>约束</strong><small>Constraints</small></div><span class="workflow-state">BOUND</span></li>
            <li><span class="workflow-index">03</span><div><strong>决策</strong><small>Decision</small></div><span class="workflow-state">COMMIT</span></li>
            <li><span class="workflow-index">04</span><div><strong>验证</strong><small>Verification</small></div><span class="workflow-state">PROVE</span></li>
            <li><span class="workflow-index">05</span><div><strong>上线</strong><small>Release</small></div><span class="workflow-state is-live">LIVE</span></li>
        </ol>
        <div class="system-card-foot"><span>evidence_required: true</span><span>rollback_ready: true</span></div>
    </aside>
</section>

<section class="shell section-block">
    <div class="section-heading"><div><p class="eyebrow">SELECTED PROJECTS</p><h2>正在把什么做成系统</h2><p>从真实问题出发，用证据说明决策与结果。</p></div><a class="section-link" href="{{ route('projects.index') }}">全部项目 <span aria-hidden="true">→</span></a></div>
    <div class="card-grid card-grid-three">
        @forelse($featuredProjects as $project)
            <article class="project-card"><div class="card-topline"><p class="card-kicker">{{ $project->status }}</p><span aria-hidden="true">↗</span></div><h3><a href="{{ route('projects.show', $project->slug) }}">{{ $project->name }}</a></h3><p>{{ $project->summary }}</p><a class="card-cover-link" aria-label="查看 {{ $project->name }} 项目档案" href="{{ route('projects.show', $project->slug) }}"></a><span class="text-link">查看证据与决策 →</span></article>
        @empty
            <div class="empty-state">首批项目案例正在整理中。后台发布后会出现在这里。</div>
        @endforelse
    </div>
</section>

<section class="shell section-block">
    <div class="section-heading"><div><p class="eyebrow">LATEST NOTES</p><h2>最近的工程文章</h2><p>把验证过的现场整理成可以复用的工程记录。</p></div><a class="section-link" href="{{ route('articles.index') }}">全部文章 <span aria-hidden="true">→</span></a></div>
    <div class="article-list">
        @forelse($latestArticles as $article)
            @include('site.articles._card', ['article' => $article])
        @empty
            <div class="empty-state">还没有公开文章。先在后台创建、预览并发布第一篇修订。</div>
        @endforelse
    </div>
</section>

<section class="shell principles section-block"><div class="section-heading"><div><p class="eyebrow">WORKING PRINCIPLES</p><h2>用工程纪律保护真实</h2></div></div><div class="principle-grid"><div><span>01</span><strong>先证据，后结论</strong><p>让数据库、请求、测试和生产读数支撑判断。</p></div><div><span>02</span><strong>发布是一个事务</strong><p>正文版本冻结，渠道投递独立，失败可核对。</p></div><div><span>03</span><strong>保留未验证部分</strong><p>明确边界和风险，避免把计划写成事实。</p></div></div></section>
@endsection
