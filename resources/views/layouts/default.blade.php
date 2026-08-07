<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '程序员的个人修养 / 工程现场')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="site-body auth-body">
    <header class="site-header">
        <div class="shell nav-shell">
            <a class="brand" href="{{ route('home') }}">
                <span class="brand-mark" aria-hidden="true">修</span>
                <span class="brand-copy">程序员的个人修养 <small>工程现场</small></span>
            </a>
            <nav class="site-nav" aria-label="辅助导航"><a href="{{ route('home') }}">返回首页</a><a href="{{ route('about') }}">About</a></nav>
        </div>
    </header>
    <main class="shell auth-main">
        <div class="flash-stack">@include('shared._messages')@include('shared._errors')</div>
        @yield('content')
    </main>
</body>
</html>
