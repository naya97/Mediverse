<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class ClinicController extends Controller
{

    public function show()
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $clinics = Clinic::all();

        return response()->json($clinics, 200);
    }

    public function showDetails(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $clinic = Clinic::where('id', $request->clinic_id)->first();

        if (!$clinic) return response()->json(['message' => 'clinic not found'], 404);

        $doctors_clinic = Doctor::where('clinic_id', $clinic->id)
            ->select(
                'first_name',
                'last_name',
                'clinic_id',
                'photo',
                'speciality',
                'finalRate',
                'visit_fee',
                'treated',
                'status'
            )
            ->get();

        return response()->json($doctors_clinic, 200);
    }

    public function addClinic(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'photo' => 'image',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }
        if ($request->hasFile('photo')) {
            $path = $request->photo->store('images/clinics', 'public');
        }
        $clinic = Clinic::create([
            'name' => $request->name,
            'photo' => '/storage/' . $path,
        ]);

        return response()->json(['message' => 'created successfully'], 201);
    }

    public function editClinic(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string',
            'photo' => 'image',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        $clinic = Clinic::where('id', $request->clinic_id)->first();

        if (!$clinic) return response()->json(['message' => 'clinic not found'], 404);
        if ($request->hasFile('photo')) {
            if ($clinic->photo) {
                $previousImagePath = public_path($clinic->photo);
                if (File::exists($previousImagePath)) {
                    File::delete($previousImagePath);
                }
            }
            $path = $request->photo->store('images/clinics', 'public');
            $clinic->photo = '/storage/' . $path;
        }
        $clinic->name = $request->name;
        $clinic->save();

        return response()->json(['message' => 'clinic updated successfully'], 200);
    }

    public function removeClinic(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $clinic = Clinic::where('id', $request->clinic_id)->first();
        if (!$clinic) return response()->json(['message' => 'clinic not found'], 404);
        $previousImagePath = public_path($clinic->photo);
        if (File::exists($previousImagePath)) {
            File::delete($previousImagePath);
        }
        $clinic->delete();

        return response()->json(['message' => 'deleted successfully'], 200);
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
