<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;


class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable;
    public $table = 'user';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'user_name', 'password', 'password_hash', 'role_id', 'token', 'slug','old_id',
    'image_path', 'created_at', 'updated_at', 'created_by', 'updated_by', 'deleted_at'];
	

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];
   
     /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'name' => $this->adminProfile->first_name . ' ' . $this->adminProfile->last_name,
            'image' => env('IMAGE_PROXY_URL') . '/' .$this->image_path
        ];
    }

    //Relations
    public function adminProfile() {
        return $this->belongsTo('App\Models\User\AdminProfile', 'id', 'user_id');
    }
    public function role() {
        return $this->belongsTo('App\Models\User\Role','id');
    }
}
