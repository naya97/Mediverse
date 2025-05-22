<?php

namespace App\Http\Controllers\LabTech;

use App\Http\Controllers\Controller;
use App\Models\Analyse;
use App\Models\Clinic;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class AnalysisController extends Controller
{
    public function addAnalyse(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $validator = Validator::make($request->all(), [
            'name' => 'string|required',
            'description' => 'string',
            'patientFirstName' => 'string|required',
            'patientLastName' => 'string|required',
            'clinic_id' => 'required',
        ]);
       if ($validator->fails()) {
            return response()->json([
               'message' =>  $validator->errors()->all()
            ], 422);
        }
        $patient = Patient::where('first_name', $request->patientFirstName)->where('last_name', $request->patientLastName)->first();
        if (!$patient) {
            return response()->json(['message' => 'You are not registered in the application'], 404);
        }
        $analyse = Analyse::create([
            'name' => $request->name,
            'description' => $request->description,
            'patient_id' => $patient->id,
            'clinic_id' => $request->clinic_id,
        ]);
        return response()->json([
            'message' => 'analyse created successfully',
            'data' => $analyse,
        ], 201);
    }
    /////
    public function showAllAnalysis(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $analysis = Analyse::where('status', $request->status)->get()->all();
        $response = [];
        foreach ($analysis as $analyse) {
            $clinic = Clinic::find($analyse->clinic_id);
            $patient = Patient::find($analyse->patient_id);
            $response[] = [
                'id' => $analyse->id,
                'name' => $analyse->name,
                'description' => $analyse->description,
                'result_file' => $analyse->result_file,
                'result_photo' => $analyse->result_photo,
                'clinic' => $clinic->name,
                'patient firt name' => $patient->first_name,
                'patient last name' => $patient->last_name,
            ];
        }
        return response()->json($response, 200);
    }

    /////
    public function showAnalyse(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $analyse = Analyse::find($request->id);
        $clinic = Clinic::find($analyse->clinic_id);
        $patient = Patient::find($analyse->patient_id);
        $response = [
            'id' => $analyse->id,
            'name' => $analyse->name,
            'description' => $analyse->description,
            'result_file' => $analyse->result_file,
            'result_photo' => $analyse->result_photo,
            'clinic' => $clinic->name,
            'patient firt name' => $patient->first_name,
            'patient last name' => $patient->last_name,
        ];
        return response()->json($response, 200);
    }
    /////
    public function addAnalyseResult(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $validator = Validator::make($request->all(), [
            'result_photo' => 'image|required_without:result_file',
            'result_file' => 'file|required_without:result_photo',
        ]);
       if ($validator->fails()) {
            return response()->json([
               'message' =>  $validator->errors()->all()
            ], 422);
        }
        $analyse = Analyse::find($request->id);
        if ($request->hasFile('result_photo')) {
            $path1 = $request->result_photo->store('images/patients/analysis', 'public');
            $analyse->result_photo = '/storage/' . $path1;
        }
        if ($request->hasFile('result_file')) {
            $path2 = $request->result_file->store('files/patients/analysis', 'public');
            $analyse->result_file = '/storage/' . $path2;
        }
        $analyse->status = 'finished';
        $analyse->save();
        return response()->json(['message' => 'added successfully'], 200);
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
        if ($user->role != 'labtech') {
            return response()->json([
                'message' => 'you dont have permission'
            ], 401);
        }
    }
}
