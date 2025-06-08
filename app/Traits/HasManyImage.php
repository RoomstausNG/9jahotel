<?php

namespace App\Traits;

use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasManyImage
{
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function storeImage($image)
    {
        // Store the image to Cloudinary
        $path = Storage::disk('cloudinary')->put('uploads', $image);

        // Store the Cloudinary URL in your database
        $this->image_url = $path;
        $this->images()->create(['image_url' => $path]);
    }
    public function store($image)
    {
        // Store the image to Cloudinary
        $path = Storage::disk('cloudinary')->put('9jahotel', $image);

        // Store the Cloudinary URL in your database
        $this->image_url = $path;
        $this->images()->create(['image_url' => $path]);
    }
}
