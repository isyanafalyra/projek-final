<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskScore extends Model
{
    protected $guarded = [];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    protected function casts(): array
    {
        return [
            'calculated_at' => 'datetime',
        ];
    }
}
