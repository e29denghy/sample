@extends('layouts.site')

@section('title', '程序员的个人修养 / 工程现场')
@section('description', 'Production notes on AI, content systems and Laravel.')
@push('head')
    @include('shared._seo', ['title' => '程序员的个人修养 / 工程现场', 'description' => 'Production notes on AI, content systems and Laravel.', 'canonical' => route('home')])
@endpush

@section('content')
<section class="hero shell">
    <p class="eyebrow">PRODUCTION NOTES ON AI, CONTENT SYSTEMS AND LARAVEL</p>
    <h1>把复杂的 AI、内容与业务流程，做成可验证、可发布、可维护的系统。</h1>
    <p class="hero-copy">我在 AI 教学、内容生产与 Laravel 工程的交叉处工作。这里记录一个系统从问题、约束和决策，到验证、上线与复盘的完整过程。</p>
    <div class="hero-actions"><a class="button button-primary" href="{{ route('projects.index') }}">查看工程案例</a><a class="button button-quiet" href="{{ route('feeds.rss') }}">订阅 RSS</a></div>
</section>

<section class="shell section-block">
    <div class="section-heading"><div><p class="eyebrow">SELECTED PROJECTS</p><h2>正在把什么做成系统</h2></div><a href="{{ route('projects.index') }}">全部项目 →</a></div>
    <div class="card-grid card-grid-three">
        @forelse($featuredProjects as $project)
            <article class="project-card"><p class="card-kicker">{{ $project->status }}</p><h3><a href="{{ route('projects.show', $project->slug) }}">{{ $project->name }}</a></h3><p>{{ $project->summary }}</p><a class="text-link" href="{{ route('projects.show', $project->slug) }}">查看证据与决策 →</a></article>
        @empty
            <div class="empty-state">首批项目案例正在整理中。后台发布后会出现在这里。</div>
        @endforelse
    </div>
</section>

<section class="shell section-block">
    <div class="section-heading"><div><p class="eyebrow">LATEST NOTES</p><h2>最近的工程文章</h2></div><a href="{{ route('articles.index') }}">全部文章 →</a></div>
    <div class="article-list">
        @forelse($latestArticles as $article)
            @include('site.articles._card', ['article' => $article])
        @empty
            <div class="empty-state">还没有公开文章。先在后台创建、预览并发布第一篇修订。</div>
        @endforelse
    </div>
</section>

<section class="shell principles section-block"><p class="eyebrow">WORKING PRINCIPLES</p><div class="principle-grid"><div><strong>先证据，后结论</strong><p>让数据库、请求、测试和生产读数支撑判断。</p></div><div><strong>发布是一个事务</strong><p>正文版本冻结，渠道投递独立，失败可核对。</p></div><div><strong>保留未验证部分</strong><p>明确边界和风险，避免把计划写成事实。</p></div></div></section>
@endsection
