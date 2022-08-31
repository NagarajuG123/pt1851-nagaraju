<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{   
    const SUPER_ADMIN = 1;
    const ADMIN = 2;
    const AUTHOR = 3;
    const BRAND = 4;

    public $table = 'role';  
}
