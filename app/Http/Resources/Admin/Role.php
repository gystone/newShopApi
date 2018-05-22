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
        if (isset($this->premissions)) {
            foreach ($this->premissions as $premission) {dd($premission);
                if (isset($premission['children'])) {
                    foreach ($premission['children'] as $child) {
                        $checkedCities = array_merge($checkedCities, $child['meta']['checkedCities']);
                    }
                } else {
                    $checkedCities = array_merge($checkedCities, $premission['meta']['checkedCities']);
                }
            }
        }

        return array(
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
//            'permissions' => new RolePermissionCollection($this->permissions),
            'permissions' => $this->permissions,
            'checkedCities' => $checkedCities,
            'created_at' => $this->created_at !== null ? date_format($this->created_at, 'Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at !== null ? date_format($this->updated_at, 'Y-m-d H:i:s') : null,
        );
    }
}
