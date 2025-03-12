<?php

namespace App\Models;

use App\Models\Scopes\HotelScope;
use App\Traits\BelongToHotel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ScopedBy(HotelScope::class)]
class Finance extends Model
{
    use HasFactory, BelongToHotel;
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function financeable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeIncome(Builder $query): void
    {
        $query->where('type', 'income');
    }

    static public function scopeTotalExpenditure(Builder $query): int|float
    {
        return $query->where('type', 'expenditure')->sum('amount');
    }

    static public function scopeTotalIncome(Builder $query): int|float
    {
        return $query->where('type', 'income')->sum('amount');
    }


    static public function profitOrLoss($type, $start, $end): int|float
    {
        return self::where('type', $type)
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->sum('amount');
    }

    public function scopeExpenditure(Builder $query): void
    {
        $query->where('type', 'expenditure');
    }

    static function boot()
    {
        parent::boot();

        self::creating(function ($finance) {
            $finance->hotel_id = auth()->user()->hotel_id;
            $finance->code = IdGenerator::generate(['table' => 'finances', 'field' => 'code', 'length' => 6, 'reset_on_prefix_change' => true, 'prefix' => 'HT' . date('d')]);
        });

        // create profile for every user created
        // self::created(function ($user) {
        //     $user->profile()->create();
        //     $user->username = $user->update([
        //         'username' =>  IdGenerator::generate(['table' => 'users', 'field' => 'username', 'length' => 8, 'reset_on_prefix_change' => true, 'prefix' => substr($user->hotel->code, 0, 3) . date('y')])
        //     ]);
        // });
    }
}
