<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'admin_permissions';

    protected $fillable = [
        'name', 'slug', 'http_method', 'http_path'
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function users()
    {
        return $this->belongsToMany(AdminUser::class, 'admin_user_permissions');
    }
}
