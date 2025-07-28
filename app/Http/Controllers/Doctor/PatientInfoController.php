<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Analyse;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\MedicalInfo;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Schedule;
use App\Models\User;
use App\Notifications\AnalyseRequest;
use App\Notifications\AppointmentVisited;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\FirebaseService;
use App\PaginationTrait;

class PatientInfoController extends Controller
{
    protected $firebaseService;
    use PaginationTrait;

    public function __construct(FirebaseService $firebase_service)
    {
        $this->firebaseService = $firebase_service;
    }
    /////
    public function addPrescription(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();
        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }
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
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
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
        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }
        $prescription = Prescription::find($request->id);
        if (!$prescription) {
            return response()->json(['message' => 'prescription not found'], 404);
        }
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
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();
        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }
        $clinic = Clinic::find($doctor->clinic_id);
        if (!$clinic) {
            return response()->json(['message' => 'Clinic not found'], 404);
        }
        $analyse = Analyse::create([
            'name' => $request->name,
            'description' => $request->description,
            'patient_id' => $request->patient_id,
            'clinic_id' => $clinic->id,
            'doctor_id' => $doctor->id
        ]);

        //labtech notification
        $user = User::where('role', 'labtech')->first();
        if ($user->fcm_token) {
            $fullName = $doctor->first_name . ' ' . $doctor->last_name;
            $this->firebaseService->sendNotification(
                $user->fcm_token,
                'New Lab Test Request',
                'A new lab test has been requested by Dr. ' . $fullName,
                ['analyse_id' => $analyse->id]
            );
            $user->notify(new AnalyseRequest([
                'analyse_id' => $analyse->id,
                'doctor_id' => $doctor->id,
                'clinic_id' => $clinic->id,
                'doctor_name' => $doctor->first_name . ' ' . $doctor->last_name,
            ]));
        }

        return response()->json([
            'message' => 'analyse created successfully',
            'data' => $analyse
        ], 201);
    }
    /////
    public function showPatientAnalysis(Request $request) //by status
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $validator = Validator::make($request->all(), [
            'status' => 'string|required',
            'patient_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }
        $analysis = Analyse::where('patient_id', $request->patient_id)
            ->where('status', $request->status)
            ->select(
                'name',
                'description',
                'result_file',
                'result_photo',
                'status',
            );
        $analysis = $this->paginateResponse($request, $analysis, 'analysis');

        return response()->json($analysis, 200);
    }
    /////
    public function showClinics()
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $clinics = Clinic::select('id', 'name', 'numOfDoctors')->get();
        return response()->json($clinics, 200);
    }
    ////
    public function showPatientAnalysisByClinic(Request $request) //by status and clinic
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required',
            'clinic_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }
        $analysis = Analyse::where('patient_id', $request->patient_id)
            ->where('clinic_id', $request->clinic_id)
            ->select(
                'name',
                'description',
                'result_file',
                'result_photo',
                'status',
            );
        $analysis = $this->paginateResponse($request, $analysis, 'analysis');

        return response()->json($analysis, 200);
    }
    /////
    public function addMedicalInfo(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $validator = Validator::make($request->all(), [
            'prescription_id' => 'exists:prescriptions,id',
            'appointment_id' => 'required|exists:appointments,id',
            'symptoms' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'doctorNote' => 'nullable|string',
            'patientNote' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->all()
            ], 422);
        }
        $madicalTnfo = MedicalInfo::create([
            'prescription_id' => $request->prescription_id,
            'appointment_id' => $request->appointment_id,
            'symptoms' => $request->symptoms,
            'diagnosis' => $request->diagnosis,
            'doctorNote' => $request->doctorNote,
            'patientNote' => $request->patientNote,
        ]);
        $appointment = Appointment::with(['patient.user'])->find($request->appointment_id);
        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }
        if ($appointment->parent_id == null) {
            $user = Auth::user();
            $doctor = Doctor::where('user_id', $user->id)->first();
            if (!$doctor) {
                return response()->json(['message' => 'Doctor not found'], 404);
            }
            $doctor->treated = $doctor->treated + 1;
            $doctor->save();
        }

        $patient = Patient::where('id', $appointment->patient_id)->first();

        $appointment->status = 'visited';
        $appointment->save();

        $patient->discount_points += 2;
        $patient->save();

        //patient notification
        if ($patient->parent_id != null) {
            $patient = Patient::where('id', $patient->parent_id)->first();
            $patient = User::where('id', $patient->user_id)->first();
        } else {
            $patient = $appointment->patient->user;
        }
        if (!empty($patient->fcm_token)) {
            $this->firebaseService->sendNotification(
                $patient->fcm_token,
                'Appointment Visited',
                'Your recent appointment has been marked as visited.',
                ['appointment_id' => $appointment->id]
            );
            $patient->notify(new AppointmentVisited([
                'appointment_id' => $appointment->id,
            ]));
        }

        return response()->json([
            'message' => 'Medical information added successfully',
            'data' => $madicalTnfo,
        ], 201);
    }
    /////
    public function showPatientProfile(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }
        $patient = Patient::select('id', 'first_name', 'last_name', 'birth_date', 'gender', 'blood_type', 'address')
            ->where('id', $request->patient_id)
            ->first();

        if (!$patient) {
            return response()->json(['message' => 'patient not found'], 404);
        }

        return response()->json($patient, 200);
    }
    /////
    public function patientsRecord(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $user = Auth::user();

        $doctor = Doctor::where('user_id', $user->id)->first();
        if (!$doctor) return response()->json(['message' => 'Doctor Not Found'], 404);

        $scheduleIds = Schedule::where('doctor_id', $doctor->id)->pluck('id')->toArray();

        $appointments = Appointment::with('patient')->whereIn('schedule_id', $scheduleIds)->pluck('patient_id')->toArray();
        $patients = Patient::whereIn('id', $appointments);

        $response = $this->paginateResponse($request, $patients, 'Patients', function ($patient) {

            return [
                'id' => $patient->id,
                'first_name' => $patient->first_name,
                'last_name' => $patient->last_name,
                'birth_date' => $patient->birth_date,
                'address' => $patient->address,
            ];
        });

        return response()->json($response, 200);
    }
    /////
    public function searchPatient(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();

        if (!$doctor) {
            return response()->json(['message' => 'Doctor Not Found'], 404);
        }

        $name = $request->input('name');
        if (!$name) {
            return response()->json(['message' => 'Name is required'], 400);
        }

        $scheduleIds = Schedule::where('doctor_id', $doctor->id)->pluck('id')->toArray();
        $patientIds = Appointment::whereIn('schedule_id', $scheduleIds)->pluck('patient_id')->toArray();

        $results = Patient::search($name)->get()->filter(function ($patient) use ($patientIds) {
            return in_array($patient->id, $patientIds);
        });

        if ($results->isEmpty()) {
            return response()->json(['message' => 'No patients found'], 404);
        }

        $response = $results->map(function ($patient) {
            return [
                'id' => $patient->id,
                'first_name' => $patient->first_name,
                'last_name' => $patient->last_name,
                'birth_date' => $patient->birth_date,
                'address' => $patient->address,
            ];
        });

        return response()->json(['Patients' => $response], 200);
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
