<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicPageContents extends Model
{
    public $table = 'dynamic_page_contents';

    const FEATURED = 1;
}
