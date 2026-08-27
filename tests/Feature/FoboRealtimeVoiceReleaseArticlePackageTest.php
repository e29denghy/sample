<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Project;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoboRealtimeVoiceReleaseArticlePackageTest extends TestCase
{
    use RefreshDatabase;

    public function test_fobo_realtime_voice_release_package_is_publishable_and_evidence_grounded(): void
    {
        $manifest = json_decode(
            file_get_contents(database_path('content/fobo-realtime-voice-release.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $markdown = file_get_contents(base_path($manifest['markdown_file']));
        $wechatMarkdown = file_get_contents(base_path('docs/wechat/fobo-realtime-voice-release.md'));
        $articleImageUrls = [
            'https://denghy.cn/images/articles/fobo-realtime-voice-release/00-realtime-access.jpg',
            'https://denghy.cn/images/articles/fobo-realtime-voice-release/01-original-english-corner.jpg',
        ];

        $this->assertSame('fobo-realtime-voice-from-harness-to-production', $manifest['slug']);
        $this->assertSame(['fobo-english-corner'], $manifest['projects']);
        $this->assertStringContainsString('https://voice.denghy.cn/talk', $markdown);
        $this->assertStringContainsString('24 个 PHP 测试、141 个断言', $markdown);
        $this->assertStringContainsString('30 个前端单元测试', $markdown);
        $this->assertStringContainsString('session.created', $markdown);
        $this->assertStringContainsString('ab055fc', $markdown);
        $this->assertStringContainsString('不公开发布邀请码', $markdown);
        $this->assertDoesNotMatchRegularExpression('/[A-Z0-9]{4}(?:-[A-Z0-9]{4}){2}/', $markdown);
        $this->assertDoesNotMatchRegularExpression('/[A-Z0-9]{4}(?:-[A-Z0-9]{4}){2}/', $wechatMarkdown);
        $this->assertStringNotContainsString('*', $markdown);
        $this->assertStringNotContainsString('*', $wechatMarkdown);
        $this->assertStringNotContainsString('摘要：', $markdown);
        $this->assertStringNotContainsString('摘要：', $wechatMarkdown);

        preg_match_all(
            '#https://denghy\.cn/images/articles/fobo-realtime-voice-release/[a-z0-9-]+\.jpg#',
            $markdown,
            $markdownImageUrls,
        );
        preg_match_all(
            '#https://denghy\.cn/images/articles/fobo-realtime-voice-release/[a-z0-9-]+\.jpg#',
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

        $article = Article::where('slug', $manifest['slug'])
            ->with(['publishedRevision', 'projects'])
            ->firstOrFail();

        $this->assertSame('published', $article->status);
        $this->assertSame('valid', $article->publishedRevision->verification_status);
        $this->assertSame(hash('sha256', $markdown), $article->publishedRevision->content_hash);
        $this->assertSame(['fobo-english-corner'], $article->projects->pluck('slug')->all());
        $this->assertSame('released', Project::where('slug', 'fobo-english-corner')->value('status'));

        $this->get(route('articles.show', $article->slug))
            ->assertOk()
            ->assertSee($manifest['title'])
            ->assertSee('session.created');

        $this->getJson(route('api.articles.show', ['identifier' => $article->slug]))
            ->assertOk()
            ->assertJsonPath('revision_version', 1)
            ->assertJsonPath('slug', $manifest['slug']);

        $this->get(route('feeds.rss'))
            ->assertOk()
            ->assertSee('福宝实时英语对话');
    }
}
