@extends('layouts.site')
@section('title', $project->name.' · denghy / 工程现场')
@section('content')
<article class="shell project-page"><header class="page-intro"><p class="eyebrow">PROJECT FILE · {{ $project->status }}</p><h1>{{ $project->name }}</h1><p>{{ $project->summary }}</p></header><div class="project-sections"><section><h2>问题与约束</h2><p>{!! nl2br(e($project->problem)) !!}</p></section><section><h2>方案与决策</h2><p>{!! nl2br(e($project->decisions)) !!}</p></section><section><h2>证据</h2><p>{!! nl2br(e($project->evidence)) !!}</p></section><section><h2>结果</h2><p>{!! nl2br(e($project->outcome)) !!}</p></section></div>@if($project->articles->isNotEmpty())<section class="section-block"><p class="eyebrow">RELATED NOTES</p><div class="article-list">@foreach($project->articles as $article)@include('site.articles._card', ['article' => $article])@endforeach</div></section>@endif</article>
@endsection
