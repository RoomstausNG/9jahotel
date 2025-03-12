<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function create(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'max:30'],
            'number' => ['required', 'numeric', 'max:10', 'min:1'],
            'body' => ['nullable', 'max:148'],
            'hotel_id' => ['required', 'exists:hotels,id']
        ], [
            'number.required' => 'The rating must be present and a number from 1 to 10',
            'number.numeric' => 'The rating must be a number from 1 to 10',
            'number.max' => 'The rating must not be a greater than 10',
            'hote_id.required' => 'The hotel id must not be empty'
        ]);

        $customer = Auth::guard('customer')->user();

        $rating = $customer->ratings()->create($data);

        return response()->json(['message' => 'Hotel rating submitted successfully', 'data' => $rating]);
    }
}