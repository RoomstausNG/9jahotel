<?php

namespace App\Models;

use App\Traits\BelongToHotel;
use App\Traits\IsCreatingHotel;
use App\Models\Scopes\HotelScope;
use Illuminate\Database\Eloquent\Model;
use CloudinaryLabs\CloudinaryLaravel\MediaAlly;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// #[ScopedBy([HotelScope::class])]
class Amenity extends Model
{
    use HasFactory;
    use BelongToHotel;
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function amenity(): BelongsTo
    {
        return $this->belongTo(GeneralAmenity::class, 'general_amenity_id');
    }
}