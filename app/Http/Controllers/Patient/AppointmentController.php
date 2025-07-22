<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\MedicalInfo;
use App\Models\Patient;
use App\Models\Prescription;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

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

        if ($request->has('child_id')) {
            $patient = Patient::where('id', $request->child_id)->first();
        } else {
            $patient = Patient::where('user_id', $user->id)->first();
        }
        if (!$patient) return response()->json(['message' => 'Patient Not Found'], 404);

        $appointments = Appointment::with('schedule.doctor')
            ->where('patient_id', $patient->id)
            ->where('status', $request->status)
        ->get();
        if (!$appointments) return response()->json(['message' => 'No Appointments yet'], 404);

        if($request->has('child_id') && $request->has('appointment_type')) {
            $appointments = Appointment::with('schedule.doctor')
            ->where('patient_id', $patient->id)
            ->where('status', $request->status)
            ->where('appointment_type', $request->appointment_type)
            ->get();
            if (!$appointments) return response()->json(['message' => 'No Appointments yet'], 404);
        }

        $response = [];
        foreach ($appointments as $appointment) {
            $doctor = $appointment->schedule->doctor ?? null;

            if ($doctor) {
                $response[] = [
                    'appointment_id' => $appointment->id,
                    'doctor_photo' => $doctor->photo,
                    'doctor_name' => $doctor->first_name . ' ' . $doctor->last_name,
                    'doctor_id' => $doctor->id,
                    'doctor_speciality' => $doctor->speciality,
                    'reservation_date' => $appointment->reservation_date,
                    'reservation_hour' => $appointment->timeSelected,
                    'payment_status' => $appointment->payment_status,
                    'reminder_offset' => $appointment->reminder_offset,
                    'appointment_price' => $appointment->price,
                    'appointment_type' => $appointment->appointment_type,
                ];
            }
        }
        return response()->json($response, 200);
    }

    public function showAppointmentInfo(Request $request)
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
        if ($request->has('child_id')) {
            $patient = Patient::where('id', $request->child_id)->first();
        } else {
            $patient = Patient::where('user_id', $user->id)->first();
        }
        if (!$patient) return response()->json(['message' => 'Patient Not Found'], 404);


        $appointment = Appointment::with('schedule.doctor')
            ->where('id', $request->appointment_id)
            ->where('patient_id', $patient->id)
            ->first();

        if (!$appointment) return response()->json(['message' => 'Appointment Not Found'], 404);

        $doctor = $appointment->schedule->doctor;
        if (!$doctor) return response()->json(['message' => 'Doctor Not Found'], 404);


        $clinic = $doctor->clinic;

        if ($appointment->parent_id == null) $type = 'first time';
        else $type = 'check up';

        $information = [
            'appointment_id' => $appointment->id,
            'clinic_id' => $doctor->clinic_id,
            'clinic_name' => $clinic->name,
            'type' => $type,
            'doctor_photo' => $doctor->photo,
            'doctor_name' => $doctor->first_name . ' ' . $doctor->last_name,
            'doctor_id' => $doctor->id,
            'doctor_speciality' => $doctor->speciality,
            'visit_fee' => $appointment->price,
            'finalRate' => $doctor->finalRate,
            'status' => $appointment->status,
            'reservation_date' => $appointment->reservation_date,
            'reservation_hour' => $appointment->timeSelected,
            'payment_status' => $appointment->paymet_status,
            'reminder_offset' => $appointment->reminder_offset,
            'price' => $appointment->price,
            'appointment_type' => $appointment->appointment_type,
        ];

        return response()->json($information, 200);
    }

    public function showAppointmentResults(Request $request)
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
        if ($request->has('child_id')) {
            $patient = Patient::where('id', $request->child_id)->first();
        } else {
            $patient = Patient::where('user_id', $user->id)->first();
        }
        if (!$patient) return response()->json(['message' => 'Patient Not Found'], 404);


        $appointment = Appointment::with('schedule.doctor')
            ->where('id', $request->appointment_id)
            ->where('patient_id', $patient->id)
            ->first();

        if (!$appointment) return response()->json(['message' => 'Appointment Not Found'], 404);


        $medicalInfo = MedicalInfo::with('prescription.medicines')->where('appointment_id', $appointment->id)->first();
        if (!$medicalInfo) {
            return response()->json([
                'message' => 'medical info not found'
            ], 404);
        }

        $prescription = $medicalInfo->prescription;
        $medicines = [];
        if ($prescription && $prescription->medicines) {
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
        }

        $formattedMedicalInfo = [
            'id' => $medicalInfo->id,
            'diagnosis' => $medicalInfo->diagnosis,
            'doctorNote' => $medicalInfo->doctorNote,
            'patientNote' => $medicalInfo->patientNote,
            'prescription' => $prescription ? [
                'id' => $prescription->id,
                'note' => $prescription->note,
                'medicines' => $medicines,
            ] : null,
        ];

        return response()->json($formattedMedicalInfo, 200);
    }

    public function downloadPrescription(Request $request)
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
        if ($request->has('child_id')) {
            $patient = Patient::where('id', $request->child_id)->first();
        } else {
            $patient = Patient::where('user_id', $user->id)->first();
        }
        if (!$patient) return response()->json(['message' => 'Patient Not Found'], 404);


        $prescription = Prescription::with('medicines')->where('id', $request->prescription_id)->first();
        if (!$prescription) return response()->json(['message' => 'Pres$prescription Not Found'], 404);


        $medicines = $prescription->medicines;

        $doctor = Doctor::with('clinic')->where('id', $prescription->doctor_id)->first();

        if (!$prescription || !$patient || !$doctor) {
            return response()->json(['message' => 'data not found'], 404);
        }

        $pdf = App::make('dompdf.wrapper');


        // $signatureFile = $doctor->sign ?? null;
        // $signatureRelativePath = public_path($signatureFile);
        // $signatureExists =  File::exists($signatureRelativePath);

        $html = view('prescription', [
            'prescription' => $prescription,
            'patient' => $patient,
            'doctor' => $doctor,
            'medicines' => $prescription->medicines,
            // 'signatureRelativePath' => $signatureRelativePath,
            // 'signatureExists' => $signatureExists, 
        ])->render();

        $pdf = Pdf::loadView('prescription', compact(
            'doctor',
            'patient',
            'prescription',
            'medicines',
            // 'signatureRelativePath',
            // 'signatureExists'
        ));

        $pdfContent = $pdf->output();
        $base64 = base64_encode($pdfContent);

        return response()->json([
            'filename' => 'prescription_' . $prescription->id . '.pdf',
            'pdf_base64' => $base64
        ]);
    }

    public function setReminder(Request $request)
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
        $validator = Validator::make($request->all(), [
            'reminder_offset' => 'required|integer|min:1|max:24',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->all()
            ], 400);
        }

        $patient = Patient::where('user_id', $user->id)->first();
        if (!$patient) return response()->json(['message' => 'Patient Not Found'], 404);

        $appointment = Appointment::where('id', $request->appointment_id)->first();
        if (!$appointment) return response()->json(['message' => 'Appointment Not Fount'], 404);

        $appointment->reminder_offset = $request->reminder_offset;
        $appointment->save();

        return response()->json(['message' => 'Your Reminder set successfully'], 200);
    }
}
