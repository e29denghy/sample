@extends('layouts.default')
@section('title', '重置密码')

@section('content')
<section class="auth-shell auth-shell-single"><div class="auth-card"><p class="eyebrow">PASSWORD RECOVERY</p><h1>重置密码</h1><p class="auth-card-copy">输入站长账号邮箱，我们会发送密码重置链接。</p>@if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif<form method="POST" action="{{ route('password.email') }}">@csrf<div class="form-group"><label for="email">邮箱地址</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>@error('email')<small class="field-error">{{ $message }}</small>@enderror</div><button type="submit" class="button button-primary button-block">发送重置邮件 <span aria-hidden="true">→</span></button></form><a class="back-link" href="{{ route('login') }}">← 返回登录</a></div></section>
@endsection
