<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public $table = 'categories';
    
    const CATEGORY_NA = 8;

    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }
    public function  getMedia($image)
    {
        return [
            'url' => env('AWS_S3_URL') ."/category/" . $image ?? null,
            'path' =>  'category/'.$image
        ];
    }
}
