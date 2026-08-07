@extends('layouts.site')
@section('title', 'Now · 程序员的个人修养 / 工程现场')
@section('content')
<section class="shell page-intro page-intro-grid"><div><p class="eyebrow">NOW / 2026-08</p><h1>当前正在把什么，<br>做成可复用的系统。</h1></div><p>这是一个低频更新的工作切片，只呈现当前重点、最近交付与明确的克制边界。</p></section>
<section class="shell section-block now-grid">
    <article class="now-card is-current"><div class="now-card-head"><span>01</span><i></i></div><p class="card-kicker">IN PROGRESS</p><h2>正在推进</h2><p>把 AI 教学、内容发布和 Laravel 生产系统中的验证证据，整理成可复用的工程文章与项目档案。</p></article>
    <article class="now-card"><div class="now-card-head"><span>02</span></div><p class="card-kicker">RECENTLY SHIPPED</p><h2>最近交付</h2><p>完成个人网站内容模型的第一轮实现：文章修订、公开发布、Feed 和小程序只读 API 共享同一份正文事实来源。</p></article>
    <article class="now-card"><div class="now-card-head"><span>03</span></div><p class="card-kicker">BOUNDARIES</p><h2>保持克制</h2><p>不把历史活动自动改写成今日承诺；不把计划、草稿或未验证结果展示为公开事实。</p></article>
</section>
@endsection
