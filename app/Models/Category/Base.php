<?php

namespace App\Models\Category;

use Illuminate\Database\Eloquent\Model;

class Base extends Model
{
    public $table = 'category';
 
    protected $guarded = [];
    protected $fillable = ['id', 'name', 'slug', 'description', 'image_path', 'sort_id', 'seo_title',
                            'seo_description', 'created_at', 'updated_at', 'created_by', 'updated_by', 'deleted_at'];
    public function keywords() {
        return $this->hasMany('App\Models\Category\Keywords','category_id');
    }
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }
}
