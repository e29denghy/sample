<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Project;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearnHarnessEngineeringArticlePackageTest extends TestCase
{
    use RefreshDatabase;

    public function test_open_source_project_and_article_package_are_publishable_and_linked(): void
    {
        $manifest = json_decode(
            file_get_contents(database_path('content/learn-harness-engineering-guide.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $markdown = file_get_contents(base_path($manifest['markdown_file']));
        $wechatMarkdown = file_get_contents(base_path('docs/wechat/learn-harness-engineering.md'));
        $articleImageUrls = [
            'https://denghy.cn/images/articles/learn-harness-engineering/00-cover.jpg',
            'https://denghy.cn/images/articles/learn-harness-engineering/01-five-subsystems.jpg',
            'https://denghy.cn/images/articles/learn-harness-engineering/02-learning-path.jpg',
        ];

        $this->assertStringContainsString('14 讲、8 个递进实践、15 种语言版本', $markdown);
        $this->assertStringContainsString('Project 08', $markdown);
        $this->assertStringContainsString('MIT License', $markdown);
        $this->assertStringContainsString('截至 2026-08-20', $markdown);
        $this->assertStringStartsWith('# 模型已经会写代码，为什么项目还是会翻车？', $wechatMarkdown);
        $this->assertStringContainsString('https://github.com/walkinglabs/learn-harness-engineering', $wechatMarkdown);

        preg_match_all(
            '#https://denghy\\.cn/images/articles/learn-harness-engineering/[a-z0-9-]+\\.jpg#',
            $markdown,
            $markdownImageUrls,
        );
        preg_match_all(
            '#https://denghy\\.cn/images/articles/learn-harness-engineering/[a-z0-9-]+\\.jpg#',
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

        $project = Project::where('slug', 'learn-harness-engineering')->firstOrFail();
        $article = Article::where('slug', $manifest['slug'])->with('publishedRevision')->firstOrFail();

        $this->assertTrue($project->articles()->whereKey($article->id)->exists());
        $this->assertSame('published', $article->status);
        $this->assertSame('valid', $article->publishedRevision->verification_status);
        $this->assertStringContainsString('Harness 不会让模型突然变得更聪明', $article->publishedRevision->rendered_html);

        $this->get(route('articles.show', $article->slug))
            ->assertOk()
            ->assertSee($manifest['title'])
            ->assertSee('Learn Harness Engineering');

        $this->get(route('feeds.rss'))
            ->assertOk()
            ->assertSee('Harness Engineering');
    }
}
