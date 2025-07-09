<?php

namespace App\Policies;

use App\Models\Hotel;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class HotelPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read hotel') || $user->hasRole('super-admin') || $user->hasRole('back-office') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Hotel $hotel): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('back-office');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create hotel') || $user->hasRole('super-admin') || $user->hasRole('back-office');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Hotel $hotel): bool
    {
        return $user->can('update hotel') || $user->can('edit hotel') || $user->hasRole('super-admin') || $user->hasRole('back-office') || $user->id == $hotel->created_by;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Hotel $hotel): bool
    {
        return $user->can('delete hotel') || $user->hasRole(['super-admin', 'admin']) || $user->current_role == 'super-admin' || $user->current_role == 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Hotel $hotel): bool
    {
        return $user->can('delete hotel') || $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Hotel $hotel): bool
    {
        return $user->can('delete hotel') || $user->hasRole('super-admin');
    }
}
