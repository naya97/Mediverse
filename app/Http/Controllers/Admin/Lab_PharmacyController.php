<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lab_Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class Lab_PharmacyController extends Controller
{
    public function add(Request $request)
    {
        $validation = $this->validation($request);
        if ($validation) return $validation;
        $auth = $this->auth();
        if ($auth) return $auth;
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
    ////
    public function update(Request $request)
    {
        $validation = $this->validation($request);
        if ($validation) return $validation;
        $auth = $this->auth();
        if ($auth) return $auth;
        $place = Lab_Pharmacy::where('id', $request->id)->first();
        if (!$place) {
            return response()->json(['message' => 'place not found'], 404);
        }
        $place->update($request->all());
        $place = Lab_Pharmacy::find($request->id);
        return response()->json(
            [
                'data' => $place,
                'message' => 'Updated successfully'
            ],
            200
        );
    }
    /////
    public function delete(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $place = Lab_Pharmacy::where('id', $request->id)->first();
        if (!$place) {
            return response()->json(['message' => 'This place is no longer exist!'], 404);
        }
        $place->delete();
        return response()->json(['message' => 'Deleted successfully'], 200);
    }
    /////
    public function auth()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ], 401);
        }
        if ($user->role != 'admin') {
            return response()->json('You do not have permission in this page', 400);
        }
    }
    ////
    public function validation($request)
    {
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
    }
}
