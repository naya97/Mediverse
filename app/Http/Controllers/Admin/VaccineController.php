<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vaccine;
use App\PaginationTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class VaccineController extends Controller
{
    use PaginationTrait;

    public function show(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $vaccines = Vaccine::query();

        $paginatedData = $this->paginateResponse($request, $vaccines, 'Vaccines');

        return response()->json($paginatedData, 200);
    }

    public function showDetails(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $vaccine = Vaccine::where('id', $request->vaccine_id)->first();
        if(!$vaccine) return response()->json(['message' => 'vaccine not found'], 404);

        return response()->json($vaccine, 200);
    }

    public function add(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'required|string',
            'age_group' => 'required|string',
            'recommended_doses' => 'required|numeric',
            'price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        $vaccine = Vaccine::create([
            'name' => $request->name,
            'description' => $request->description,
            'age_group' => $request->age_group,
            'recommended_doses' => $request->recommended_doses,
            'price' => $request->price,
        ]);

        return response()->json([
            'message' => 'vaccine added successfully',
            'data' => $vaccine,
        ], 201);
    }

    public function edit(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string',
            'description' => 'nullable|string',
            'age_group' => 'nullable|string',
            'recommended_doses' => 'nullable|numeric',
            'price' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        $vaccine = Vaccine::where('id', $request->vaccine_id)->first();
        if(!$vaccine) return response()->json(['message' => 'vaccine not found'], 404);

        $vaccine->update($request->all());
        $vaccine->save();

        return response()->json([
            'message' => 'vaccine updated successfully',
            'data' => $vaccine,
        ], 200);
    }

    public function remove(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $vaccine = Vaccine::where('id', $request->vaccine_id);
        if(!$vaccine) return response()->json(['message' => 'vaccine not found'], 404);

        $vaccine->delete();

        return response()->json(['message' => 'vaccine deleted successfully'], 200);
    }

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

}
