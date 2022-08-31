<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    
    public $table = 'admin';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['first_name', 'last_name', 'email', 'user_name', 'password', 'confirmPassword', 'type'], 'safe'],  
        ];
    }

    /**
     * Validates password.
     *
     * @param string $password password to validate
     *
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        if (is_null($this->password) or $this->password == '') {
            return false;
        }

        return Yii::$app->getSecurity()->validatePassword($password, $this->password);
    }
}
