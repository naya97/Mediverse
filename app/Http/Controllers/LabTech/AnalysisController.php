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
    public function showClinics()
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $clinics = Clinic::select('id', 'name', 'numOfDoctors', 'location')->get();
        return response()->json($clinics, 200);
    }
    
    public function addAnalyse(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $validator = Validator::make($request->all(), [
            'name' => 'string|required',
            'description' => 'string',
            'clinic_id' => 'required',
            'patient_number' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 422);
        }
        $patient = Patient::find($request->patient_number);
        if (!$patient) {
            return response()->json(['message' => 'patient is not registered in the application'], 404);
        }
        $analyse = Analyse::create([
            'name' => $request->name,
            'description' => $request->description,
            'patient_id' => $request->patient_number,
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

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,finished'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->all()
            ], 422);
        }

        $analysis = Analyse::with(['clinic', 'patient'])
            ->where('status', $request->status)
            ->get();

        $response = $analysis->map(function ($analyse) {
            return [
                'id' => $analyse->id,
                'name' => $analyse->name,
                'description' => $analyse->description,
                'result_file' => $analyse->result_file,
                'result_photo' => $analyse->result_photo,
                'clinic' => $analyse->clinic->name ?? null,
                'patient_first_name' => $analyse->patient->first_name ?? null,
                'patient_last_name' => $analyse->patient->last_name ?? null,
                'patient_number' => $analyse->patient_id,
            ];
        });

        return response()->json($response, 200);
    }


    /////
    public function showAnalyse(Request $request)
    {
        if ($auth = $this->auth()) {
            return $auth;
        }

        $analyse = Analyse::with(['clinic', 'patient'])->find($request->id);

        if (!$analyse) {
            return response()->json(['error' => 'Analyse not found'], 404);
        }
        $response = [
            'id' => $analyse->id,
            'name' => $analyse->name,
            'description' => $analyse->description,
            'result_file' => $analyse->result_file,
            'result_photo' => $analyse->result_photo,
            'clinic' => $analyse->clinic->name ?? null,
            'patient_first_name' => $analyse->patient->first_name ?? null,
            'patient_last_name' => $analyse->patient->last_name ?? null,
            'patient_number' => $analyse->patient_id,
        ];

        return response()->json($response);
    }

    /////
    public function addAnalyseResult(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:analyses,id',
            'result_photo' => 'nullable|image|required_without:result_file',
            'result_file' => 'nullable|file|required_without:result_photo',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 422);
        }
        $analyse = Analyse::find($request->id);
        if (!$analyse) {
            return response()->json(['error' => 'Analyse not found'], 404);
        }
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

    public function searchAnalyse(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'status' => 'required|string|in:pending,finished'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 422);
        }
        $results = Analyse::search($request->name)
            ->where('status', $request->status)
            ->get();
        $results->load(['clinic', 'patient']);

        if ($results->isEmpty()) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        $response = $results->map(function ($analyse) {
            return [
                'id' => $analyse->id,
                'name' => $analyse->name,
                'description' => $analyse->description,
                'result_file' => $analyse->result_file,
                'result_photo' => $analyse->result_photo,
                'clinic' => $analyse->clinic->name ?? null,
                'patient_first_name' => $analyse->patient->first_name ?? null,
                'patient_last_name' => $analyse->patient->last_name ?? null,
                'patient_number' => $analyse->patient_id,
            ];
        });

        return response()->json($response, 200);
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
