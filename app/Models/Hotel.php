<?php

namespace App\Models;

use Carbon\Carbon;
use App\Traits\HasManyImage;
use App\Traits\HasManyRating;
use Nnjeim\World\Models\City;
use Nnjeim\World\Models\Currency;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\InteractsWithMedia;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Hotel extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, Notifiable, InteractsWithMedia, HasManyImage, HasManyRating;

    protected $guarded = ['country_id', 'state_id'];
    protected $casts = [
        'checked_out' => 'datetime',
        'renew' => 'date'
    ];

    public function getCurrentTenantLabel(): string
    {
        return 'Current Hotel';
    }

    public function user(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }


    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
    public function revenues(): HasMany
    {
        return $this->hasMany(Revenue::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(CityCopy::class);
    }

    function bookingInvoices(): HasMany
    {
        return $this->hasMany(BookingInvoice::class);
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class)->withTimestamps();
    }

    // public function store($image)
    // {
    //     // Store the image to Cloudinary
    //     $path = Storage::disk('cloudinary')->put('9jahotel', $image);

    //     // Store the Cloudinary URL in your database
    //     $this->logo = $path;
    //     $this->save();
    //     // $this->images()->create(['image_url' => $path]);
    // }


    public function halls(): HasMany
    {
        return $this->hasMany(Hall::class, 'hotel_id');
    }
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'hotel_id');
    }
    public function reviews(): HasMany
    {
        return $this->hasMany(Rating::class, 'hotel_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function roomCategory(): HasMany
    {
        return $this->hasMany(Type::class, 'hotel_id');
    }

    public function types(): HasMany
    {
        return $this->hasMany(Type::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }


    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class);
    }

    public function roomWithLowestPrice()
    {
        return $this->roomCategory()->orderBy('price1')->first();
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function primaryCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'primary_currency');
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'amenity_hotel');
    }
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include hotels with at least one type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHasTypes(Builder $query): Builder
    {
        return $query->has('roomCategory');
    }

    public function scopeCapacity(Builder $query, $capacity): Builder
    {
        $capacity = (int) $capacity;
        return $query->whereHas('halls', function (Builder $query) use ($capacity) {
            $query->withoutGlobalScopes()->where('capacity', '>=', $capacity);
        });
    }

    public function secondaryCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'secondary_currency');
    }

    public function favourites(): HasMany
    {
        return $this->hasMany(Favourite::class)->withTimestamps();
    }

    /**
     * Scope a query to search hotels by name while preserving pagination.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|null  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if ($search) {
            return $query->where('name', 'LIKE', "%{$search}%");
        }

        return $query;
    }

    public function favouritedByCustomers()
    {
        return $this->belongsToMany(Customer::class, 'favourites')
            ->using(Favourite::class)
            ->withTimestamps();
    }


    public function scopeForSearchResults(Builder $query): Builder
    {
        return $query->select('hotels.*')->distinct();
    }

    /**
     * Optimized scope for search results with ratings
     */
    public function scopeWithSearchOptimizations(Builder $query): Builder
    {
        return $query->select([
            'hotels.*',
            DB::raw('COALESCE(AVG(ratings.number), 0) as avg_rating'),
            DB::raw('COUNT(ratings.id) as rating_count')
        ])
            ->leftJoin('ratings', 'hotels.id', '=', 'ratings.hotel_id')
            ->groupBy('hotels.id');
    }

    /**
     * Scope for filtering by multiple amenities efficiently
     */
    public function scopeWithAmenities(Builder $query, array $amenityIds): Builder
    {
        if (empty($amenityIds)) {
            return $query;
        }

        foreach ($amenityIds as $amenityId) {
            $query->whereExists(function ($subQuery) use ($amenityId) {
                $subQuery->select(DB::raw(1))
                    ->from('amenity_hotel')
                    ->where('amenity_hotel.hotel_id', '=', DB::raw('hotels.id'))
                    ->where('amenity_hotel.amenity_id', '=', $amenityId);
            });
        }

        return $query;
    }

    /**
     * Scope for price range filtering
     */
    public function scopePriceRange(Builder $query, ?float $min = null, ?float $max = null): Builder
    {
        if ($min !== null) {
            $query->where('room_lowest_price', '>=', $min);
        }

        if ($max !== null) {
            $query->where('room_lowest_price', '<=', $max);
        }

        return $query;
    }

    /**
     * Scope for city filtering with index usage
     */
    public function scopeInCity(Builder $query, int $cityId): Builder
    {
        return $query->where('city_id', $cityId);
    }

    /**
     * Scope for hall price range filtering
     */
    public function scopeHallPriceRange(Builder $query, ?float $min = null, ?float $max = null): Builder
    {
        if ($min !== null) {
            $query->where('hall_lowest_price', '>=', $min);
        }

        if ($max !== null) {
            $query->where('hall_lowest_price', '<=', $max);
        }

        return $query;
    }

    /**
     * Optimized scope for search with all common needs
     */
    public function scopeOptimizedSearch(Builder $query, array $params = []): Builder
    {
        $query = $query->select([
            'hotels.*',
            DB::raw('COALESCE(AVG(ratings.number), 0) as avg_rating'),
            DB::raw('COUNT(DISTINCT ratings.id) as rating_count')
        ])
            ->leftJoin('ratings', 'hotels.id', '=', 'ratings.hotel_id')
            ->with(['city:id,name', 'amenities:id,name'])
            ->groupBy('hotels.id');

        // Apply city filter if provided
        if (isset($params['city_id'])) {
            $query->where('hotels.city_id', $params['city_id']);
        }

        // Apply name search if provided
        if (isset($params['name']) && !empty($params['name'])) {
            $query->where('hotels.name', 'LIKE', '%' . trim($params['name']) . '%');
        }

        // Apply price range if provided
        if (isset($params['price_min']) && is_numeric($params['price_min'])) {
            $query->where('hotels.room_lowest_price', '>=', floatval($params['price_min']));
        }
        if (isset($params['price_max']) && is_numeric($params['price_max'])) {
            $query->where('hotels.room_lowest_price', '<=', floatval($params['price_max']));
        }

        // Apply amenity filters if provided
        if (isset($params['amenities']) && is_array($params['amenities']) && !empty($params['amenities'])) {
            foreach ($params['amenities'] as $amenityId) {
                $query->whereExists(function ($subQuery) use ($amenityId) {
                    $subQuery->select(DB::raw(1))
                        ->from('amenity_hotel')
                        ->where('amenity_hotel.hotel_id', '=', DB::raw('hotels.id'))
                        ->where('amenity_hotel.amenity_id', '=', intval($amenityId));
                });
            }
        }

        // Ensure only hotels with room categories
        $query->whereHas('roomCategory');

        return $query;
    }

    /**
     * Optimized scope for hall search
     */
    public function scopeOptimizedHallSearch(Builder $query, array $params = []): Builder
    {
        $query = $query->select([
            'hotels.*',
            DB::raw('COALESCE(AVG(ratings.number), 0) as avg_rating'),
            DB::raw('COUNT(DISTINCT ratings.id) as rating_count')
        ])
            ->leftJoin('ratings', 'hotels.id', '=', 'ratings.hotel_id')
            ->with(['city:id,name', 'amenities:id,name'])
            ->groupBy('hotels.id');

        // Apply city filter if provided
        if (isset($params['city_id'])) {
            $query->where('hotels.city_id', $params['city_id']);
        }

        // Apply name search if provided
        if (isset($params['name']) && !empty($params['name'])) {
            $query->where('hotels.name', 'LIKE', '%' . trim($params['name']) . '%');
        }

        // Apply hall price range if provided
        if (isset($params['price_min']) && is_numeric($params['price_min'])) {
            $query->where('hotels.hall_lowest_price', '>=', floatval($params['price_min']));
        }
        if (isset($params['price_max']) && is_numeric($params['price_max'])) {
            $query->where('hotels.hall_lowest_price', '<=', floatval($params['price_max']));
        }

        // Apply capacity filter if provided
        if (isset($params['capacity']) && is_numeric($params['capacity'])) {
            $query->where('hotels.hall_capacity', '>=', intval($params['capacity']));
        }

        // Apply amenity filters if provided
        if (isset($params['amenities']) && is_array($params['amenities']) && !empty($params['amenities'])) {
            foreach ($params['amenities'] as $amenityId) {
                $query->whereExists(function ($subQuery) use ($amenityId) {
                    $subQuery->select(DB::raw(1))
                        ->from('amenity_hotel')
                        ->where('amenity_hotel.hotel_id', '=', DB::raw('hotels.id'))
                        ->where('amenity_hotel.amenity_id', '=', intval($amenityId));
                });
            }
        }

        // Ensure only hotels with halls
        $query->whereHas('halls');

        return $query;
    }

    /**
     * Get average rating efficiently
     */
    public function getAverageRatingAttribute()
    {
        return $this->ratings()->avg('number') ?? 0;
    }

    /**
     * Get total ratings count efficiently
     */
    public function getTotalRatingsAttribute()
    {
        return $this->ratings()->count();
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($hotel) {
            $hotel->code = IdGenerator::generate(['table' => 'hotels', 'field' => 'code', 'reset_on_prefix_change' => true, 'length' => 7, 'prefix' => getFirstLetters($hotel->name) . date('y')]);
        });

        self::created(function ($hotel) {
            $hotel->update(['renew' => Carbon::parse($hotel->created_at)->addMonth()]);
            $hotel->invoices()->create([
                'description' => 'Hotel Subscription due',
                'status' => 'unpaid',
                'amount' => 99995, // Example amount
                'from' => $hotel->created_at,
                'subscription' => 'annually'
            ]);

            // associated users to hotels
            $hotel->users()->attach(auth()->id());
        });
    }
}
