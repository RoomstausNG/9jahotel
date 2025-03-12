<?php

namespace App\Models;

use App\Traits\BelongToHotel;
use App\Models\Scopes\HotelScope;
use App\Traits\IsCreatingHotel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ScopedBy([HotelScope::class])]
class Department extends Model
{
    use HasFactory, BelongToHotel, IsCreatingHotel;
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}