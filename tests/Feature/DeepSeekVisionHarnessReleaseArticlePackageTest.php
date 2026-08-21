<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use App\Services\ArticleManager;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeepSeekVisionHarnessReleaseArticlePackageTest extends TestCase
{
    use RefreshDatabase;

    public function test_multimodal_release_article_package_is_publishable_and_source_grounded(): void
    {
        $manifest = json_decode(
            file_get_contents(database_path('content/deepseek-v4-flash-vision-harness-rc8-rc1.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $markdown = file_get_contents(base_path($manifest['markdown_file']));
        $wechatMarkdown = file_get_contents(base_path('docs/wechat/deepseek-v4-flash-vision-harness-rc8-rc1.md'));
        $articleImageUrls = [
            'https://denghy.cn/images/articles/deepseek-v4-flash-vision-harness/00-cover.jpg',
            'https://denghy.cn/images/articles/deepseek-v4-flash-vision-harness/01-release-handoff.jpg',
            'https://denghy.cn/images/articles/deepseek-v4-flash-vision-harness/02-verification-loop.jpg',
        ];

        $this->assertStringContainsString('DeepSeek-V4-Flash-Vision-Exp', $markdown);
        $this->assertStringContainsString('v0.1.0-rc.8', $markdown);
        $this->assertStringContainsString('v0.1.1-rc.1', $markdown);
        $this->assertStringContainsString('v0.1.1-rc.2', $markdown);
        $this->assertStringContainsString('优先通过 Files API 上传图像', $markdown);
        $this->assertStringContainsString('自动缩放并转换为合适格式', $markdown);
        $this->assertStringContainsString('https://github.com/deepseek-ai/deepseek-harness/releases/tag/dsh-v0.1.1-rc.2', $markdown);
        $this->assertStringContainsString('这是 DeepSeek 官方披露的测试结论，不是本文完成的独立复测', $markdown);
        $this->assertStringContainsString('SQLite 后端的数据结构不兼容', $markdown);
        $this->assertStringContainsString('Bubblewrap 沙箱', $markdown);
        $this->assertStringContainsString('资料核对时间：2026-08-21', $markdown);
        $this->assertStringContainsString('https://api-docs.deepseek.com/zh-cn/guides/vision', $markdown);
        $this->assertStringContainsString('https://mp.weixin.qq.com/s/xw-hxtHMcxxSGqbSbp98RQ', $markdown);
        $this->assertStringContainsString('https://mp.weixin.qq.com/s/UGMfvPMwBIB4oFYZZejekA', $markdown);
        $this->assertStringNotContainsString('*', $markdown);
        $this->assertStringNotContainsString('*', $wechatMarkdown);
        $this->assertStringNotContainsString('摘要：', $markdown);
        $this->assertStringNotContainsString('摘要：', $wechatMarkdown);

        preg_match_all(
            '#https://denghy\\.cn/images/articles/deepseek-v4-flash-vision-harness/[a-z0-9-]+\\.jpg#',
            $markdown,
            $markdownImageUrls,
        );
        preg_match_all(
            '#https://denghy\\.cn/images/articles/deepseek-v4-flash-vision-harness/[a-z0-9-]+\\.jpg#',
            $wechatMarkdown,
            $wechatImageUrls,
        );

        $this->assertSame($articleImageUrls, array_values(array_unique($markdownImageUrls[0])));
        $this->assertSame($articleImageUrls, array_values(array_unique($wechatImageUrls[0])));

        foreach ($articleImageUrls as $imageUrl) {
            $imagePath = parse_url($imageUrl, PHP_URL_PATH);

            $this->assertIsString($imagePath);
            $this->assertFileExists(public_path(ltrim($imagePath, '/')));
        }

        $this->seed(ContentSeeder::class);

        $article = Article::where('slug', $manifest['slug'])->with('publishedRevision')->firstOrFail();

        $this->assertSame('published', $article->status);
        $this->assertSame('valid', $article->publishedRevision->verification_status);
        $this->assertStringContainsString('deepseek-v4-flash-vision-exp', $article->publishedRevision->rendered_html);

        $this->get(route('articles.show', $article->slug))
            ->assertOk()
            ->assertSee($manifest['title'])
            ->assertSee('DeepSeek-V4-Flash-Vision-Exp');

        $this->getJson(route('api.articles.show', ['identifier' => $article->slug]))
            ->assertOk()
            ->assertJsonPath('revision_version', 1)
            ->assertJsonPath('slug', $manifest['slug']);

        $this->get(route('feeds.rss'))
            ->assertOk()
            ->assertSee('DeepSeek V4 Vision');
    }

    public function test_existing_multimodal_article_can_publish_the_rc2_revision(): void
    {
        $this->seed(ContentSeeder::class);

        $manifest = json_decode(
            file_get_contents(database_path('content/deepseek-v4-flash-vision-harness-rc8-rc1.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $data = $manifest;
        unset($data['markdown_file']);
        $data['markdown'] = file_get_contents(base_path($manifest['markdown_file']));

        $article = Article::where('slug', $manifest['slug'])->firstOrFail();
        $admin = User::factory()->create([
            'is_admin' => true,
            'activated' => true,
        ]);
        $manager = app(ArticleManager::class);

        $draft = $manager->saveDraft($article, $data, $admin);
        $manager->publish($draft, $admin);

        $article->refresh()->load('publishedRevision');

        $this->assertSame(2, $article->publishedRevision->version);
        $this->assertSame($manifest['title'], $article->publishedRevision->title);
        $this->assertSame(hash('sha256', $data['markdown']), $article->publishedRevision->content_hash);
        $this->assertDatabaseHas('outbox_events', [
            'aggregate_id' => $article->id,
            'aggregate_version' => 2,
            'event_type' => 'ArticlePublished',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_id' => $article->id,
            'action' => 'article.published',
        ]);
    }
}
