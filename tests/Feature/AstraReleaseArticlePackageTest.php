<?php

namespace Tests\Feature;

use App\Models\MediaAsset;
use App\Models\User;
use App\Services\ArticleManager;
use App\Services\MarkdownRenderer;
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
        $this->assertStringContainsString('我还没有完成 Astra 的对照测试', $data['markdown']);
        $this->assertStringContainsString('每任务成本比 Sol 高约 75%', $data['markdown']);
        $this->assertStringNotContainsString('| ---', $data['markdown']);
        $this->assertFileExists(public_path($data['cover']['path']));
        $this->assertSame($data['cover']['content_hash'], hash_file('sha256', public_path($data['cover']['path'])));
        $data['cover_media_id'] = MediaAsset::create($data['cover'])->id;
        $actor = User::factory()->create();
        $manager = app(ArticleManager::class);
        $article = $manager->saveDraft(null, $data, $actor);
        $this->get(route('articles.show', $article->slug))->assertNotFound();
        $article = $manager->publish($article, $actor);
        $this->assertSame(hash('sha256', $data['markdown']), $article->publishedRevision->content_hash);
        $this->assertSame(0, $article->projects()->count());
        $html = $article->publishedRevision->rendered_html;
        $this->assertStringContainsString('id="三个试用任务"', $html);
        $this->assertStringContainsString('href="#三个试用任务"', rawurldecode($html));
        $this->assertStringContainsString('class="footnote-backref"', $html);
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        $xpath = new \DOMXPath($dom);
        foreach ($xpath->query('//a[starts-with(@href, "#")]') as $link) {
            $id = rawurldecode(substr($link->getAttribute('href'), 1));
            $this->assertSame(1, $xpath->query('//*[@id="'.$id.'"]')->length, 'Missing or duplicate fragment: '.$id);
        }
        $safeHtml = app(MarkdownRenderer::class)->render('<script>alert(1)</script>'."\n\n".'[unsafe](javascript:alert%281%29)');
        $this->assertStringNotContainsString('<script', $safeHtml);
        $this->assertStringNotContainsString('href="javascript:', $safeHtml);
        $this->get(route('articles.show', $article->slug))->assertOk()->assertSee($data['title'])->assertSee('artificialanalysis.ai/articles/benchmarking-gpt-6-astra', false);
        $this->getJson(route('api.articles.show', ['identifier' => $article->slug]))->assertOk()->assertJsonPath('slug', $data['slug'])->assertJsonPath('revision_version', 1)->assertJsonPath('share_image', $data['cover']['source_url']);
        $this->get(route('feeds.rss'))->assertOk()->assertSee($data['title'])->assertSee($data['cover']['source_url'], false);
        $this->assertDatabaseCount('articles', 1);
        $this->assertDatabaseCount('article_revisions', 1);
        $this->assertDatabaseCount('outbox_events', 1);
    }
}
