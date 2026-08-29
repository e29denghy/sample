<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Project;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KaiwuDeepSeekHarnessArticlePackageTest extends TestCase
{
    use RefreshDatabase;

    public function test_kaiwu_deepseek_harness_article_package_is_publishable_and_linked(): void
    {
        $manifest = json_decode(
            file_get_contents(database_path('content/kaiwu-deepseek-harness-adapter.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $markdown = file_get_contents(base_path($manifest['markdown_file']));
        $wechatMarkdown = file_get_contents(base_path('docs/wechat/kaiwu-deepseek-harness-adapter.md'));
        $articleImageUrls = [
            'https://denghy.cn/images/articles/kaiwu-deepseek-harness-adapter/00-cover.png',
            'https://denghy.cn/images/articles/kaiwu-deepseek-harness-adapter/01-kaiwu-home.jpg',
            'https://denghy.cn/images/articles/kaiwu-deepseek-harness-adapter/02-protocol-and-dsh.jpg',
        ];

        $this->assertStringContainsString('0.1.1-rc.2', $markdown);
        $this->assertStringContainsString('RC.2 PINNED', $markdown);
        $this->assertStringContainsString('8458504c7b34c5e85f00fe79fd78205fbe9391dc', $markdown);
        $this->assertStringContainsString('requires_write=false', $markdown);
        $this->assertStringContainsString('没有调用真实模型', $markdown);
        $this->assertStringNotContainsString('固定 rc6', $markdown);
        $this->assertStringStartsWith('# 开物如何适配 DeepSeek Harness', $wechatMarkdown);

        preg_match_all(
            '#https://denghy\.cn/images/articles/kaiwu-deepseek-harness-adapter/[a-z0-9.-]+#',
            $markdown,
            $markdownImageUrls,
        );
        preg_match_all(
            '#https://denghy\.cn/images/articles/kaiwu-deepseek-harness-adapter/[a-z0-9.-]+#',
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

        $project = Project::where('slug', 'kaiwu')->firstOrFail();
        $article = Article::where('slug', $manifest['slug'])->with('publishedRevision')->firstOrFail();

        $this->assertTrue($project->articles()->whereKey($article->id)->exists());
        $this->assertSame('published', $article->status);
        $this->assertSame('valid', $article->publishedRevision->verification_status);
        $this->assertStringContainsString('进程边界', $article->publishedRevision->rendered_html);

        $this->get(route('articles.show', $article->slug))
            ->assertOk()
            ->assertSee($manifest['title'])
            ->assertSee('0.1.1-rc.2');

        $this->getJson(route('api.articles.show', ['identifier' => $article->slug]))
            ->assertOk()
            ->assertJsonPath('slug', $manifest['slug'])
            ->assertJsonPath('revision_version', 1);

        $this->get(route('feeds.rss'))
            ->assertOk()
            ->assertSee('DeepSeek Harness');
    }
}
