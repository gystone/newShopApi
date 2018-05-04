<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'admin_menus';

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'admin_role_menu');
    }
}
