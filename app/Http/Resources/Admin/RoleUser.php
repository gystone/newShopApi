<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\Resource;

class RoleUser extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'permissions' => $this->permissions,
        );
    }
}
