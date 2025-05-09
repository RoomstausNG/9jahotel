<?php

namespace App\Livewire;

use App\Models\Hotel;
use Livewire\Component;

class ImageCarousel extends Component
{
    /**
     * The hotel ID to load images from
     */
    public $hotelId;

    /**
     * The array of slides to display in the carousel
     */
    public $slides = [];

    /**
     * Mount the component with optional hotel ID
     */
    public function mount($hotelId = null)
    {
        $this->hotelId = $hotelId;
        $this->loadSlides();

        // Debug: Make sure we have slides after loading
        if (empty($this->slides)) {
            // Force at least one default slide if none were loaded
            $this->slides = [
                [
                    'imgSrc' => 'https://penguinui.s3.amazonaws.com/component-assets/carousel/default-slide-1.webp',
                    'imgAlt' => 'Default slide',
                ]
            ];
        }
    }

    /**
     * Load the slides based on hotel ID or use defaults
     */
    protected function loadSlides()
    {
        // Use the provided logo as the default image
        $defaultSlides = [
            [
                'imgSrc' => 'https://9jahotel.com/img/logo.jpeg',
                'imgAlt' => 'Default hotel image',
            ]
        ];

        if (!$this->hotelId) {
            $this->slides = $defaultSlides;
            $this->showAllPhotos = false;
            $this->hasRealImages = false;
            return;
        }

        try {
            $hotel = Hotel::with('images')->find($this->hotelId);

            $slides = [];
            $hasImages = false;

            // 1. Add background image as the first slide if available
            if ($hotel && $hotel->background_image) {
                $slides[] = [
                    'imgSrc' => getCloudinaryImage($hotel->background_image),
                    'imgAlt' => $hotel->name . ' - Background image'
                ];
            }

            // 2. Add up to 4 more images
            if ($hotel && $hotel->images && $hotel->images->count() > 0) {
                $images = $hotel->images;
                $imageSlides = [];
                foreach ($images->take(4) as $image) {
                    $imageSlides[] = [
                        'imgSrc' => getCloudinaryImage($image->url),
                        'imgAlt' => $hotel->name . ' - ' . ($image->description ?? 'Hotel image')
                    ];
                }
                $slides = array_merge($slides, $imageSlides);
                $hasImages = true;
            }

            // 3. If no images at all, use default
            if (empty($slides)) {
                $slides = $defaultSlides;
            }

            // 4. Only show "Show all photos" if there are real hotel images
            $this->showAllPhotos = $hasImages;
            $this->hasRealImages = $hasImages;

            $this->slides = $slides;
        } catch (\Exception $e) {
            $this->slides = $defaultSlides;
            $this->showAllPhotos = false;
            $this->hasRealImages = false;
        }
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.image-carousel');
    }
}