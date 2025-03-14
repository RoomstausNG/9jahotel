<?php

namespace App\Policies;

use App\Models\BookingInvoice;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BookingInvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('read booking') || $user->hasRole('super-admin') || $user->hasRole('back-office');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BookingInvoice $booking): bool
    {
        return $user->hasRole('super-admin') || $user->hasRole('back-office');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create booking') || $user->hasRole('super-admin') || $user->hasRole('back-office');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BookingInvoice $booking): bool
    {
        return $user->can('update booking') || $user->can('edit Booking') || $user->hasRole('super-admin') || $user->hasRole('back-office');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BookingInvoice $booking): bool
    {
        return $user->can('delete booking') || $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BookingInvoice $booking): bool
    {
        return $user->can('delete booking') || $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BookingInvoice $booking): bool
    {
        return $user->can('delete booking') || $user->hasRole('super-admin');
    }
}
