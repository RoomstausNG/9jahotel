<?php

namespace App\Console\Commands;

use App\Models\GeneralItem;
use Illuminate\Support\Str;
use Illuminate\Console\Command;

class UpdateImageUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:image';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updating image url from image to image_url';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating image URLs for general items...');

        $items = GeneralItem::all();

        // foreach ($items as $item) {
        //     if ($item->image && (is_null($item->image_url) || Str::contains($item->image_url, 'avatar'))) {
        //         $imageUrl = getCloudinaryImage($item->image);
        //         $item->update(['image_url' => $imageUrl]);
        //         $this->info("Updated image URL for item image: {$imageUrl} id: {$item->id}");
        //     } else {
        //         $this->info("No image found for item ID: {$item->id}");
        //         continue;
        //     }
        // }

        foreach ($items as $item) {
            try {
                if ($item->image && (is_null($item->image_url) || Str::contains($item->image_url, 'avatar'))) {
                    $imageUrl = getCloudinaryImage($item->image);

                    if (Str::contains($imageUrl, 'cloudinary')) {
                        $item->items()->update(['image' => $item->image]);
                        $item->update(['image_url' => $imageUrl]);
                    } else {
                        $this->error("Unable to generate url for item ID: {$item->id}");
                        continue;
                    }

                    $this->info("Updated image URL for item image: {$imageUrl} id: {$item->id}");
                } else {
                    $this->info("No image found for item ID: {$item->id}, Image: {$item->image}");
                    continue;
                }
            } catch (\Exception $e) {
                $this->error("Error processing item ID: {$item->id}. Message: " . $e->getMessage());
                continue;
            }
        }

        $this->info('Image URL update process completed!');
    }
}
