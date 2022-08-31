<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class AdminProfile extends Model
{
    
    public $table = 'admin_profile';
    protected $fillable = ['id','first_name', 'last_name', 'email', 'user_id'];
    public $timestamps = false;   

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['first_name', 'last_name', 'email', 'user_id'], 'safe'],  
        ];
    }

}
