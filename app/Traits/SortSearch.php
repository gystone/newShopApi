<?php

namespace App\Traits;

trait SortSearch
{
    public function sort($query, $sort = null)
    {
        
        if ($sort) {
            foreach ($sort as $k => $v) { 
                $query = $query->orderBy($k, $v);
            }
        }

        return $query;
    }

    public function search($query, $search = null)
    { 
        if ($search) {
            foreach ($search as $k => $v) {
                $query = $query->where($k, 'like', '%'.$v.'%');
            }
        }
    
        return $query;
    }

    public function sortAndSearch($query, $sort = null, $search = null)
    {
        $query = $this->sort($query, $sort);
        $query = $this->search($query, $search);

        return $query;
    }
}