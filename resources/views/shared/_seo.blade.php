@php($seoTitle = $seoTitle ?? ($title ?? 'denghy / 工程现场'))
@php($seoDescription = $seoDescription ?? ($description ?? 'Production notes on AI, content systems and Laravel.'))
<meta property="og:type" content="{{ $ogType ?? 'website' }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $canonical ?? url()->current() }}">
<meta property="og:site_name" content="denghy / 工程现场">
@if(!empty($shareImage)) <meta property="og:image" content="{{ $shareImage }}"> @endif
