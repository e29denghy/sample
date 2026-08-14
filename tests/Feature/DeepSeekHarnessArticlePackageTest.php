<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ArticleManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeepSeekHarnessArticlePackageTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_package_is_complete_and_publishable(): void
    {
        $manifest = json_decode(
            file_get_contents(database_path('content/deepseek-harness-webui-desktop-app.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $markdown = file_get_contents(base_path($manifest['markdown_file']));

        preg_match_all(
            '#https://denghy\.cn(?P<path>/images/articles/deepseek-harness-webui-desktop-app/[^)]+)#',
            $markdown,
            $images,
        );

        $this->assertCount(6, array_unique($images['path']));
        foreach (array_unique($images['path']) as $imagePath) {
            $this->assertFileExists(public_path(ltrim($imagePath, '/')));
        }

        $data = $manifest;
        unset($data['markdown_file']);
        $data['markdown'] = $markdown;

        $admin = User::factory()->create([
            'is_admin' => true,
            'activated' => true,
        ]);
        $manager = app(ArticleManager::class);
        $article = $manager->saveDraft(null, $data, $admin);
        $manager->publish($article, $admin);

        $article->refresh()->load('publishedRevision');

        $this->assertSame('published', $article->status);
        $this->assertSame(1, $article->publishedRevision->version);
        $this->assertStringContainsString('npx @deepseek-ai/dsh web', $article->publishedRevision->rendered_html);

        $this->get(route('articles.show', $article->slug))
            ->assertOk()
            ->assertSee($manifest['title'])
            ->assertSee('https://denghy.cn/images/articles/deepseek-harness-webui-desktop-app/00-cover.png', false);

        $this->getJson(route('api.articles.show', ['identifier' => $article->slug]))
            ->assertOk()
            ->assertJsonPath('slug', $manifest['slug'])
            ->assertJsonPath('revision_version', 1);

        $this->get(route('feeds.rss'))
            ->assertOk()
            ->assertSee('DeepSeek Harness');
    }
}
