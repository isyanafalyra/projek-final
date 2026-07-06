<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $guarded = [];

    public function ports()
    {
        return $this->hasMany(Port::class);
    }

    public function riskScores()
    {
        return $this->hasMany(RiskScore::class);
    }
}
