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
            <a class="brand" href="{{ route('home') }}" aria-label="程序员的个人修养首页">
                <span class="brand-mark" aria-hidden="true">修</span>
                <span class="brand-copy">程序员的个人修养 <small>工程现场</small></span>
            </a>
            <nav aria-label="主导航" class="site-nav">
                <a href="{{ route('articles.index') }}" @class(['is-active' => request()->routeIs('articles.*', 'tags.*')]) @if(request()->routeIs('articles.*', 'tags.*')) aria-current="page" @endif>文章</a>
                <a href="{{ route('projects.index') }}" @class(['is-active' => request()->routeIs('projects.*')]) @if(request()->routeIs('projects.*')) aria-current="page" @endif>项目</a>
                <a href="{{ route('now') }}" @class(['is-active' => request()->routeIs('now')]) @if(request()->routeIs('now')) aria-current="page" @endif>Now</a>
                <a href="{{ route('about') }}" @class(['is-active' => request()->routeIs('about')]) @if(request()->routeIs('about')) aria-current="page" @endif>About</a>
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
            <div class="footer-intro"><strong>程序员的个人修养</strong><p>记录问题、约束、决策与验证，也记录系统如何真正上线。</p></div>
            <nav aria-label="页脚导航" class="footer-links"><a href="{{ route('articles.index') }}">文章</a><a href="{{ route('projects.index') }}">项目</a><a href="{{ route('about') }}">关于</a><a href="{{ route('feeds.rss') }}">RSS</a><a href="{{ route('feeds.atom') }}">Atom</a></nav>
            <div class="footer-meta"><span>© {{ now()->year }} denghy.cn</span><a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener">粤ICP备18024712号</a></div>
        </div>
    </footer>
</body>
</html>
