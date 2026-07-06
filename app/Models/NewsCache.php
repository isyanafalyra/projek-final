<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsCache extends Model
{
    protected $table = 'news_caches';
    protected $guarded = [];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
