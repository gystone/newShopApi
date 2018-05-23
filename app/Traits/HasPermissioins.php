<?php

namespace App\Traits;

trait HasPermissioins
{
    public function getCheckedCities()
    {
        $checkedCities = [];
        if (is_array($this->roles->fluck('permissions'))) {
            foreach ($this->roles->fluck('permissions') as $permission) {
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

    public function isCan(string $permission) : bool
    {
        if ($this->getCheckedCities()->contains($permission)) {
            return true;
        }

        return false;
    }

    public function isCannot(string $permission) : bool
    {
        return !$this->isCan($permission);
    }
}