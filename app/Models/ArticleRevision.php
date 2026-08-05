<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleRevision extends Model
{
    protected $fillable = [
        'article_id',
        'version',
        'title',
        'excerpt',
        'markdown',
        'rendered_html',
        'content_hash',
        'seo_title',
        'seo_description',
        'cover_media_id',
        'verification_status',
        'verified_at',
        'created_by',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $revision): void {
            $revision->content_hash ??= hash('sha256', $revision->markdown);
        });
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'cover_media_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function canonicalUrl(): string
    {
        return route('articles.show', $this->article->slug);
    }
}
