<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'admin_roles';

    protected $fillable = [
        'name', 'slug'
    ];

    public function users()
    {
        return $this->belongsToMany(AdminUser::class, 'admin_role_users');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'admin_role_permissions');
    }

    public function menu()
    {
        return $this->belongsToMany(Menu::class, 'admin_role_menu');
    }
}
