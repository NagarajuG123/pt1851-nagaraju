<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PowerRanking extends Model
{    
    public $table = 'power_ranking';

    public function brand() {
        return $this->belongsTo('App\Models\Registration','brand_id');
    }

}
