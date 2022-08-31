<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Widgets extends Model
{
    public $table = 'widgets';

    public function brand() {
        return $this->belongsTo('App\Models\Registration');
    }
    public function layout() {
        return $this->belongsTo('App\Models\WidgetLayout');
    }
}
