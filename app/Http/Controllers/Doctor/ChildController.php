<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ChildRecord;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\VaccinationRecord;
use App\Models\Vaccine;
use App\PaginationTrait;
use Google\Service\CloudSourceRepositories\Repo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ChildController extends Controller
{
    use PaginationTrait;

    // public function showChildrenRecords(Request $request) {
    //     $user = Auth::user();

    //     $auth = $this->auth();
    //     if($auth) return $auth; // show only the childs that belongs to this dr

    //     $doctor = Doctor::where('user_id', $user->id)->first();
    //     if(!$doctor) return response()->json(['message' => 'doctor not found'], 404);

    //     $childs = ChildRecord::with('patient.parent')->where('doctor_id', $doctor->id);

    //     $response = $this->paginateResponse($request, $childs, 'Children', function($data) {
    //         $child = $data->patient;

    //         return [
    //             'child_id' => $child->id,
    //             'record_id' => $data->id,
    //             'child_first_name' => $child->first_name,
    //             'child_last_name' => $child->last_name,
    //             'child_date_birth' => $child->date_birth ? : null,
    //             'last_visit_date' =>$data->last_visit_date ? : null,
    //             'height_cm' =>$data->height_cm ? : null,
    //             'weight_kg' =>$data->weight_kg ? : null,
    //             'head_circumference_cm' =>$data->head_circumference_cm ? : null,
    //             'growth_notes' =>$data->growth_notes ? : null,
    //             'developmental_observations' =>$data->developmental_observations ? : null,
    //             'allergies' =>$data->allergies ? : null,
    //             'doctor_notes' =>$data->doctor_notes ? : null,
    //             'feeding_type' =>$data->feeding_type ? : null,
    //         ];
    //     });

    //     return response()->json($response, 200);

    // }

    public function showChildRecord(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $child = ChildRecord::where('child_id', $request->child_id)->first();
        if(!$child) return response()->json(['message' => 'record not found'], 404);

        return response()->json($child, 200);
    }

    public function addChildRecords(Request $request) {
        $user = Auth::user();

        $auth = $this->auth();
        if($auth) return $auth;

        $doctor = Doctor::where('user_id', $user->id)->first();
        if(!$doctor) return response()->json(['message' => 'doctor not found'], 404);

        $validator = Validator::make($request->all(), [
            'child_id' => 'required|exists:patients,id',
            // 'last_visit_date' => 'nullable|date',
            'next_visit_date' => 'nullable|date',
            'height_cm' => 'required|numeric',
            'weight_kg' => 'required|numeric',
            'head_circumference_cm' => 'required|numeric',
            'growth_notes' => 'nullable|string',
            'developmental_observations' => 'required|string',
            'allergies' => 'required|string',
            'doctor_notes' => 'nullable|string',
            'feeding_type' => ['nullable', Rule::in(['natural', 'formula', 'mixed'])] ,
        ]);


        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        $childRecord = ChildRecord::where('child_id', $request->child_id)->first(); 
        if($childRecord) return response()->json(['message' => 'this child have a record'], 400);

        $lastAppointment = Appointment::where('patient_id', $request->child_id)
        ->orderBy('reservation_date', 'desc')
        ->first();
        if(!$lastAppointment) return response()->json(['message' => 'There is no appointment for this patient'], 404);

        $record = ChildRecord::create([
            'child_id' => $request->child_id,
            'doctor_id' => $doctor->id,
            'last_visit_date' => $lastAppointment->reservation_date, //
            'next_visit_date' => $request->next_visit_date,
            'height_cm' => $request->height_cm,
            'weight_kg' => $request->weight_kg,
            'head_circumference_cm' => $request->head_circumference_cm,
            'growth_notes' => $request->growth_notes,
            'developmental_observations' => $request->developmental_observations,
            'allergies' => $request->allergies,
            'doctor_notes' => $request->doctor_notes,
            'feeding_type' => $request->feeding_type,
        ]);

        return response()->json([
            'message' => 'record added successfully',
            'data' => $record,
        ],200);

    }

    public function editChildRecords(Request $request) { // should send a notification to patient when edit
        $auth = $this->auth();
        if($auth) return $auth;

        $record = ChildRecord::find($request->record_id);
        if (!$record) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            // 'last_visit_date' => 'nullable|date',
            'next_visit_date' => 'nullable|date',
            'height_cm' => 'nullable|numeric',
            'weight_kg' => 'nullable|numeric',
            'head_circumference_cm' => 'nullable|numeric',
            'growth_notes' => 'nullable|string',
            'developmental_observations' => 'nullable|string',
            'allergies' => 'nullable|string',
            'doctor_notes' => 'nullable|string',
            'feeding_type' => ['nullable', Rule::in(['natural', 'formula', 'mixed'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->all()
            ], 400);
        }

        $fields = [
            // 'last_visit_date',
            'next_visit_date',
            'height_cm',
            'weight_kg',
            'head_circumference_cm',
            'growth_notes',
            'developmental_observations',
            'allergies',
            'doctor_notes',
            'feeding_type',
        ];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $record->$field = $request->input($field);
            }
        }

        $lastAppointment = Appointment::where('patient_id', $record->child_id)
        ->orderBy('reservation_date', 'desc')
        ->first();
        if(!$lastAppointment) return response()->json(['message' => 'There is no appointment for this patient'], 404);

        $record->last_visit_date = $lastAppointment->reservation_date;

        $record->save();

        return response()->json([
            'message' => 'Record updated successfully',
            'data' => $record,
        ], 200);

    }

    public function showVaccines(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $vaccines = Vaccine::query();

        $response = $this->paginateResponse($request, $vaccines, 'Vaccines');

        return response()->json($response, 200);

    }

    public function showVaccineRecords(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $vaccinesRecords = VaccinationRecord::with('vaccine', 'patient')->where('child_id', $request->child_id);


        $response = $this->paginateResponse($request, $vaccinesRecords, 'Vaccination Records', function($vaccineRecord) {
            return [
                'id' => $vaccineRecord->id,
                'vaccine_id' => $vaccineRecord->vaccine_id,
                'vaccine_name' => $vaccineRecord->vaccine->name,
                'child_id' => $vaccineRecord->child_id,
                'child_first_name' => $vaccineRecord->patient->first_name,
                'child_last_name' => $vaccineRecord->patient->last_name,
                'appointment_id' =>$vaccineRecord->appointment_id ?? null,
                'dose_number' =>$vaccineRecord->dose_number ?? null,
                'notes' =>$vaccineRecord->notes ?? null,
                'isTaken' =>$vaccineRecord->isTaken,
                'when_to_take' =>$vaccineRecord->when_to_take,
                'recommended' =>$vaccineRecord->recommended,
                'next_vaccine_date' =>$vaccineRecord->next_vaccine_date ?? null,
            ];
        });

        return response()->json($response, 200);
    }

    public function showVaccineRecordsDetails(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $vaccineRecord = VaccinationRecord::find($request->vaccination_record_id);
        if(!$vaccineRecord) return response()->json(['message' => 'Vaccination Record Not Found'], 404);

        return response()->json($vaccineRecord, 200);
    }

    public function editVaccineRecordInfo(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $vaccinationRecord = VaccinationRecord::where('id', $request->record_id)->first();
        if(!$vaccinationRecord) return response()->json(['message' => 'vaccination record not found'], 404);

        $appointment = Appointment::where('id', $vaccinationRecord->appointment_id)->first();
        if(!$appointment) return response()->json(['message' => 'there is no appointment for this record'], 404);

        $vaccinationRecord->update([
            'dose_number' => $request->dose_number,
            'notes' => $request->notes ? : null,
            'isTaken' => $request->isTaken,
            'next_vaccine_date' => $request->next_vaccine_date ? : null,
        ]);
        
        $vaccinationRecord->save();

        $appointment->status = 'visited';
        $appointment->save();

        return response()->json($vaccinationRecord, 200);
    }

    public function auth() {
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
