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
use Spatie\MediaLibrary\InteractsWithMedia;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        return $this->belongsTo(City::class);
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

    public function secondaryCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'secondary_currency');
    }

    public function favourites(): HasMany
    {
        return $this->hasMany(Favourite::class)->withTimestamps();
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
