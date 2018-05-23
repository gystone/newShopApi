<?php

namespace App\Traits;

trait HasPermissioins
{
    public function can(string $permission) : bool
    {
        if ($this->roles->getCheckedCities()->flatten()->contains($permission)) {
            return true;
        }

        return false;
    }

    public function cannot(string $permission) : bool
    {
        return !$this->can($permission);
    }
}