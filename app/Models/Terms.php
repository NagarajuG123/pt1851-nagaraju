<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Terms extends Model
{    
    public $table = 'terms';
    public $timestamps = false;
    protected $fillable = ['term'];

}
