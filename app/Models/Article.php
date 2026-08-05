<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'author_id',
        'slug',
        'status',
        'visibility',
        'current_draft_revision_id',
        'published_revision_id',
        'first_published_at',
        'published_at',
        'archived_at',
    ];

    protected $casts = [
        'first_published_at' => 'datetime',
        'published_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $article): void {
            $article->public_id ??= (string) Str::ulid();
        });
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ArticleRevision::class);
    }

    public function draftRevision(): BelongsTo
    {
        return $this->belongsTo(ArticleRevision::class, 'current_draft_revision_id');
    }

    public function publishedRevision(): BelongsTo
    {
        return $this->belongsTo(ArticleRevision::class, 'published_revision_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_article');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where('visibility', 'public')
            ->whereNotNull('published_revision_id')
            ->whereNotNull('published_at');
    }

    public function isPublic(): bool
    {
        return $this->status === 'published' && $this->visibility === 'public' && $this->published_revision_id !== null;
    }

    public function canonicalUrl(): string
    {
        return route('articles.show', $this->slug);
    }
}
