<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PinnedArticle extends Model
{
   const PINNED_BY_USER = 'user';
   const PINNED_BY_AUTHOR = 'author';

    public $table = 'pinned_articles';

    public function scopePinnedByAuthor($query)
    {
        return $query->where(['pinned_by' => self::PINNED_BY_AUTHOR]);
    }
    public function scopePinnedByUser($query)
    {
        return $query->where(['pinned_by' => self::PINNED_BY_USER]);
    }
}