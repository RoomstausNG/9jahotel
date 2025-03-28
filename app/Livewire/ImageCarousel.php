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
        // Start with default slides to ensure we always have something
        $defaultSlides = [
            [
                'imgSrc' => 'https://penguinui.s3.amazonaws.com/component-assets/carousel/default-slide-1.webp',
                'imgAlt' => 'Vibrant abstract painting with swirling blue and light pink hues on a canvas.',
            ],
            [
                'imgSrc' => 'https://penguinui.s3.amazonaws.com/component-assets/carousel/default-slide-2.webp',
                'imgAlt' => 'Vibrant abstract painting with swirling red, yellow, and pink hues on a canvas.',
            ],
            [
                'imgSrc' => 'https://penguinui.s3.amazonaws.com/component-assets/carousel/default-slide-3.webp',
                'imgAlt' => 'Vibrant abstract painting with swirling blue and purple hues on a canvas.',
            ],
        ];

        if (!$this->hotelId) {
            $this->slides = $defaultSlides;
            return;
        }

        try {
            $hotel = Hotel::with('images')->find($this->hotelId);

            if ($hotel && $hotel->images && $hotel->images->count() > 0) {
                // Transform hotel images into the format expected by the carousel
                $hotelSlides = $hotel->images->map(function ($image) use ($hotel) {
                    return [
                        'imgSrc' => getCloudinaryImage($image->url), // Using the helper function
                        'imgAlt' => $hotel->name . ' - ' . ($image->description ?? 'Hotel image')
                    ];
                })->toArray();

                if (!empty($hotelSlides)) {
                    $this->slides = $hotelSlides;
                    return;
                }
            }

            // If hotel has no images but has a background image
            if ($hotel && $hotel->background_image) {
                $this->slides = [
                    [
                        'imgSrc' => getCloudinaryImage($hotel->background_image),
                        'imgAlt' => $hotel->name . ' - Background image'
                    ]
                ];
                return;
            }

            // Fall back to default slides if no hotel images are available
            $this->slides = $defaultSlides;
        } catch (\Exception $e) {
            // If anything goes wrong, use default slides
            $this->slides = $defaultSlides;
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