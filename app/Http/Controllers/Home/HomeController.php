<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function showDoctors() {
        $auth = $this->auth();
        if($auth) return $auth;

        $doctors = $this->showAllDoctors();

        // don't show the clinic id (tell the front)
        return response()->json($doctors,200);
    }

    public function searchDoctor(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $results = Doctor::search(($request->name))->get();

        if ($results->isEmpty()) {
            return response()->json(['message' => 'Not Found']);
        }

        $response = [];
        foreach ($results as $result) {
            $response[] = [
                'first_name' => $result->first_name,
                'last_name' => $result->last_name,
                'photo' => $result->photo,
                'clinic_id' => $result->clinic_id,
                'speciality' => $result->speciality,
                'finalRate' => $result->finalRate,
            ];
        }

        return response()->json($response, 200);

    }

    public function showDoctorDetails(Request $request) {
      $auth = $this->auth();
        if($auth) return $auth;
        $doctor = Doctor::where('id',$request->doctor_id)->first();

        $department = Clinic::where('id',$doctor->clinic_id)->select('name')->first();
        $doctor_details = User::where('id',$doctor->user_id)->select('first_name','last_name','phone')->first();

        $response = [
            'first_name' => $doctor_details->first_name,
            'last_name' => $doctor_details->last_name,
            'phone' => $doctor_details->phone,
            'clinic' => $department->name,
            'photo' => $doctor->photo,
            'treated' => $doctor->treated,
            'speciality' => $doctor->speciality,
            'department' => $doctor->department,
            'finalRate' => $doctor->finalRate,
            'visit_fee' => $doctor->visit_fee,
            'excperience' => $doctor->excperience,
            'status' => $doctor->status,
        ];

        return response()->json($response,200);

    }

    public function showClinincDoctors(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $doctors = $this->showAllDoctors();

        $clinic_doctors = [];
        foreach($doctors as $doctor) { 
            if ($doctor->clinic_id == $request->clinic_id) {
                $clinic_doctors[] = $doctor;
            }
        }

        return response()->json($clinic_doctors,200);
    }

    //-------------------------------------------------------------------

    public function auth() {
        $user = Auth::user();
        
        if(!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ],401);
        }
    }

    public function showAllDoctors() {
        $doctors = Doctor::select('photo', 'first_name', 'last_name', 'speciality', 'status', 'finalRate', 'clinic_id')
        ->get();

        return $doctors;
    }
}
