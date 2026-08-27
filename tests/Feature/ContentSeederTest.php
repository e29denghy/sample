<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Project;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_projects_and_articles_are_public_and_linked(): void
    {
        $this->seed(ContentSeeder::class);

        $this->assertDatabaseCount('projects', 6);
        $this->assertDatabaseCount('articles', 8);
        $this->assertDatabaseCount('article_revisions', 8);
        $this->assertDatabaseHas('project_article', [
            'project_id' => Project::where('slug', 'kaiwu')->value('id'),
            'article_id' => Article::where('slug', 'kaiwu-agent-workbench-events-quests-approval')->value('id'),
        ]);
        $this->assertDatabaseHas('projects', [
            'slug' => 'learn-harness-engineering',
            'source_url' => 'https://github.com/walkinglabs/learn-harness-engineering',
            'is_public' => true,
        ]);
        $this->assertDatabaseHas('projects', [
            'slug' => 'fobo-english-corner',
            'status' => 'released',
            'is_public' => true,
        ]);
        $this->assertDatabaseHas('project_article', [
            'project_id' => Project::where('slug', 'learn-harness-engineering')->value('id'),
            'article_id' => Article::where('slug', 'learn-harness-engineering-open-source-course-guide')->value('id'),
        ]);

        $this->get(route('articles.index'))->assertOk()->assertSee('KAIWU：把跨 Agent 工作拆成事件、任务与审批');
        $this->getJson(route('api.articles.index'))->assertOk()->assertJsonCount(8, 'data');
        $this->get(route('projects.show', 'learn-harness-engineering'))
            ->assertOk()
            ->assertSee('查看开源仓库')
            ->assertSee('模型已经会写代码，为什么项目还是会翻车？');
        $this->getJson(route('api.projects.show', 'learn-harness-engineering'))
            ->assertOk()
            ->assertJsonPath('source_url', 'https://github.com/walkinglabs/learn-harness-engineering');
    }
}
