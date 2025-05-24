<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\MedicalInfo;
use App\Models\Patient;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function showAppointment(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ], 401);
        }
        if ($user->role != 'patient') {
            return response()->json([
                'message' => 'you dont have permission'
            ], 401);
        }
        $patient = Patient::where('user_id', $user->id)->first();

        $appointments = Appointment::with('schedule.doctor')
            ->where('patient_id', $patient->id)
            ->where('status', $request->status)
            ->get();
    
        foreach ($appointments as $appointment) {
            $doctor = $appointment->schedule->doctor ?? null;

            if ($doctor) {
                $response[] = [
                    'id' => $appointment->id,
                    'doctor_photo' => $doctor->photo,
                    'doctor_name' => $doctor->first_name . ' ' . $doctor->last_name,
                    'doctor_speciality' => $doctor->speciality,
                    'reservation_date' => $appointment->reservation_date,
                    'reservation_hour' => $appointment->timeSelected,
                ];
            }   
        }
        return response()->json($response, 200);
    }

    public function showAppointmentInfo(Request $request) {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ], 401);
        }
        if ($user->role != 'patient') {
            return response()->json([
                'message' => 'you dont have permission'
            ], 401);
        }
        $patient = Patient::where('user_id', $user->id)->first();

        $appointment = Appointment::with('schedule.doctor')
            ->where('id', $request->appointment_id)
            ->where('patient_id', $patient->id)
        ->first();

        $doctor = $appointment->schedule->doctor;
        $clinic = $doctor->clinic;

        if ($appointment->parent_id == null) $type = 'first time';
        else $type = 'check up';

        $information = [
            'id' => $doctor->id,
            'clinic_id' => $doctor->clinic_id ,
            'clinic_name'=> $clinic->name ,
            'type' => $type,
            'doctor_photo' => $doctor->photo,
            'doctor_name' => $doctor->first_name . ' ' . $doctor->last_name,
            'doctor_speciality' => $doctor->speciality,
            'visit_fee' => $doctor->visit_fee,
            'finalRate' => $doctor->finalRate,
            'status' => $appointment->status,
            'reservation_date' => $appointment->timeSelected,
            'reservation_hour' => $appointment->reservation_date,
        ];

        return response()->json($information, 200);
    }

    public function showAppointmentResults(Request $request) {

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ], 401);
        }
        if ($user->role != 'patient') {
            return response()->json([
                'message' => 'you dont have permission'
            ], 401);
        }
        $patient = Patient::where('user_id', $user->id)->first();

        $appointment = Appointment::with('schedule.doctor')
            ->where('id', $request->appointment_id)
            ->where('patient_id', $patient->id)
        ->first();

        $medicalInfo = MedicalInfo::with('prescription.medicines')->where('appointment_id', $appointment->id)->first();
        $prescription = $medicalInfo->prescription;
        
        $medicines = $prescription->medicines->map(function ($medicine) {
            return [
                'id' => $medicine->id,
                'name' => $medicine->name,
                'dose' => $medicine->dose,
                'frequency' => $medicine->frequency,
                'strength' => $medicine->strength,
                'until' => $medicine->until,
                'whenToTake' => $medicine->whenToTake,
                'note' => $medicine->note,
            ];
        });

        $formattedMedicalInfo = [
            'id' => $medicalInfo->id,
            'diagnosis' => $medicalInfo->diagnosis,
            'doctorNote' => $medicalInfo->doctorNote,
            'patientNote' => $medicalInfo->patientNote,
            'prescription' => [
                'id' => $prescription->id,
                'note' => $prescription->note,
                'medicines' => $medicines,
            ],
        ];

        return response()->json($formattedMedicalInfo, 200);
    }


}
