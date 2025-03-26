<?php

namespace App\Models;

use App\Traits\BelongToHotel;
use CloudinaryLabs\CloudinaryLaravel\MediaAlly;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GeneralItem extends Model
{
    use HasFactory, BelongToHotel;
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    static function booted()
    {
        parent::booted();

        self::created(function (self $item) {
            $imageUrl = getCloudinaryImage($item->image);
            $item->image_url = $imageUrl;
            $item->save();
        });
    }
}
