<?php

namespace App\Models;

use App\Traits\BelongToHotel;
use Nnjeim\World\Models\City;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StateCopy extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'states';

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    function cities(): HasMany
    {
        return $this->hasMany(City::class, 'state_id');
    }
}