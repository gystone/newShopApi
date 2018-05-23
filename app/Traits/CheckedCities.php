<?php

namespace App\Traits;

trait CheckedCities
{
    public function getCheckedCities()
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
        return collect($checkedCities);
    }

    public function can(string $permission) : bool
    {
        if ($this->getCheckedCities()->contains($permission)) {
            return true;
        }

        return false;
    }

    public function cannot(string $permission) : bool
    {
        return !$this->can($permission);
    }
}