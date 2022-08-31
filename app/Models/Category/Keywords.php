<?php

namespace App\Models\Category;

use Illuminate\Database\Eloquent\Model;


class Keywords extends Model
{
    public $table = 'category_meta_keyword';
    protected $fillable = [
        'id','category_id','name'
    ];
    public $timestamps = false;   
}
