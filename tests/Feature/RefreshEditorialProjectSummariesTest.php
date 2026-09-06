<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefreshEditorialProjectSummariesTest extends TestCase
{
    use RefreshDatabase;

    public function test_refresh_is_previewed_scoped_and_idempotent(): void
    {
        $changes = $this->createBaseline();
        $before = Project::orderBy('id')->get()->toArray();
        $this->artisan('content:refresh-project-summaries')->assertSuccessful();
        $this->assertSame($before, Project::orderBy('id')->get()->toArray());
        $this->assertDatabaseCount('audit_logs', 0);

        $this->artisan('content:refresh-project-summaries', ['--apply' => true])->assertSuccessful();

        foreach ($changes as $change) {
            $this->assertDatabaseHas('projects', ['id' => $change['id'], 'summary' => $change['after']]);
            $this->getJson(route('api.projects.show', $change['slug']))->assertOk()->assertJsonPath('summary', $change['after']);
        }

        $after = Project::orderBy('id')->get()->toArray();
        foreach ($before as $index => $row) {
            unset($row['summary'], $row['updated_at']);
            $actual = $after[$index];
            unset($actual['summary'], $actual['updated_at']);
            $this->assertSame($row, $actual);
        }
        $this->assertSame($before[3], $after[3]);
        $this->assertDatabaseCount('audit_logs', 3);
        $this->assertDatabaseCount('articles', 0);
        $this->assertDatabaseCount('outbox_events', 0);
        $this->artisan('content:refresh-project-summaries', ['--apply' => true])->assertSuccessful();
        $this->assertSame($after, Project::orderBy('id')->get()->toArray());
        $this->assertDatabaseCount('audit_logs', 3);
    }

    public function test_conflicting_summary_aborts_the_whole_batch(): void
    {
        $this->createBaseline();
        Project::whereKey(3)->update(['summary' => 'A newer editorial change']);
        $before = Project::orderBy('id')->get()->toArray();
        $this->artisan('content:refresh-project-summaries', ['--apply' => true])->assertFailed();
        $this->assertSame($before, Project::orderBy('id')->get()->toArray());
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_an_id_reused_for_another_project_aborts_the_batch(): void
    {
        $this->createBaseline();
        Project::whereKey(3)->update(['slug' => 'a-different-project']);
        $before = Project::orderBy('id')->get()->toArray();
        $this->artisan('content:refresh-project-summaries', ['--apply' => true])->assertFailed();
        $this->assertSame($before, Project::orderBy('id')->get()->toArray());
        $this->assertDatabaseCount('audit_logs', 0);
    }

    private function createBaseline(): array
    {
        $changes = json_decode(file_get_contents(database_path('content/editorial-project-summaries.json')), true, flags: JSON_THROW_ON_ERROR);
        foreach ($changes as $change) {
            $project = Project::create(['name' => $change['slug'], 'slug' => $change['slug'], 'summary' => $change['before'], 'is_public' => true, 'is_featured' => true, 'published_at' => now()]);
            $this->assertSame($change['id'], $project->id);
        }
        Project::create(['name' => 'Other project', 'slug' => 'other-project', 'summary' => 'Keep this description']);

        return $changes;
    }
}
