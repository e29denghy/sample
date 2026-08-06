<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '程序员的个人修养 / 工程现场')</title>
    <meta name="description" content="@yield('description', 'Production notes on AI, content systems and Laravel.')">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <link rel="alternate" type="application/rss+xml" title="程序员的个人修养 RSS" href="{{ route('feeds.rss') }}">
    <link rel="alternate" type="application/atom+xml" title="程序员的个人修养 Atom" href="{{ route('feeds.atom') }}">
    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="site-body">
    <header class="site-header">
        <div class="shell nav-shell">
            <a class="brand" href="{{ route('home') }}">程序员的个人修养 <span>/ 工程现场</span></a>
            <nav aria-label="主导航" class="site-nav">
                <a href="{{ route('articles.index') }}">文章</a>
                <a href="{{ route('projects.index') }}">项目</a>
                <a href="{{ route('now') }}">Now</a>
                <a href="{{ route('about') }}">About</a>
                <a href="{{ route('feeds.rss') }}">RSS</a>
                @auth
                    @can('admin') <a href="{{ route('admin.dashboard') }}">后台</a> @endcan
                @endauth
            </nav>
        </div>
    </header>

    <main>
        <div class="shell flash-stack">
            @include('shared._messages')
            @include('shared._errors')
        </div>
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="shell footer-grid">
            <p>程序员的个人修养 · 把复杂的 AI、内容与业务流程，做成可验证、可发布、可维护的系统。</p>
            <div><a href="{{ route('feeds.rss') }}">RSS</a><span aria-hidden="true"> · </span><a href="{{ route('feeds.atom') }}">Atom</a></div>
            <div><a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener">粤ICP备18024712号</a></div>
        </div>
    </footer>
</body>
</html>
