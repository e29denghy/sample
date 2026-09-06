<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentPublishingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_only_show_published_revisions(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.articles.store'), $this->articleData('第一版正文'));
        $response->assertStatus(302);
        $response->assertSessionDoesntHaveErrors();
        $article = Article::firstOrFail();

        $response->assertRedirect(route('admin.articles.edit', $article));
        $this->get(route('articles.index'))->assertOk()->assertDontSee('第一版正文');

        $this->actingAs($admin)->post(route('admin.articles.publish', $article))->assertRedirect();
        $this->get(route('articles.show', $article->slug))->assertOk()->assertSee('第一版正文');
        $this->assertDatabaseHas('outbox_events', [
            'event_type' => 'ArticlePublished',
            'aggregate_id' => $article->id,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->patch(route('admin.articles.update', $article), $this->articleData('第二版草稿'));
        $this->get(route('articles.show', $article->slug))->assertOk()->assertSee('第一版正文')->assertDontSee('第二版草稿');

        $renamed = $this->articleData('第二版正文');
        $renamed['slug'] = 'renamed-article';
        $this->actingAs($admin)->patch(route('admin.articles.update', $article), $renamed);
        $this->actingAs($admin)->post(route('admin.articles.publish', $article));
        $this->get('/articles/test-article')->assertRedirect('/articles/renamed-article')->assertStatus(301);
    }

    public function test_admin_gate_and_public_registration_are_closed(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get('/signup')->assertNotFound();
        $this->get('/users')->assertNotFound();
        $this->actingAs(User::factory()->create())->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_public_layout_preserves_original_brand_and_icp_record(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('程序员的个人修养')
            ->assertSee('粤ICP备18024712号')
            ->assertSee('https://beian.miit.gov.cn/')
            ->assertSee('把复杂流程')
            ->assertSee('做成可验证的系统')
            ->assertDontSee('SYSTEM TRACE')
            ->assertDontSee('已验证');
    }

    public function test_shared_visual_language_reaches_public_and_auth_pages(): void
    {
        $this->get(route('articles.index'))
            ->assertOk()
            ->assertSee('ARTICLES / 工程文章')
            ->assertSee('footer-grid', false);

        $this->get(route('projects.index'))
            ->assertOk()
            ->assertSee('PROJECTS / 项目档案');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('CONTENT CONTROL ROOM')
            ->assertSee('管理员专用入口');
    }

    public function test_feed_and_api_use_the_same_published_revision(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $response = $this->actingAs($admin)->post(route('admin.articles.store'), $this->articleData('Feed 正文'));
        $response->assertStatus(302);
        $response->assertSessionDoesntHaveErrors();
        $article = Article::firstOrFail();
        $this->actingAs($admin)->post(route('admin.articles.publish', $article));
        $article->refresh()->load('publishedRevision');

        $api = $this->getJson(route('api.articles.show', ['identifier' => $article->public_id]));
        $api->assertOk()->assertJsonPath('content_hash', $article->publishedRevision->content_hash);
        $etag = $api->headers->get('ETag');
        $this->withHeaders(['If-None-Match' => $etag])->getJson(route('api.articles.show', ['identifier' => $article->public_id]))->assertStatus(304);
        $this->get(route('feeds.rss'))->assertOk()->assertSee('Feed 正文')->assertSee('urn:denghy:article:'.$article->public_id);
        $this->get(route('feeds.atom'))->assertOk()->assertSee('Feed 正文');
    }

    private function articleData(string $body): array
    {
        return [
            'title' => '一篇测试文章',
            'slug' => 'test-article',
            'excerpt' => '测试摘要',
            'markdown' => $body,
            'seo_title' => '测试文章',
            'seo_description' => '测试描述',
            'verification_status' => 'valid',
            'tags' => ['Laravel, RSS'],
        ];
    }
}
