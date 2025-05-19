<?php

namespace App\Http\Controllers;

use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PharmacyController extends Controller
{
    public function show(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ], 401);
        }

        $pharmacy = Pharmacy::get()->all();
        $response = [];
        foreach ($pharmacy as $place) {
            $response[] = [
                'id' => $place->id,
                'name' => $place->name,
                'start_time' => $place->start_time,
                'finish_time' => $place->finish_time,
                'phone' => $place->phone,
                'latitude' => $place->latitude,
                'longitude' => $place->longitude,
                'location' => $place->location,

            ];
        }
        return response()->json($response, 200);
    }
    //////search by name or location
    public function search(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ], 401);
        }

        $results = Pharmacy::search(($request->name))->get();
        if ($results->isEmpty()) {
            return response()->json(['message' => 'Not Found']);
        }
        $response = [];
        foreach ($results as $result) {
            $response[] = [
                'id' => $result->id,
                'name' => $result->name,
                'start_time' => $result->start_time,
                'finish_time' => $result->finish_time,
                'phone' => $result->phone,
                'latitude' => $result->latitude,
                'longitude' => $result->longitude,
                'location' => $result->location,

            ];
        }
        return response()->json($response, 200);
    }
}
