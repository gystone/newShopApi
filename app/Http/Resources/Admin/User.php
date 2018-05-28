<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Storage;

class User extends Resource
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
            'username' => $this->username,
            'name' => $this->name,
            'avatar_url' => Storage::disk('admin')->url($this->avatar ?? 'default.jpg'),
            'avatar' => $this->avatar,
            'roles_name' =>  $this->roles->first() ? $this->roles->first()->name : null,
            'roles' => new RoleUserCollection($this->roles),
            'created_at' => $this->created_at !== null ? date_format($this->created_at, 'Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at !== null ? date_format($this->updated_at, 'Y-m-d H:i:s') : null,
        );
    }
}
