@extends('layouts.site')
@section('title', '关于 · 程序员的个人修养 / 工程现场')
@section('description', '我是邓红宇，做 AI 教学产品，也用 Laravel 和 Vue 开发应用。这里记录项目里的问题、选择和结果。')
@section('content')
<section class="shell page-intro page-intro-grid about-intro"><div><p class="eyebrow">关于</p><h1>你好，我是邓红宇。</h1></div><p>我做 AI 教学产品，也用 Laravel 和 Vue 开发应用。教学资源怎么找到、孩子怎么开始一次英语练习、AI 任务怎么交给工具执行，都是这些项目里需要解决的问题。</p></section>
<section class="shell section-block about-grid">
    <div class="about-statement"><p class="eyebrow">写在这里</p><h2>把做项目时的选择记下来</h2><p>这个网站收集我的开发记录和技术阅读笔记。我会写出问题出现的条件、尝试过的办法，以及最后为什么这样做。涉及模型评测时注明资料来源；项目测试做到哪一步，就说明到哪一步。</p></div>
    <div class="focus-list">
        <article><span>01</span><div><h3><a href="{{ route('projects.show', 'interapi') }}">InterAPI / 知阅录</a></h3><p>管理课程和教学资源，处理搜索、播放与使用权限。</p></div></article>
        <article><span>02</span><div><h3><a href="{{ route('projects.show', 'fobo-english-corner') }}">福宝英语角</a></h3><p>给孩子提供英语句型和场景练习，逐步加入实时语音对话。</p></div></article>
        <article><span>03</span><div><h3><a href="{{ route('projects.show', 'kaiwu') }}">KAIWU / 开物</a></h3><p>整理交给 AI 的任务，记录批准、执行结果和失败后的重试。</p></div></article>
        <article><span>04</span><div><h3><a href="{{ route('projects.show', 'myaiworkflow') }}">MyAiWorkFlow / 迹序</a></h3><p>把项目记录、待办和今天选定的工作放在一起，方便接着做。</p></div></article>
    </div>
</section>
<section class="shell section-block contact-strip"><p class="eyebrow">交流</p><h2>如果你也在做类似的产品，欢迎聊聊具体遇到的问题。</h2><a class="button button-primary" href="https://github.com/e29denghy">在 GitHub 找到我 <span aria-hidden="true">↗</span></a></section>
@endsection
