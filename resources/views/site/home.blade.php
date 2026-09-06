@extends('layouts.site')

@section('title', '程序员的个人修养 / 工程现场')
@section('description', '我做 AI 教学产品，也写代码。记录教学资源平台、英语对话工具和 Laravel 项目的开发过程。')
@push('head')
    @include('shared._seo', ['title' => '程序员的个人修养 / 工程现场', 'description' => '我做 AI 教学产品，也写代码。记录教学资源平台、英语对话工具和 Laravel 项目的开发过程。', 'canonical' => route('home')])
@endpush

@section('content')
<section class="hero shell">
    <div class="hero-content">
        <p class="eyebrow">邓红宇的开发笔记</p>
        <h1><span class="hero-line hero-line-ink">把复杂流程，</span><span class="hero-line hero-line-accent">做成可验证的系统。</span></h1>
        <p class="hero-copy">这里记录教学资源平台、英语对话工具和 Laravel 项目的开发过程：遇到了什么问题，为什么这样改，上线后又发现了什么。</p>
        <div class="hero-actions"><a class="button button-primary" href="{{ route('projects.index') }}">看看我做的项目 <span aria-hidden="true">↗</span></a><a class="button button-quiet" href="{{ route('articles.index') }}">读最近的文章</a></div>
        <ul class="capability-list" aria-label="关注领域"><li>AI 教学</li><li>教学资源</li><li>Laravel</li></ul>
    </div>
    @if($spotlightArticle)
        <aside class="project-spotlight" aria-label="福宝英语对话项目">
            <a class="spotlight-image" href="{{ route('articles.show', $spotlightArticle->slug) }}" aria-label="阅读福宝实时英语对话的开发记录">
                <img src="{{ asset('images/articles/fobo-realtime-voice-release/03-realtime-conversation.png') }}" alt="福宝寻找玩具练习的历史截图，显示小熊猫、对话字幕和麦克风提示" width="1003" height="845" fetchpriority="high">
            </a>
            <div class="spotlight-copy"><p class="eyebrow">项目一瞥 · 福宝英语角</p><h2>让孩子开口说英语</h2><p>从快捷句型到实时对话，福宝加入了字幕、打断和场景练习。图中是一次“寻找玩具”练习，也保留了当时的麦克风提示。</p><a class="text-link" href="{{ route('articles.show', $spotlightArticle->slug) }}">读这次开发记录 <span aria-hidden="true">→</span></a></div>
        </aside>
    @endif
</section>

<section class="shell section-block">
    <div class="section-heading"><div><p class="eyebrow">项目</p><h2>我在做的几个项目</h2><p>课程和资源管理、AI 任务执行，还有每天的工作安排。</p></div><a class="section-link" href="{{ route('projects.index') }}">全部项目 <span aria-hidden="true">→</span></a></div>
    <div class="card-grid card-grid-three">
        @forelse($featuredProjects as $project)
            <article class="project-card"><div class="card-topline"><p class="card-kicker">{{ $project->status }}</p><span aria-hidden="true">↗</span></div><h3><a href="{{ route('projects.show', $project->slug) }}">{{ $project->name }}</a></h3><p>{{ $project->summary }}</p><a class="card-cover-link" aria-label="查看 {{ $project->name }} 项目介绍" href="{{ route('projects.show', $project->slug) }}"></a><span class="text-link">了解这个项目 →</span></article>
        @empty
            <div class="empty-state">项目介绍正在整理中。</div>
        @endforelse
    </div>
</section>

<section class="shell section-block">
    <div class="section-heading"><div><p class="eyebrow">文章</p><h2>最近写了什么</h2><p>有项目里的具体问题，也有读完技术资料后的想法。</p></div><a class="section-link" href="{{ route('articles.index') }}">全部文章 <span aria-hidden="true">→</span></a></div>
    <div class="article-list">
        @forelse($latestArticles as $article)
            @include('site.articles._card', ['article' => $article])
        @empty
            <div class="empty-state">文章正在整理中。</div>
        @endforelse
    </div>
</section>

<section class="shell section-block home-note"><p>想了解这些项目之外的我，可以读读<a href="{{ route('about') }}">关于</a>，或看看<a href="{{ route('now') }}">最近在做什么</a>。</p><a class="section-link" href="{{ route('feeds.rss') }}">通过 RSS 订阅文章 <span aria-hidden="true">→</span></a></section>
@endsection
