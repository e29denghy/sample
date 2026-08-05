<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleRedirect extends Model
{
    protected $fillable = ['from_slug', 'article_id', 'to_slug'];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
