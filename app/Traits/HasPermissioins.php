<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait HasPermissioins
{
    public function getCheckedCities()
    {
        $checkedCities = [];Log::info($this->roles);
        if (is_array($this->roles->pluck('permissions'))) {
            foreach ($this->roles->pluck('permissions') as $permission) {
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
    {Log::info($this->getCheckedCities());
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