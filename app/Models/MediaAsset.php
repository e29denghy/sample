<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MediaAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'disk', 'path', 'original_name', 'mime_type', 'size', 'width', 'height',
        'content_hash', 'alt_text', 'copyright', 'source_url', 'uploaded_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $asset): void {
            $asset->public_id ??= (string) Str::ulid();
        });
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function url(): string
    {
        return asset('storage/'.$this->path);
    }
}
