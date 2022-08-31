<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Issue extends Model
{
    
    const ENDPOINT_BANNER = 'banner';
    const ENDPOINT_IMAGES = 'images';
    const ENDPOINT_DETAILS = 'details';

    public $table = 'issue';

    public $fillable = [
       'monthYear'
    ];
   
    public function coverUrl() {
        $date = Carbon::parse($this->monthYear);
        $month = $date->month;
        $year = $date->year;
        $rowCount = Issue::whereMonth('monthYear', $month)
            ->whereYear('monthYear', $year)
            ->count();
        if ((int) $rowCount == 1) {
            $daypass = 3;
        } else {
            if ($date->format('d') < 16) {
                $daypass = 1;
            } else {
                $daypass = 2;
            }
        }

        return '/monthlydetails/'.$month.'/'.$year.'/'.$date->format('d').'/'.$daypass;

    }
    public function pinnedArticle() {
        return $this->belongsTo('App\Models\UserCategory','pinn_article_id');
    }
}
