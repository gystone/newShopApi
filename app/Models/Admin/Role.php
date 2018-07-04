<?php

namespace App\Models\Admin;

use App\Traits\SortSearch;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use SortSearch;

    protected $table = 'admin_roles';

    protected $fillable = [
        'name', 'slug', 'permissions'
    ];

    public function getPermissionsAttribute($value)
    {
        return unserialize($value);
    }

    public function setPermissionsAttribute($value)
    {
        $this->attributes['permissions'] = serialize($value);
    }

    public function users()
    {
        return $this->belongsToMany(AdminUser::class, 'admin_role_users');
    }

    public function menu()
    {
        return $this->belongsToMany(Menu::class, 'admin_role_menu');
    }
}
