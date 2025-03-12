<?php

namespace App\Models;

use App\Traits\BelongToHotel;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;

class Team extends Model
{
    use HasFactory, HasApiTokens, BelongToHotel;
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function hotels(): HasMany
    {
        return $this->hasMany(Hotel::class);
    }

    public function marketers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user', 'team_id', 'marketer_id');
    }

    protected static function boot()
    {
        parent::boot();

        // Auto-generate the team code before creating a team
        static::creating(function ($team) {
            $team->code = static::generateTeamCode();
        });
    }

    // Method to generate the next sequential team code
    // Method to generate the next sequential team code with zero-padding
    protected static function generateTeamCode()
    {
        return DB::transaction(function () {
            $lastTeam = static::lockForUpdate()->orderBy('id', 'desc')->first(); // Lock the table
            $lastId = $lastTeam ? $lastTeam->id : 0; // Get the last team's ID or default to 0
            $nextId = $lastId + 1; // Increment the ID
            return '9JATEAM-' . str_pad($nextId, 2, '0', STR_PAD_LEFT); // Zero-pad the ID
        });
    }
}