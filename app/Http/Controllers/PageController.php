<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Nnjeim\World\Models\City;
use App\Models\BookingInvoice;
use Nnjeim\World\Models\State;

class PageController extends Controller
{
    public  function homepage()
    {
        $states = State::where('country_id', 160)->get();
        return view('index', compact(['states']));
    }

    public function success($reference)
    {
        if ($reference) {
            $invoice = BookingInvoice::where('reference', $reference)->first();

            return view('success', compact(['invoice']));
        }
    }

    public function term()
    {
        $term = Page::where('key', 'term')->first();
        return view('term', compact(['term']));
    }



    public function hotelByState(State $state)
    {
        $cities = City::where('state_id', $state->id)->pluck('id')->toArray();
        $hotels = Hotel::whereIn('id', $cities)->paginate(24);
    }
}