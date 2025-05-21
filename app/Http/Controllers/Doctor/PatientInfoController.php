<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Analyse;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Medicine;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PatientInfoController extends Controller
{
    public function addPrescription(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();
        $prescription = Prescription::create([
            'patient_id' => $request->patient_id,
            'doctor_id' => $doctor->id,
        ]);
        return response()->json([
            'message' => 'prescription created successfully',
            'data' => [
                'prescription_id' => $prescription->id,
                'doctor first name' => $doctor->first_name,
                'doctor last name' => $doctor->last_name,
                'doctor sign' => $doctor->sign,
            ],
        ], 201);
    }
    /////
    public function addMedicine(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $validator = Validator::make($request->all(), [
            'name' => 'string|required',
            'dose' => 'string|required',
            'frequency' => 'string|required',
            'strength' => 'string|required',
            'until' => 'string|required',
            'whenToTake' => 'string|required',
            'prescription_id' => 'required',
            'note' => 'string'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $medicine = Medicine::create([
            'name' => $request->name,
            'dose' => $request->dose,
            'frequency' => $request->frequency,
            'strength' => $request->strength,
            'until' => $request->until,
            'whenToTake' => $request->whenToTake,
            'prescription_id' => $request->prescription_id,
            'note' => $request->note
        ]);
        return response()->json([
            'message' => 'created successfully',
            'data' => $medicine
        ], 201);
    }
    /////
    public function completPrescription(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();
        $prescription = Prescription::find($request->id);
        $prescription->note = $request->note;
        $prescription->save();
        return response()->json([
            'message' => 'prescription completed',
            'data' => [
                'doctor first name' => $doctor->first_name,
                'doctor last name' => $doctor->last_name,
                'doctor sign' => $doctor->sign,
                'note' => $request->note,
            ],
        ], 201);
    }
    /////
    public function requestAnalyse(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $validator = Validator::make($request->all(), [
            'name' => 'string|required',
            'description' => 'string',
            'patient_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();
        $clinic = Clinic::find($doctor->clinic_id);
        $analyse = Analyse::create([
            'name' => $request->name,
            'description' => $request->description,
            'patient_id' => $request->patient_id,
            'clinic_id' => $clinic->id,
        ]);
        return response()->json([
            'message' => 'analyse created successfully',
            'data' => $analyse,
        ], 201);
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
        if ($user->role != 'doctor') {
            return response()->json([
                'message' => 'you dont have permission'
            ], 401);
        }
    }
}
