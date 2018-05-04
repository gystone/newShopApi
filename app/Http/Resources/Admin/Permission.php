<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\Resource;

class Permission extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'http_method' => $this->http_method,
            'http_path' => $this->http_path,
            'created_at' => $this->created_at !== null ? date_format($this->created_at, 'Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at !== null ? date_format($this->updated_at, 'Y-m-d H:i:s') : null,
        ];
    }
}
