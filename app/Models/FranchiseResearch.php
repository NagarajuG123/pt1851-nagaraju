<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FranchiseResearch extends Model
{
    public $table = 'franchise_research';

    const BRAND_INFO = 1;
    const WHY_I_BOUGHT = 2;
    const EXECUTIVE = 3;
    const AVAILABLE_MARKET = 4;

}
