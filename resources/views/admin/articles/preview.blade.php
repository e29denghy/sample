@extends('layouts.admin')
@section('title', '文章预览')
@section('content')<article class="article-page preview-page"><p class="eyebrow">PREVIEW · 未保存</p><h1>{{ $title }}</h1><p class="article-lede">{{ $excerpt }}</p><div class="article-body prose">{!! $html !!}</div></article>@endsection
