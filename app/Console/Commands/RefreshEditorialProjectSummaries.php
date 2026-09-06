<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshEditorialProjectSummaries extends Command
{
    protected $signature = 'content:refresh-project-summaries {--apply : Apply the September 2026 editorial summaries}';

    protected $description = 'Preview or apply the three reviewed project summaries without changing other content';

    public function handle(): int
    {
        $changes = json_decode(file_get_contents(database_path('content/editorial-project-summaries.json')), true, flags: JSON_THROW_ON_ERROR);

        try {
            $result = DB::transaction(function () use ($changes): array {
                $pending = [];
                $unchanged = 0;

                foreach ($changes as $change) {
                    $project = Project::whereKey($change['id'])->where('slug', $change['slug'])->first();

                    if (! $project) {
                        throw new \RuntimeException("Project identity mismatch: {$change['slug']}");
                    }

                    if ($project->summary === $change['after']) {
                        $unchanged++;

                        continue;
                    }

                    if ($project->summary !== $change['before']) {
                        throw new \RuntimeException("Summary changed since review: {$change['slug']}");
                    }

                    $pending[] = $change;
                }

                if ($this->option('apply')) {
                    foreach ($pending as $change) {
                        $updated = Project::whereKey($change['id'])
                            ->where('slug', $change['slug'])
                            ->where('summary', $change['before'])
                            ->update(['summary' => $change['after']]);

                        if ($updated !== 1) {
                            throw new \RuntimeException("Concurrent summary edit: {$change['slug']}");
                        }

                        AuditLog::create([
                            'action' => 'project.summary_refreshed',
                            'auditable_type' => Project::class,
                            'auditable_id' => $change['id'],
                            'metadata' => ['release' => 'editorial-2026-09-06', 'before' => $change['before'], 'after' => $change['after']],
                        ]);
                    }
                }

                return ['mode' => $this->option('apply') ? 'applied' : 'dry_run', 'changes' => $pending, 'unchanged' => $unchanged];
            });
        } catch (\RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
    }
}
