<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;


class City extends Model
{

    use HasFactory;

    protected $guarded = [];
    protected $keyType = 'string';
    public $incrementing = false;

    public function events(): HasMany
    {

        return $this->hasMany(Event::class, 'city_id');
    }
}
