<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait HasPermissioins
{
    public function getCheckedCities()
    {
        $checkedCities = [];

        if ($this->isAdministrator()) {
            return collect(['admin']);
        }

        if (is_array($this->roles->pluck('permissions')->first())) {
            foreach ($this->roles->pluck('permissions')->first() as $permission) {
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

    public function isAdministrator() : bool
    {
        if (is_array($this->roles->pluck('permissions')->first())) {
            foreach ($this->roles->pluck('permissions')->first() as $permission) {
                if ($permission === 'admin') {
                    return true;
                }
            }
        }

        return false;
    }
}