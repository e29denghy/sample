<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ArticleManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AstraReleaseArticlePackageTest extends TestCase
{
    use RefreshDatabase;

    public function test_package_publishes_the_same_revision_to_page_api_and_feed(): void
    {
        $data = json_decode(file_get_contents(database_path('content/gpt-6-astra-release.json')), true, flags: JSON_THROW_ON_ERROR);
        $data['markdown'] = file_get_contents(base_path($data['markdown_file']));
        $this->assertSame('# '.$data['title']."\n\n".$data['markdown'], file_get_contents(base_path('docs/wechat/gpt-6-astra-release.md')));
        $this->assertSame([], $data['projects']);
        $this->assertStringContainsString('本站没有完成自己的 Astra 对照测试', $data['markdown']);
        $this->assertStringContainsString('每任务成本比 Sol 高约 75%', $data['markdown']);
        $this->assertStringNotContainsString('| ---', $data['markdown']);
        $actor = User::factory()->create();
        $manager = app(ArticleManager::class);
        $article = $manager->saveDraft(null, $data, $actor);
        $this->get(route('articles.show', $article->slug))->assertNotFound();
        $article = $manager->publish($article, $actor);
        $this->assertSame(hash('sha256', $data['markdown']), $article->publishedRevision->content_hash);
        $this->assertSame(0, $article->projects()->count());
        $this->get(route('articles.show', $article->slug))->assertOk()->assertSee($data['title'])->assertSee('artificialanalysis.ai/articles/benchmarking-gpt-6-astra', false);
        $this->getJson(route('api.articles.show', ['identifier' => $article->slug]))->assertOk()->assertJsonPath('slug', $data['slug'])->assertJsonPath('revision_version', 1);
        $this->get(route('feeds.rss'))->assertOk()->assertSee($data['title']);
        $this->assertDatabaseCount('articles', 1);
        $this->assertDatabaseCount('article_revisions', 1);
        $this->assertDatabaseCount('outbox_events', 1);
    }
}
