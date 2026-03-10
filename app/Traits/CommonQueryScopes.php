<?php

namespace App\Traits;

trait CommonQueryScopes
{
    public function scopeFilterByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSearchByTitle($query, $title)
    {
        return $query->where('title', 'like', "%$title%");
    }
}
