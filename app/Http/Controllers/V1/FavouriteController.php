<?php

namespace App\Http\Controllers\V1;

use App\Models\Hotel;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\FavouriteResource;
use App\Models\Favourite;

class FavouriteController extends Controller
{
    use ApiResponse;

    public function toggleFavourite(Request $request, Hotel $hotel)
    {
        $customer =  $request->user();

        $favourite = $customer->favourites()->where('hotel_id', $hotel->id)->first();
        if ($favourite) {
            $favourite = Favourite::where('hotel_id', $hotel->id)->where('customer_id', $customer->id)->first();

            $done = $favourite->delete();

            return $this->successResponse(null, 'Hotel Removed from favourites');
        }

        $favourite =  $customer->favourites()->create(['hotel_id' => $hotel->id]);
        return $this->successResponse($favourite, 'Hotel added to favourites');
    }

    public function favourites(Request $request)
    {
        $customer =  $request->user();
        $favourites = $customer->favourites()->paginate(24);

        return FavouriteResource::collection($favourites);
    }
}