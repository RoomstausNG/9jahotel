<?php

namespace App\Filament\Customer\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Booking;

class Dashboard extends Page
{
  protected static ?string $navigationIcon = 'heroicon-o-home';
  protected static string $view = 'filament.customer.pages.dashboard';
  protected static ?string $title = 'Dashboard';
  protected static ?string $navigationLabel = 'Dashboard';
  protected static ?string $slug = 'dashboard';

  public $stats = [];

  public function mount(): void
  {
    $customer = Auth::guard('customers')->user();
    $totalBookings = $customer->bookings()->count();
    $activeBookings = $customer->bookings()->where('status', '!=', 'checked_out')->count();
    $totalSpent = $customer->bookings()->sum('amount_paid');

    $this->stats = [
      Stat::make('Total Bookings', $totalBookings),
      Stat::make('Active Bookings', $activeBookings),
      Stat::make('Total Spent', '₦' . number_format($totalSpent)),
    ];
  }
}
