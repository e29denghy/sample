<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'status', 'summary', 'problem', 'decisions', 'evidence', 'outcome', 'source_url',
        'cover_media_id', 'is_public', 'is_featured', 'sort_order', 'published_at',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $project): void {
            $project->public_id ??= (string) Str::ulid();
        });
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'project_article');
    }

    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'cover_media_id');
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true)->whereNotNull('published_at');
    }
}
