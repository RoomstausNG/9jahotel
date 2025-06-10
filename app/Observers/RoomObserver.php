<?php

namespace App\Observers;

use App\Models\Room;
use App\Services\CacheResponseService;
use Illuminate\Contracts\Queue\ShouldQueue;

class RoomObserver implements ShouldQueue
{
  /**
   * Handle the Room "created" event.
   */
  public function created(Room $room): void
  {
    CacheResponseService::clearRoomCache();
    CacheResponseService::clearHotelCache($room->hotel_id);
  }

  /**
   * Handle the Room "updated" event.
   */
  public function updated(Room $room): void
  {
    CacheResponseService::clearRoomCache($room->id);
    CacheResponseService::clearHotelCache($room->hotel_id);
  }

  /**
   * Handle the Room "deleted" event.
   */
  public function deleted(Room $room): void
  {
    CacheResponseService::clearRoomCache($room->id);
    CacheResponseService::clearHotelCache($room->hotel_id);
  }

  /**
   * Handle the Room "restored" event.
   */
  public function restored(Room $room): void
  {
    CacheResponseService::clearRoomCache($room->id);
    CacheResponseService::clearHotelCache($room->hotel_id);
  }

  /**
   * Handle the Room "force deleted" event.
   */
  public function forceDeleted(Room $room): void
  {
    CacheResponseService::clearRoomCache($room->id);
    CacheResponseService::clearHotelCache($room->hotel_id);
  }
}
