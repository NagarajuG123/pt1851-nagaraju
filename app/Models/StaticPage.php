<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaticPage extends Model
{    
    const CONTACT_1851_SALE = 1;
    const CONTACT_1851_EDITORIAL = 2;
    const FRANCHISE_LEGAL_PLAYERS = 3;
    const POWER_RANKING = 4;
    
    public $table = 'static_page';

    public function media() {
        return $this->belongsTo('App\Models\Media','media_id');
    }
}
