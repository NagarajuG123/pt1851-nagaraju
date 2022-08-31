<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandStaticContent extends Model
{    
    public $table = 'brand_static_content';
    const TYPE_EXECUTIVE = 'Executive Q&A';
    const TYPE_AVAILABLE_MARKET = 'Available Markets';
    const TYPE_WHY_I_BOUGHT = 'Why I Bought';

    public function scopeStatus($query)
    {
        return $query->where(['visible' => true]);
    }
}
