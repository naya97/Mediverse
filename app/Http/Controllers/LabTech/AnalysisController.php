<?php

namespace App\Http\Controllers\LabTech;

use App\Http\Controllers\Controller;
use App\Models\Analyse;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Notifications\DoctorAnalyseResult;
use App\Notifications\PatientAnalyseResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\FirebaseService;

class AnalysisController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebase_service)
    {
        $this->firebaseService = $firebase_service;
    }
    /////
    public function showClinics()
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $clinics = Clinic::select('id', 'name', 'numOfDoctors')->get();
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
            'price' => 'required'
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
            'price' => $request->price,
            'payment_status' => 'paid',
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
                'payment status' => $analyse->payment_status,
                'price' => $analyse->price,
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
            'payment status' => $analyse->payment_status,
            'price' => $analyse->price,
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
        $analyse = Analyse::with(['patient.user'])->find($request->id);
        if (!$analyse) {
            return response()->json(['error' => 'Analyse not found'], 404);
        }
        if ($analyse->payment_status == 'pending') {
            return response()->json(['message' => 'this patient did not pay for this analyse yet'], 402);
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

        //patient notification
        $patient = $analyse->patient->user;
        if ($patient->fcm_token) {
            $this->firebaseService->sendNotification(
                $patient->fcm_token,
                'Analysis Result Available',
                'Your test result is now available. Please check the app.',
                ['analyse_id' => $analyse->id]
            );
            $patient->notify(new PatientAnalyseResult([
                'analyse_id' => $analyse->id,
            ]));
        }
        //doctor notification
        if ($analyse->doctor_id != null) {
            $doctor = Doctor::with(['user'])->find($analyse->doctor_id);
            $user = $doctor->user;
            if ($user->fcm_token) {
                $fullName = $analyse->patient->user->first_name . ' ' . $analyse->patient->user->last_name;
                $this->firebaseService->sendNotification(
                    $user->fcm_token,
                    'Analysis Result Available',
                    'The test result for patient ' . $fullName . ' is now available.',
                    ['analyse_id' => $analyse->id]
                );
                $user->notify(new DoctorAnalyseResult([
                    'analyse_id' => $analyse->id,
                    'patient_id' => $analyse->patient->id,
                    'patient_name' => $fullName,
                ]));
            }
        }

        return response()->json(['message' => 'added successfully'], 200);
    }
    /////

    public function searchAnalyseByName(Request $request)
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
        $search = $this->searchAnalyse($request->name, $request->status);
        if ($search) return $search;
    }
    /////
    public function searchAnalyseByPatientNum(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|numeric',
            'status' => 'required|string|in:pending,finished'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 422);
        }
        $patient = Patient::find($request->patient_id);
        if (!$patient) {
            return response()->json(['message' => 'patient not found'], 404);
        }
        $search = $this->searchAnalyse($request->patient_id, $request->status);
        if ($search) return $search;
    }
    /////
    public function searchAnalyse($type, $status)
    {
        $results = Analyse::search($type)
            ->where('status', $status)
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
                'payment status' => $analyse->payment_status,
                'price' => $analyse->price,
            ];
        });

        return response()->json($response, 200);
    }
    /////
    public function addBill(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $validator = Validator::make($request->all(), [
            'analyse_id' => 'required|exists:analyses,id',
            'price' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 422);
        }
        $analyse = Analyse::where('status', 'pending')
            ->where('payment_status', 'pending')
            ->where('id', $request->analyse_id)
            ->first();

        if (!$analyse) return response()->json(['message' => 'analyse not found'], 404);

        $analyse->price = $request->price;
        $analyse->payment_status = 'paid';
        $analyse->save();

        return response()->json('successfully payed', 200);
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
