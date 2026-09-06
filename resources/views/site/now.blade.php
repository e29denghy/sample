@extends('layouts.site')
@section('title', '近况 · 程序员的个人修养 / 工程现场')
@section('description', '最近在整理网站和项目文章，回看福宝实时语音与开物的开发记录。更新于 2026 年 9 月 6 日。')
@section('content')
<section class="shell page-intro page-intro-grid"><div><p class="eyebrow">近况 · <time datetime="2026-09-06">2026 年 9 月 6 日</time></p><h1>最近在做什么</h1></div><p>这段时间在整理网站和项目文章。技术细节已经写了不少，接下来想把当时遇到的问题和做选择的原因讲得更清楚。</p></section>
<section class="shell section-block now-grid">
    <article class="now-card is-current"><div class="now-card-head"><span>01</span><i></i></div><p class="card-kicker">正在做</p><h2>重新整理网站的文字</h2><p>先更新首页和项目介绍，再整理福宝、开物和模型观察三篇文章的材料。保留有用的技术细节，把重复的解释和空泛的总结收一收。</p></article>
    <article class="now-card"><div class="now-card-head"><span>02</span></div><p class="card-kicker">最近的项目记录</p><h2>福宝与开物</h2><p>8 月记录了福宝实时语音的邀请测试，也写了开物接入 DeepSeek Harness 的过程。一次关注语音会话和费用，一次关注任务批准与执行。</p><a href="{{ route('articles.index') }}">阅读这些记录 →</a></article>
    <article class="now-card"><div class="now-card-head"><span>03</span></div><p class="card-kicker">接下来想弄清楚</p><h2>怎样让文章更好读</h2><p>同一个工程问题，哪些细节值得在正文展开，哪些适合放进附录？我会先用几篇已有文章试着调整，再看是否更容易读懂。</p></article>
</section>
@endsection
