@extends('layouts.default')
@section('title', '登录')

@section('content')
<section class="auth-shell">
    <div class="auth-note"><p class="eyebrow">CONTENT CONTROL ROOM</p><h1>内容发布后台</h1><p>编辑文章与项目，预览修订，并在确认后将同一份正文发布到网站、Feed 与只读 API。</p><ul><li>管理员专用入口</li><li>发布前保留预览步骤</li><li>公开版本可追溯</li></ul></div>
    <div class="auth-card"><p class="eyebrow">ADMIN SIGN IN</p><h2>登录</h2><p class="auth-card-copy">使用预置的站长账号继续。</p><form method="POST" action="{{ route('login') }}">@csrf<div class="form-group"><label for="email">邮箱</label><input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus></div><div class="form-group"><div class="label-row"><label for="password">密码</label><a href="{{ route('password.request') }}">忘记密码？</a></div><input id="password" type="password" name="password" autocomplete="current-password" required></div><label class="check-label"><input type="checkbox" name="remember"> <span>记住我</span></label><button type="submit" class="button button-primary button-block">进入后台 <span aria-hidden="true">→</span></button></form><p class="auth-footnote">账号由管理员预置，暂不开放公开注册。</p></div>
</section>
@stop
