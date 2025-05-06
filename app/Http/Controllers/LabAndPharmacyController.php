<?php

namespace App\Http\Controllers;

use App\Models\Lab_Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LabAndPharmacyController extends Controller
{
    public function add(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'you have to login/signup again']);
        }
        if ($user->role != 'admin') {
            return response()->json('You do not have permission in this page', 400);
        }

        $validator = Validator::make($request->all(), [
            'is_lab' => 'boolean|required',
            'name' => 'string|required',
            'location' => 'string',
            'start_time' => 'string',
            'finish_time' => 'string',
            'phone' => 'string',
            'latitude' => 'nullable|numeric|between:-180,180',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $place = Lab_Pharmacy::create([
            'is_lab' => $request->is_lab,
            'name' => $request->name,
            'location' => $request->location,
            'start_time' => $request->start_time,
            'finish_time' => $request->finish_time,
            'phone' => $request->phone,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);
        return response()->json($place, 201);
    }
    /////
    public function show(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'you have to login/signup again']);
        }

        $places = Lab_Pharmacy::where('is_lab', $request->is_lab)->get()->all();
        $response = [];
        foreach ($places as $place) {
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
}
