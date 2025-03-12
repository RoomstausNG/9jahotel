<?php

namespace App\Http\Controllers\API;

use App\Models\CityCopy;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use Nnjeim\World\Models\Country;
use Nnjeim\World\Models\State;

class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return CityResource::collection(CityCopy::where('name', 'LIKE', "%{$request->city}%")->paginate(50));
    }


    public function cities(Request $request)
    {
        // return  ["demo"];
        return CityResource::collection(CityCopy::has('hotels')->where('name', 'LIKE', "%{$request->city}%")->paginate(50));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function nigeriaStates(Request $request)
    {
        return CityResource::collection(State::select('id', 'name')->where('country_id', 160)->where('name', 'LIKE', "%{$request->state}%")->paginate(20));
    }

    public function states(Request $request)
    {
        if (!empty($request->country)) {
            return CityResource::collection(State::select('id', 'name')->where('country_id', $request->country)->orWhere('country_id', 160)->where('name', 'LIKE', "%{$request->state}%")->paginate(20));
        } else {
            return CityResource::collection(State::select('id', 'name')->orWhere('country_id', 160)->where('name', 'LIKE', "%{$request->state}%")->paginate(20));
        }
    }

    public function countries(Request $request)
    {
        return CityResource::collection(Country::select('id', 'name')->where('name', 'LIKE', "%{$request->name}%")->orWhere('id', 160)->paginate(20));
    }
}