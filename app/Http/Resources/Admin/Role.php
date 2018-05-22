<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\Resource;

class Role extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $checkedCities = [];
        if (is_array($this->permissions)) {
            foreach ($this->permissions as $permission) {
                if (isset($permission['children'])) {
                    foreach ($permission['children'] as $child) {
                        $checkedCities = array_merge($checkedCities, $child['meta']['checkedCities']);
                    }
                } elseif (isset($permission['meta'])) {
                    $checkedCities = array_merge($checkedCities, $permission['meta']['checkedCities']);
                }
            }
        }

        return array(
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
//            'permissions' => new RolePermissionCollection($this->permissions),
            'permissions' => $this->getCheckedCities(),
            'checkedCities' => $checkedCities,
            'created_at' => $this->created_at !== null ? date_format($this->created_at, 'Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at !== null ? date_format($this->updated_at, 'Y-m-d H:i:s') : null,
        );
    }
}
