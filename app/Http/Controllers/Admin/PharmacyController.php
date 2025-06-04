<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\PharmacyTrait;

class PharmacyController extends Controller
{
    use PharmacyTrait;
    public function add(Request $request)
    {
        $validation = $this->validation($request);
        if ($validation) return $validation;
        $auth = $this->auth();
        if ($auth) return $auth;
        $pharmacy = Pharmacy::create([
            'name' => $request->name,
            'location' => $request->location,
            'start_time' => $request->start_time,
            'finish_time' => $request->finish_time,
            'phone' => $request->phone,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);
        return response()->json($pharmacy, 201);
    }
    ////
    public function update(Request $request)
    {
        $validation = $this->validation($request);
        if ($validation) return $validation;
        $auth = $this->auth();
        if ($auth) return $auth;
        $pharmacy = Pharmacy::where('id', $request->id)->first();
        if (!$pharmacy) {
            return response()->json(['message' => 'pharmacy not found'], 404);
        }
        $pharmacy->update($request->all());
        $pharmacy = Pharmacy::find($request->id);
        return response()->json(
            [
                'data' => $pharmacy,
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
        $pharmacy = Pharmacy::where('id', $request->id)->first();
        if (!$pharmacy) {
            return response()->json(['message' => 'This pharmacy is no longer exist!'], 404);
        }
        $pharmacy->delete();
        return response()->json(['message' => 'Deleted successfully'], 200);
    }
    /////
    public function showAllPharmacies(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        return $this->getAllPharmacies($request);
    }
    /////
    public function searchPharmacy(Request $request) //by name
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        return $this->searchPharmacyByName($request);
    }
    /////
    public function getPharmacyById(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        return $this->getPharmacy($request);
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
            'name' => 'string|required',
            'location' => 'string',
            'start_time' => 'string',
            'finish_time' => 'string',
            'phone' => 'phone:SY',
            'latitude' => 'nullable|numeric|between:-180,180',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 422);
        }
    }
}
