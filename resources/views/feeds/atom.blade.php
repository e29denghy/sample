<feed xmlns="http://www.w3.org/2005/Atom">
    <title>程序员的个人修养 / 工程现场</title>
    <id>{{ url('/') }}</id>
    <link href="{{ url('/') }}" />
    <link rel="self" href="{{ route('feeds.atom') }}" />
    <updated>{{ ($articles->max('published_at') ?: now())->toAtomString() }}</updated>
    <subtitle>Production notes on AI, content systems and Laravel.</subtitle>
    @foreach($articles as $article)
        @php($revision = $article->publishedRevision)
        <entry>
            <title>{{ $revision->title }}</title>
            <id>urn:denghy:article:{{ $article->public_id }}</id>
            <link href="{{ $article->canonicalUrl() }}" />
            <published>{{ $article->first_published_at->toAtomString() }}</published>
            <updated>{{ $article->published_at->toAtomString() }}</updated>
            <summary type="html"><![CDATA[{{ $revision->excerpt }}]]></summary>
            <content type="html"><![CDATA[{!! str_replace(']]>', ']]]]><![CDATA[>', \App\Http\Controllers\FeedController::absoluteHtml($revision->rendered_html)) !!}]]></content>
        </entry>
    @endforeach
</feed>
