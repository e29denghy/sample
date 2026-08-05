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

        $this->assertDatabaseCount('projects', 5);
        $this->assertDatabaseCount('articles', 5);
        $this->assertDatabaseCount('article_revisions', 5);
        $this->assertDatabaseHas('project_article', [
            'project_id' => Project::where('slug', 'kaiwu')->value('id'),
            'article_id' => Article::where('slug', 'kaiwu-agent-workbench-events-quests-approval')->value('id'),
        ]);

        $this->get(route('articles.index'))->assertOk()->assertSee('KAIWU：把跨 Agent 工作拆成事件、任务与审批');
        $this->getJson(route('api.articles.index'))->assertOk()->assertJsonCount(5, 'data');
    }
}
