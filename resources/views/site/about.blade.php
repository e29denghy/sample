@extends('layouts.site')
@section('title', 'About · 程序员的个人修养 / 工程现场')
@section('content')
<section class="shell page-intro page-intro-grid about-intro"><div><p class="eyebrow">ABOUT / 关于本站</p><h1>在复杂系统里，<br>保留清晰的证据。</h1></div><p>我在 AI 教学、内容生产与 Laravel 工程的交叉处工作。关注的不只是功能完成，而是问题、边界、证据、发布和回滚能否形成完整闭环。</p></section>
<section class="shell section-block about-grid">
    <div class="about-statement"><p class="eyebrow">WHAT I DOCUMENT</p><h2>这里记录可复用的工程脉络</h2><p>从一项真实约束开始，说明如何判断、如何上线，以及哪些部分仍需继续验证。</p></div>
    <div class="focus-list">
        <article><span>01</span><div><h3>让 AI 可评估</h3><p>检索、权限、成本和生成兜底。</p></div></article>
        <article><span>02</span><div><h3>让内容可发布</h3><p>来源、审核、Manifest、Outbox 和对账。</p></div></article>
        <article><span>03</span><div><h3>让系统可上线</h3><p>Laravel、多租户、部署和生产复盘。</p></div></article>
        <article><span>04</span><div><h3>让工作可积累</h3><p>项目记忆、任务执行和个人产品实验。</p></div></article>
    </div>
</section>
<section class="shell section-block contact-strip"><p class="eyebrow">START WITH A PROBLEM</p><h2>合作或交流，可以从一个具体的工程问题开始。</h2><a class="button button-primary" href="{{ route('articles.index') }}">先看看文章 <span aria-hidden="true">→</span></a></section>
@endsection
