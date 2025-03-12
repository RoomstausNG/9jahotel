<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TeamController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $teams = Team::all();
        return $this->successResponse($teams, 'Teams retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'marketers' => 'array',
            'marketers.*' => 'exists:users,id,distinct'
        ]);

        $team = Team::create(['name' => $data['name']]);

        if (isset($data['marketers'])) {
            $team->marketers()->attach($data['marketers']);
        }

        return $this->successResponse($team, 'Team created successfully along side its marketers');
    }

    /**
     * Display the specified resource.
     */
    public function show(Team $team)
    {
        return $this->successResponse($team, 'Team retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Team $team)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255'
        ]);

        $team->update($data);
        return $this->successResponse($team, 'Team updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team)
    {
        $team->delete();
        return $this->successResponse('Team deleted successfully');
    }


    public function login(Request $request)
    {
        $data =   $request->validate([
            'name' => ['required', 'exists:teams,id'],
            'code' => ['required', 'exists:teams,code'],
        ]);

        $team = Team::where('id', $data['name'])
            ->where('code', $data['code'])
            ->first();

        if (!$team) {
            throw ValidationException::withMessages([
                'code' => ['The team credentials are incorrect. Check your code and try again.'],
            ]);
        }

        // Generate a token for the team
        $token = $team->createToken('team', ['team'])->plainTextToken;

        $res['token'] = $token;
        $res['team'] = $team;
        return $this->successResponse($res, 'Team logged in successfully');
    }

    // Team logout
    public function logout(Request $request)
    {
        $request->user('team')->currentAccessToken()->delete();
        return $this->successResponse('Logged out successfully');
    }
}