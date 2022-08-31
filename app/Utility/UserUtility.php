<?php

namespace App\Utility;

use App\Models\User\Role;

class UserUtility
{
    public function  fetchRoleName($roleId)
    {
        switch ($roleId) {
            case Role::SUPER_ADMIN :
                $role = "Super Admin";
                break;
            case Role::ADMIN :
                $role = "Admin";
                break;
            case Role::AUTHOR :
                $role = "Author";
                break;
            case Role::BRAND :
                $role = "Brand";
                break;
        }
        return $role;
    }    
}