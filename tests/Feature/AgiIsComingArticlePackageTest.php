<?php

namespace Tests\Feature;

use App\Models\Article;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgiIsComingArticlePackageTest extends TestCase
{
    use RefreshDatabase;

    public function test_agi_article_package_is_publishable_and_synchronized(): void
    {
        $manifest = json_decode(
            file_get_contents(database_path('content/agi-is-coming.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $markdown = file_get_contents(base_path($manifest['markdown_file']));
        $wechatMarkdown = file_get_contents(base_path('docs/wechat/agi-is-coming.md'));
        $coverUrl = 'https://denghy.cn/images/articles/agi-is-coming/00-cover.png';

        $this->assertSame('AGI 要来了？先别急', $manifest['title']);
        $this->assertStringStartsWith('# AGI 要来了？先别急', $wechatMarkdown);
        $this->assertStringContainsString($coverUrl, $markdown);
        $this->assertStringContainsString($coverUrl, $wechatMarkdown);
        $this->assertStringContainsString('没有公布 GPT-6', $markdown);
        $this->assertStringContainsString('Qwen4 架构的早期预览', $markdown);
        $this->assertStringContainsString('不是第二天早晨', $markdown);
        $this->assertStringNotContainsString('AGI 已经到来', $markdown);
        $this->assertFileExists(public_path('images/articles/agi-is-coming/00-cover.png'));
        $this->assertSame($markdown, substr($wechatMarkdown, strpos($wechatMarkdown, "\n") + 2));

        $this->seed(ContentSeeder::class);

        $article = Article::where('slug', $manifest['slug'])->with('publishedRevision')->firstOrFail();

        $this->assertSame('published', $article->status);
        $this->assertSame('valid', $article->publishedRevision->verification_status);
        $this->assertStringContainsString('AGI 的前夜', $article->publishedRevision->rendered_html);

        $this->get(route('articles.show', $article->slug))
            ->assertOk()
            ->assertSee($manifest['title'])
            ->assertSee('Astra');

        $this->getJson(route('api.articles.show', ['identifier' => $article->slug]))
            ->assertOk()
            ->assertJsonPath('slug', $manifest['slug'])
            ->assertJsonPath('revision_version', 1);

        $this->get(route('feeds.rss'))
            ->assertOk()
            ->assertSee('AGI 要来了？先别急');
    }
}
