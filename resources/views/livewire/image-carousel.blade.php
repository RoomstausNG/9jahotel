<div>
    @php
        // Prepare images for the grid
        $mainImage = $slides[0] ?? [
            'imgSrc' => 'https://9jahotel.com/img/logo.jpeg',
            'imgAlt' => 'Default hotel image',
        ];

        // For the grid, show up to 4 more images after the main image (total 5 displayed in grid)
        $gridImages = [];
        $gridIndexes = [];
        $defaultGrid = [
            ['imgSrc' => 'https://9jahotel.com/img/logo.jpeg', 'imgAlt' => 'Default hotel image'],
            ['imgSrc' => 'https://9jahotel.com/img/logo.jpeg', 'imgAlt' => 'Default hotel image'],
            ['imgSrc' => 'https://9jahotel.com/img/logo.jpeg', 'imgAlt' => 'Default hotel image'],
            ['imgSrc' => 'https://9jahotel.com/img/logo.jpeg', 'imgAlt' => 'Default hotel image'],
        ];

        // Only take up to 4 images after the main image for grid display
        for ($i = 1; $i <= 4; $i++) {
            if (isset($slides[$i])) {
                $gridImages[] = $slides[$i];
                $gridIndexes[] = $i; // real index in $slides
            } else {
                $gridImages[] = $defaultGrid[$i - 1];
                $gridIndexes[] = null; // no real image
            }
        }

        $imageCount = count($slides);
        $hasMoreImages = $imageCount > 5; // More than 5 total (1 main + 4 grid)
        $remainingCount = max(0, $imageCount - 5);
    @endphp

    {{-- Mobile: Show only one image if only one, else show slideshow --}}
    @if ($imageCount <= 1)
        <div class="block md:hidden relative">
            <img src="{{ $mainImage['imgSrc'] }}" alt="{{ $mainImage['imgAlt'] }}"
                class="w-full aspect-square object-cover rounded">
        </div>
    @else
        {{-- Mobile slideshow --}}
        <div x-data="{
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
                    <!-- Navigation arrows on mobile -->
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

    {{-- Desktop: Main image left, 2x2 grid right --}}
    <div class="hidden md:flex gap-2 w-full max-h-max">
        {{-- Main image --}}
        <div class="w-1/2 h-full">
            <img src="{{ $mainImage['imgSrc'] }}" alt="{{ $mainImage['imgAlt'] }}"
                class="w-full h-full aspect-square object-cover rounded-lg cursor-pointer"
                @click="$dispatch('open-carousel', { startSlide: 1 })">
        </div>

        {{-- 2x2 Grid images --}}
        <div class="w-1/2 grid grid-cols-2 grid-rows-2 gap-2 h-full relative">
            @foreach ($gridImages as $i => $img)
                <div class="relative">
                    <img src="{{ $img['imgSrc'] }}" alt="{{ $img['imgAlt'] }}"
                        class="w-full h-full aspect-square object-cover rounded @if ($i == 3) rounded-br-lg @endif @if ($i == 1) rounded-tr-lg @endif cursor-pointer"
                        @if ($gridIndexes[$i] !== null) @click="$dispatch('open-carousel', { startSlide: {{ $gridIndexes[$i] + 1 }} })" @endif>

                    {{-- Show "more photos" overlay on last image if there are more than 5 total --}}
                    @if ($i == 3 && $hasMoreImages)
                        <div class="absolute inset-0 bg-black/60 flex items-center justify-center rounded @if ($i == 3) rounded-br-lg @endif cursor-pointer"
                            @click="$dispatch('open-carousel', { startSlide: 1 })">
                            <span class="text-white text-lg font-semibold">+{{ $remainingCount }} more</span>
                        </div>
                    @endif
                </div>
            @endforeach

            {{-- Show all photos button --}}
            @if (!empty($showAllPhotos) && $showAllPhotos)
                <button
                    class="absolute bottom-3 right-3 text-sm font-medium z-30 bg-white/90 px-4 py-2 rounded shadow hover:bg-white transition-colors"
                    @click="$dispatch('open-carousel')">
                    Showing all {{ $imageCount }} photos
                </button>
            @endif
        </div>
    </div>

    {{-- Carousel Modal --}}
    <div x-data="{
        slides: {{ json_encode($slides) }},
        currentSlideIndex: 1,
        showCarousel: false,
        init() {
            window.addEventListener('open-carousel', (event) => {
                if (event.detail && event.detail.startSlide) {
                    this.currentSlideIndex = event.detail.startSlide;
                } else {
                    this.currentSlideIndex = 1;
                }
                this.showCarousel = true;
                document.body.style.overflow = 'hidden'; // Prevent background scrolling
            });
        },
        close() {
            this.showCarousel = false;
            document.body.style.overflow = 'auto'; // Restore scrolling
        },
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
    }" x-show="showCarousel" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md cursor-pointer"
        @keydown.escape="close()" @keydown.arrow-left="previous()" @keydown.arrow-right="next()" @click="close()">

        {{-- Close button --}}
        <button @click.stop="close()"
            class="absolute top-6 right-6 text-white text-2xl hover:text-gray-300 z-40 bg-black/50 rounded-full w-12 h-12 flex items-center justify-center hover:bg-black/70 transition-colors shadow-lg border border-white/20">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" fill="none"
                stroke-width="2" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        {{-- Cancel button (alternative close) --}}
        <button @click.stop="close()"
            class="absolute top-6 left-6 text-white text-sm hover:text-gray-300 z-40 bg-black/50 rounded-lg px-4 py-3 hover:bg-black/70 transition-colors flex items-center gap-2 shadow-lg border border-white/20">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" fill="none"
                stroke-width="2" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Cancel
        </button>

        {{-- Image counter --}}
        <div class="absolute top-6 left-1/2 transform -translate-x-1/2 text-white text-lg z-40 bg-black/50 rounded-lg px-6 py-3 shadow-lg border border-white/20"
            @click.stop>
            <span x-text="currentSlideIndex"></span> / <span x-text="slides.length"></span>
        </div>

        {{-- Main content area --}}
        <div class="relative w-full h-full min-w-[90%] md:min-w-[70%] flex items-center justify-center p-4 md:p-8">
            {{-- Slides --}}
            <div class="relative w-full h-full max-h-[90vh] flex items-center justify-center" @click.stop>
                {{-- Previous button - positioned relative to image area --}}
                <button type="button"
                    class="absolute left-4 top-1/2 z-30 flex rounded-full -translate-y-1/2 items-center justify-center bg-white/70 p-3 text-gray-800 hover:bg-white/90 transition-all duration-200 shadow-xl border border-white/30 hover:scale-110"
                    aria-label="previous slide" @click.stop="previous()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" fill="none"
                        stroke-width="2.5" class="size-5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </button>

                {{-- Next button - positioned relative to image area --}}
                <button type="button"
                    class="absolute right-4 top-1/2 z-30 flex rounded-full -translate-y-1/2 items-center justify-center bg-white/70 p-3 text-gray-800 hover:bg-white/90 transition-all duration-200 shadow-xl border border-white/30 hover:scale-110"
                    aria-label="next slide" @click.stop="next()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" fill="none"
                        stroke-width="2.5" class="size-5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>

                <template x-for="(slide, index) in slides" :key="index">
                    <div x-show="currentSlideIndex == index + 1"
                        class="absolute inset-0 flex items-center justify-center"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        x-transition:enter-end="opacity-100 transform scale-100"
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100 transform scale-100"
                        x-transition:leave-end="opacity-0 transform scale-95">
                        <img class="max-w-full max-h-full min-w-[300px] md:min-w-[500px] object-contain rounded-lg shadow-2xl cursor-default"
                            :src="slide.imgSrc" :alt="slide.imgAlt" />
                    </div>
                </template>
            </div>
        </div>

        {{-- Indicators --}}
        <div class="absolute bottom-6 left-1/2 z-30 flex -translate-x-1/2 gap-3 bg-black/40 backdrop-blur-sm px-6 py-4 rounded-full shadow-lg"
            role="group" aria-label="slides" @click.stop>
            <template x-for="(slide, index) in slides" :key="index">
                <button class="size-4 rounded-full transition-all duration-300 hover:scale-125 border-2"
                    :class="currentSlideIndex === index + 1 ? 'bg-white border-white shadow-md' :
                        'bg-white/30 border-white/50 hover:bg-white/50'"
                    @click.stop="currentSlideIndex = index + 1" :aria-label="'Go to slide ' + (index + 1)"></button>
            </template>
        </div>
    </div>
</div>
