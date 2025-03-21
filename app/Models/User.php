<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Panel;
use Illuminate\Support\Str;
use App\Traits\BelongToHotel;
use App\Models\Scopes\HotelScope;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Collection;
use Filament\Models\Contracts\HasName;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Storage;
use Filament\Models\Contracts\HasAvatar;

use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\HasTenants;
use Filament\Models\Contracts\FilamentUser;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use CloudinaryLabs\CloudinaryLaravel\MediaAlly;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// #[ScopedBy(HotelScope::class)]
class User extends Authenticatable implements FilamentUser, HasName, HasAvatar
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, BelongToHotel;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     *
     */

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar ? getCloudinaryUrl($this->avatar) : null;
    }


    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user', 'marketer_id', 'team_id');
    }

    public function store($image)
    {
        // Store the image to Cloudinary
        $path = Storage::disk('cloudinary')->put('9jahotel', $image);

        // Store the Cloudinary URL in your database
        $this->avatar = $path;
        $this->save();
        // $this->images()->create(['image_url' => $path]);
    }

    public function getFilamentName(): string
    {
        return "{$this->firstname} {$this->lastname}";
    }

    protected $guarded = ['password_confirmation'];

    /**
     * The attributes that should be hidden for serialization
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'password' => 'hashed',
    ];

    // public function getDefaultTenant(Panel $panel): ?Model
    // {
    //     return $this->latestTeam;
    // }

    // public function latestTeam(): BelongsTo
    // {
    //     return $this->belongsTo(Hotel::class, 'latest_team_id');
    // }

    function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }


    public function hotels(): BelongsToMany
    {
        return $this->belongsToMany(Hotel::class);
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function ($user) {
            $user->username = IdGenerator::generate(['table' => 'users', 'field' => 'username', 'length' => 8, 'reset_on_prefix_change' => true, 'prefix' => 'RS' . strtoupper(Str::random(2)) . date('y')]);
        });

        // create profile for every user created
        self::created(function ($user) {
            $user->profile()->create();
            // $user->username = $user->update([
            //     'username' =>  IdGenerator::generate(['table' => 'users', 'field' => 'username', 'length' => 8, 'reset_on_prefix_change' => true, 'prefix' => substr($user->hotel->code, 0, 3) . date('y')])
            // ]);

            // attach role when created
            $user->assignRole($user->current_role);

            $permissions = $user->getPermissionsViaRoles();
            if (count($permissions) > 0) {
                foreach ($permissions as $permission) {
                    $user->givePermissionTo($permission->name);
                }
            }
        });

        self::updating(function ($user) {
            // Get the old and new values for the specific column
            $oldRole = $user->getOriginal('current_role');
            $newRole = $user->current_role;

            // Only proceed if the value has changed
            if ($oldRole !== $newRole) {
                // Update the role and permissions
                $user->assignRole($newRole);

                $permissions = $user->getPermissionsViaRoles();
                if (count($permissions) > 0) {
                    foreach ($permissions as $permission) {
                        $user->givePermissionTo($permission->name);
                    }
                }
            }
        });
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'agent_id');
    }

    public function scopeExcludeSuperadmins($query, $filter)
    {
        return $query
            ->where('hotel_id', auth()->user()->hotel_id)
            // ->where('current_role', '!=', 'super-admin')
            ->where('username', 'LIKE', trim($filter));
        // ->orWhere('firstname', 'LIKE', trim($filter))
        // ->orWhere('lastname', 'LIKE', trim($filter))
        // ->orwhereHas('department', fn (Builder $query) => $query->where('name', 'LIKE', trim($filter)));
    }
    public function scopeMyStaff($query, $filter)
    {
        return $query
            ->where('hotel_id', auth()->user()->hotel_id)
            ->where('current_role', '!=', 'super-admin')
            ->where('username', 'LIKE', trim($filter));
        // ->orWhere('firstname', 'LIKE', trim($filter))
        // ->orWhere('lastname', 'LIKE', trim($filter))
        // ->orwhereHas('department', fn (Builder $query) => $query->where('name', 'LIKE', trim($filter)));
    }
}