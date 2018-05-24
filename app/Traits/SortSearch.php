<?php

namespace App\Traits;

trait SortSearch
{
    public function sort($sort = null)
    {
        if ($sort) {
            foreach ($sort as $k => $v) {
                $query = $this->orderBy($k, $v);
            }
        }

        return $query;
    }

    public function search($search = null)
    {
        if ($search) {
            foreach ($search as $k => $v) {
                $query = $this->where($k, 'like', '%'.$v.'%');
            }
        }

        return $query;
    }

    public function sortAndSearch($sort = null, $search = null)
    {
        $query = $this->sort($query, $sort);
        $query = $this->search($query, $search);

        return $query;
    }
}