<div>
    @php
    // Prepare images for the grid
    $mainImage = $slides[0] ?? ['imgSrc' => 'https://9jahotel.com/img/logo.jpeg', 'imgAlt' => 'Default hotel image'];
    $gridImages = array_slice($slides, 1, 4);
    $defaultGrid = [
    ['imgSrc' => 'https://9jahotel.com/img/logo.jpeg', 'imgAlt' => 'Default hotel image'],
    ['imgSrc' => 'https://9jahotel.com/img/logo.jpeg', 'imgAlt' => 'Default hotel image'],
    ['imgSrc' => 'https://9jahotel.com/img/logo.jpeg', 'imgAlt' => 'Default hotel image'],
    ['imgSrc' => 'https://9jahotel.com/img/logo.jpeg', 'imgAlt' => 'Default hotel image'],
    ];
    // Fill grid with defaults if not enough images
    $gridImages = array_pad($gridImages, 4, null);
    foreach ($gridImages as $k => $img) {
    if (!$img) $gridImages[$k] = $defaultGrid[$k];
    }
    $imageCount = count($slides);
    @endphp

    {{-- The following block for initial mobile image is removed --}}

    {{-- Mobile: Show only one image if only one, else show slideshow --}}
    @if($imageCount <= 1)
        {{-- Only show single image if it's a real image or the only image available (even if default) --}}
        {{-- If $hasRealImages is false AND $imageCount is 1 (meaning it's a default), it will show. --}}
        {{-- If $hasRealImages is true AND $imageCount is 1, it will show. --}}
        <div class="block md:hidden relative">
            <img src="{{ $mainImage['imgSrc'] }}" alt="{{ $mainImage['imgAlt'] }}"
                class="w-full aspect-square object-cover rounded">
        </div>
    @else
        {{-- This is the mobile slideshow part --}}
        <div 
            x-data="{
                slides: {{ json_encode($slides) }},
                currentSlideIndex: 1,
                slidesCount: {{ $imageCount }},
                next() {
                    if (this.currentSlideIndex < this.slides.length) {
                        this.currentSlideIndex = this.currentSlideIndex + 1;
                    } else {
                        this.currentSlideIndex = 1;
                    }
                },
                previous() {
                    if (this.currentSlideIndex > 1) {
                        this.currentSlideIndex = this.currentSlideIndex - 1;
                    } else {
                        this.currentSlideIndex = this.slides.length;
                    }
                }
            }" class="block md:hidden relative">
    <template x-for="(slide, index) in slides" :key="index">
        <div x-show="currentSlideIndex == index + 1" class="relative">
            <img :src="slide.imgSrc" :alt="slide.imgAlt"
                class="w-full aspect-square object-cover rounded cursor-pointer" @click="next()">
            <span class="absolute bottom-3 right-3 bg-black/70 text-white text-xs px-2 py-1 rounded">
                <span x-text="slidesCount"></span> photos
            </span>
            <!-- Optional: Show navigation arrows on mobile -->
            <button type="button"
                class="absolute left-2 top-1/2 z-20 flex rounded-full -translate-y-1/2 items-center justify-center bg-white/60 p-1 text-neutral-600"
                aria-label="previous slide" @click.stop="previous()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" fill="none"
                    stroke-width="3" class="size-4" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
            </button>
            <button type="button"
                class="absolute right-2 top-1/2 z-20 flex rounded-full -translate-y-1/2 items-center justify-center bg-white/60 p-1 text-neutral-600"
                aria-label="next slide" @click.stop="next()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" fill="none"
                    stroke-width="3" class="size-4" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </button>
        </div>
    </template>
    <!-- Indicators -->
    <div class="absolute rounded-sm bottom-3 left-3 z-20 flex gap-2 bg-white/75 px-1 py-0.5" role="group"
        aria-label="slides">
        <template x-for="(slide, index) in slides" :key="index">
            <button class="size-2 rounded-full transition"
                :class="currentSlideIndex === index + 1 ? 'bg-neutral-600' : 'bg-neutral-600/50'"
                @click="currentSlideIndex = index + 1" :aria-label="'slide ' + (index + 1)"></button>
        </template>
    </div>
</div>
@endif

{{-- Desktop: Main image left, 4-grid right --}}
<div class="hidden md:flex gap-2 w-full max-h-max">
    <div class="w-1/2 h-full">
        <img src="{{ $mainImage['imgSrc'] }}" alt="{{ $mainImage['imgAlt'] }}"
            class="w-full h-full aspect-square object-cover rounded-lg cursor-pointer"
            @click="$dispatch('open-carousel', { startSlide: 1 })">
    </div>
    <div class="w-1/2 grid grid-cols-2 grid-rows-2 gap-2 h-full relative">
        @foreach($gridImages as $i => $img)
        <img src="{{ $img['imgSrc'] }}" alt="{{ $img['imgAlt'] }}"
            class="w-full h-full aspect-square object-cover rounded @if($i==3) rounded-br-lg @endif @if($i==1) rounded-tr-lg @endif cursor-pointer"
            @click="$dispatch('open-carousel', { startSlide: {{ $i + 2 }} })">
        @endforeach
        @if (!empty($showAllPhotos) && $showAllPhotos)
        <button class="absolute bottom-3 right-3 z-30 bg-white/80 px-4 py-2 rounded shadow"
            @click="$dispatch('open-carousel')"> {{-- This will default to slide 1 --}}
            Show all photos
        </button>
        @endif
    </div>
</div>

{{-- Carousel Modal (hidden by default, shown when open-carousel is dispatched) --}}
<div x-data="{
                slides: {{ json_encode($slides) }},
                currentSlideIndex: 1,
                showCarousel: false,
                init() {
                    window.addEventListener('open-carousel', (event) => {
                        if (event.detail && event.detail.startSlide) {
                            this.currentSlideIndex = event.detail.startSlide;
                        } else {
                            this.currentSlideIndex = 1; // Default to first slide
                        }
                        this.showCarousel = true;
                    });
                },
                close() { this.showCarousel = false; },
                previous() {
                    if (this.currentSlideIndex > 1) {
                        this.currentSlideIndex = this.currentSlideIndex - 1;
                    } else {
                        this.currentSlideIndex = this.slides.length;
                    }
                },
                next() {
                    if (this.currentSlideIndex < this.slides.length) {
                        this.currentSlideIndex = this.currentSlideIndex + 1;
                    } else {
                        this.currentSlideIndex = 1;
                    }
                },
            }" x-show="showCarousel" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/80">
    <button @click="close()" class="absolute top-5 right-5 text-white text-2xl">&times;</button>
    <div class="relative w-full max-w-3xl mx-auto">
        <!-- previous button -->
        <button type="button"
            class="absolute left-2 top-1/2 z-20 flex rounded-full -translate-y-1/2 items-center justify-center bg-white/40 p-2 text-neutral-600"
            aria-label="previous slide" x-on:click="previous()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" fill="none"
                stroke-width="3" class="size-5 md:size-6 pr-0.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
        </button>
        <!-- next button -->
        <button type="button"
            class="absolute right-2 top-1/2 z-20 flex rounded-full -translate-y-1/2 items-center justify-center bg-white/40 p-2 text-neutral-600"
            aria-label="next slide" x-on:click="next()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" fill="none"
                stroke-width="3" class="size-5 md:size-6 pl-0.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
        </button>
        <!-- slides -->
        <div class="relative min-h-[50svh] w-full">
            <template x-for="(slide, index) in slides" :key="index">
                <div x-show="currentSlideIndex == index + 1" class="absolute inset-0"
                    x-transition:enter="transition ease-out duration-1000" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-1000"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                    <img class="absolute rounded w-full h-full inset-0 object-cover text-neutral-600"
                        :src="slide.imgSrc" :alt="slide.imgAlt" />
                </div>
            </template>
        </div>
        <!-- indicators -->
        <div class="absolute rounded-sm bottom-3 left-1/2 z-20 flex -translate-x-1/2 gap-4 bg-white/75 px-1.5 py-1"
            role="group" aria-label="slides">
            <template x-for="(slide, index) in slides" :key="index">
                <button class="size-2 rounded-full transition"
                    :class="currentSlideIndex === index + 1 ? 'bg-neutral-600' : 'bg-neutral-600/50'"
                    x-on:click="currentSlideIndex = index + 1" :aria-label="'slide ' + (index + 1)"></button>
            </template>
        </div>
    </div>
</div>
</div>
