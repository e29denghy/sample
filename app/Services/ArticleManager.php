<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleRedirect;
use App\Models\ArticleRevision;
use App\Models\AuditLog;
use App\Models\OutboxEvent;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Str;

class ArticleManager
{
    public function __construct(
        private readonly MarkdownRenderer $renderer,
        private readonly DatabaseManager $database,
    ) {}

    public function saveDraft(?Article $article, array $data, User $actor): Article
    {
        return $this->database->transaction(function () use ($article, $data, $actor): Article {
            $wasExisting = $article?->exists === true;
            $oldSlug = $article?->slug;

            if (! $article) {
                $article = new Article([
                    'author_id' => $actor->id,
                    'status' => 'draft',
                    'visibility' => 'private',
                ]);
            }

            $article->fill([
                'author_id' => $article->author_id ?: $actor->id,
                'slug' => $data['slug'],
            ]);
            $article->save();

            if ($wasExisting && $oldSlug !== $article->slug) {
                ArticleRedirect::updateOrCreate(
                    ['from_slug' => $oldSlug],
                    ['article_id' => $article->id, 'to_slug' => $article->slug],
                );
            }

            $revision = $this->createRevision($article, $data, $actor);
            $article->forceFill([
                'current_draft_revision_id' => $revision->id,
                'status' => $article->published_revision_id ? 'published' : 'draft',
            ])->save();

            $this->syncRelations($article, $data);
            $this->audit($actor, 'article.draft_saved', $article, ['revision_id' => $revision->id]);

            return $article->fresh(['draftRevision', 'publishedRevision', 'tags', 'projects']);
        });
    }

    public function publish(Article $article, User $actor): Article
    {
        return $this->database->transaction(function () use ($article, $actor): Article {
            $article->loadMissing('draftRevision');

            if (! $article->draftRevision) {
                throw new \DomainException('没有可发布的草稿修订。');
            }

            $now = now();
            $revision = $article->draftRevision;
            $article->forceFill([
                'status' => 'published',
                'visibility' => 'public',
                'published_revision_id' => $revision->id,
                'first_published_at' => $article->first_published_at ?: $now,
                'published_at' => $now,
                'archived_at' => null,
            ])->save();

            OutboxEvent::firstOrCreate(
                ['idempotency_key' => "article:published:{$article->public_id}:{$revision->id}"],
                [
                    'event_type' => 'ArticlePublished',
                    'aggregate_type' => Article::class,
                    'aggregate_id' => $article->id,
                    'aggregate_version' => $revision->version,
                    'payload' => [
                        'article_public_id' => $article->public_id,
                        'article_revision_id' => $revision->id,
                        'content_hash' => $revision->content_hash,
                    ],
                    'status' => 'pending',
                    'available_at' => $now,
                ],
            );

            $this->audit($actor, 'article.published', $article, ['revision_id' => $revision->id]);

            return $article->fresh(['publishedRevision', 'tags', 'projects']);
        });
    }

    public function archive(Article $article, User $actor): Article
    {
        $article->forceFill([
            'status' => 'archived',
            'visibility' => 'private',
            'archived_at' => now(),
        ])->save();
        $this->audit($actor, 'article.archived', $article);

        return $article->fresh();
    }

    private function createRevision(Article $article, array $data, User $actor): ArticleRevision
    {
        $version = ((int) $article->revisions()->max('version')) + 1;
        $markdown = (string) $data['markdown'];

        return $article->revisions()->create([
            'version' => $version,
            'title' => $data['title'],
            'excerpt' => $data['excerpt'] ?? null,
            'markdown' => $markdown,
            'rendered_html' => $this->renderer->render($markdown),
            'content_hash' => hash('sha256', $markdown),
            'seo_title' => $data['seo_title'] ?? null,
            'seo_description' => $data['seo_description'] ?? null,
            'cover_media_id' => $data['cover_media_id'] ?? null,
            'verification_status' => $data['verification_status'] ?? 'review_required',
            'verified_at' => ($data['verification_status'] ?? null) === 'valid' ? now() : null,
            'created_by' => $actor->id,
        ]);
    }

    private function syncRelations(Article $article, array $data): void
    {
        $tagIds = collect($data['tags'] ?? [])
            ->map(fn (string $name): array => ['name' => trim($name), 'slug' => Str::slug($name)])
            ->filter(fn (array $tag): bool => $tag['name'] !== '' && $tag['slug'] !== '')
            ->unique('slug')
            ->map(fn (array $tag) => Tag::firstOrCreate(['slug' => $tag['slug']], ['name' => $tag['name']])->id)
            ->values()
            ->all();

        $article->tags()->sync($tagIds);
    }

    private function audit(User $actor, string $action, Article $article, array $metadata = []): void
    {
        AuditLog::create([
            'user_id' => $actor->id,
            'action' => $action,
            'auditable_type' => Article::class,
            'auditable_id' => $article->id,
            'metadata' => $metadata,
        ]);
    }
}
