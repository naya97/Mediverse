<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Refund;
use Stripe\Stripe;
use App\CancelAppointmentsTrait;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\User;
use App\PaginationTrait;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    use CancelAppointmentsTrait;
    use PaginationTrait;

    public function filteringAppointmentByDoctor(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:doctors,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        if ($request->has('date')) {
            $date = Carbon::createFromFormat('m-Y', $request->date);
            $startOfMonth = $date->startOfMonth()->toDateString();
            $endOfMonth = $date->endOfMonth()->toDateString();

            $appointments = Appointment::with('patient.user', 'schedule.doctor')
                ->whereBetween('reservation_date', [$startOfMonth, $endOfMonth])
                ->whereHas('schedule', function ($query) use ($request) {
                    $query->where('doctor_id', $request->doctor_id);
                })->get();
        } else {
            $appointments = Appointment::with('patient.user', 'schedule.doctor')
                ->whereHas('schedule', function ($query) use ($request) {
                    $query->where('doctor_id', $request->doctor_id);
                })->get();
        }

        $response = [];

        foreach ($appointments as $appointment) {
            $patient = $appointment->patient;
            $doctor = $appointment->schedule->doctor;
            $patientUser = $appointment->patient->user;
            $doctorUser = $appointment->schedule->doctor->user;

            $response[] = [
                'id' => $appointment->id,
                'patient' => $patient->first_name . ' ' . $patient->last_name,
                'patient_phone' => $patientUser ? $patientUser->phone : null,
                'doctor' => $doctor->first_name . ' ' . $doctor->last_name,
                'doctor_id' => $doctor->id,
                'doctor_phone' => $doctorUser ? $doctorUser->phone : null,
                'doctor_photo' => $doctor->photo,
                'visit_fee' => $doctor->visit_fee,
                'expected_price' => $appointment->expected_price,
                'paid_price' => $appointment->paid_price,
                'reservation_date' => $appointment->reservation_date,
                'payment_status' => $appointment->payment_status,
                'reservation_hour' => $appointment->timeSelected,
                'status' => $appointment->status,
                'queue_number' => $appointment->queue_number,
                'discount_points' => $appointment->discount_points,
            ];
        }


        return response()->json($response, 200);
    }

    public function filteringAppointmentByStatus(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        if ($request->status == 'today') {
            $today = now()->format('Y-m-d');
            $appointments = Appointment::with('patient.user', 'schedule.doctor.user')
                ->where('status', $request->status)
                ->where('reservation_date', $today)
                ->get();
        } else {
            if ($request->has('date')) {
                $date = Carbon::createFromFormat('m-Y', $request->date);
                $startOfMonth = $date->startOfMonth()->toDateString();
                $endOfMonth = $date->endOfMonth()->toDateString();

                $appointments = Appointment::with('patient.user', 'schedule.doctor.user')
                    ->where('status', $request->status)
                    ->whereBetween('reservation_date', [$startOfMonth, $endOfMonth])
                    ->get();
            } else {
                $appointments = Appointment::with('patient.user', 'schedule.doctor.user')
                    ->where('status', $request->status)
                    ->get();
            }
        }

        $response = [];
        foreach ($appointments as $appointment) {
            $patient = $appointment->patient;
            $doctor = $appointment->schedule->doctor;
            $patientUser = $appointment->patient->user;
            $doctorUser = $appointment->schedule->doctor->user;

            $response[] = [
                'id' => $appointment->id,
                'patient' => $patient->first_name . ' ' . $patient->last_name,
                'patient_phone' => $patientUser ? $patientUser->phone : null,
                'doctor' => $doctor->first_name . ' ' . $doctor->last_name,
                'doctor_id' => $doctor->id,
                'doctor_phone' => $doctorUser ? $doctorUser->phone : null,
                'doctor_photo' => $doctor->photo,
                'visit_fee' => $doctor->visit_fee,
                'expected_price' => $appointment->expected_price,
                'paid_price' => $appointment->paid_price,
                'reservation_date' => $appointment->reservation_date,
                'payment_status' => $appointment->payment_status,
                'reservation_hour' => $appointment->timeSelected,
                'status' => $appointment->status,
                'queue_number' => $appointment->queue_number,
                'discount_points' => $appointment->discount_points,
            ];
        }



        return response()->json($response, 200);
    }

    public function filteringAppointmentByDoctorStatus(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'status' => 'required',
            'doctor_id' => 'required|exists:doctors,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        if ($request->status == 'today') {
            $today = now()->format('Y-m-d');
            $appointments = Appointment::with('patient.user', 'schedule.doctor.user')
                ->where('status', $request->status)
                ->whereHas('schedule', function ($query) use ($request) {
                    $query->where('doctor_id', $request->doctor_id);
                })
                ->where('reservation_date', $today)
                ->get();
        } else {
            if ($request->has('date')) {
                $date = Carbon::createFromFormat('m-Y', $request->date);
                $startOfMonth = $date->startOfMonth()->toDateString();
                $endOfMonth = $date->endOfMonth()->toDateString();

                $appointments = Appointment::with('patient.user', 'schedule.doctor.user')
                    ->where('status', $request->status)
                    ->whereBetween('reservation_date', [$startOfMonth, $endOfMonth])
                    ->whereHas('schedule', function ($query) use ($request) {
                        $query->where('doctor_id', $request->doctor_id);
                    })
                    ->get();
            } else {
                $appointments = Appointment::with('patient.user', 'schedule.doctor.user')
                    ->where('status', $request->status)
                    ->whereHas('schedule', function ($query) use ($request) {
                        $query->where('doctor_id', $request->doctor_id);
                    })
                    ->get();
            }
        }

        $response = [];
        foreach ($appointments as $appointment) {
            $patient = $appointment->patient;
            $doctor = $appointment->schedule->doctor;
            $patientUser = $appointment->patient->user;
            $doctorUser = $appointment->schedule->doctor->user;

            $response[] = [
                'id' => $appointment->id,
                'patient' => $patient->first_name . ' ' . $patient->last_name,
                'patient_phone' => $patientUser ? $patientUser->phone : null,
                'doctor' => $doctor->first_name . ' ' . $doctor->last_name,
                'doctor_id' => $doctor->id,
                'doctor_phone' => $doctorUser ? $doctorUser->phone : null,
                'doctor_photo' => $doctor->photo,
                'visit_fee' => $doctor->visit_fee,
                'expected_price' => $appointment->expected_price,
                'paid_price' => $appointment->paid_price,
                'reservation_date' => $appointment->reservation_date,
                'payment_status' => $appointment->payment_status,
                'reservation_hour' => $appointment->timeSelected,
                'status' => $appointment->status,
                'queue_number' => $appointment->queue_number,
                'discount_points' => $appointment->discount_points,
            ];
        }



        return response()->json($response, 200);
    }


    public function filteringAppointmentByDate(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'date' => ['required', 'date_format:m-Y'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        $date = Carbon::createFromFormat('m-Y', $request->date);
        $startOfMonth = $date->startOfMonth()->toDateString();
        $endOfMonth = $date->endOfMonth()->toDateString();

        $appointments = Appointment::whereBetween('reservation_date', [$startOfMonth, $endOfMonth])
            ->orderBy('reservation_date', 'asc')
            ->get();
        $response = [];

        foreach ($appointments as $appointment) {
            $patient = $appointment->patient;
            $doctor = $appointment->schedule->doctor;
            $patientUser = $appointment->patient->user;
            $doctorUser = $appointment->schedule->doctor->user;

            $response[] = [
                'id' => $appointment->id,
                'patient' => $patient->first_name . ' ' . $patient->last_name,
                'patient_phone' => $patientUser ? $patientUser->phone : null,
                'doctor' => $doctor->first_name . ' ' . $doctor->last_name,
                'doctor_id' => $doctor->id,
                'doctor_phone' => $doctorUser ? $doctorUser->phone : null,
                'doctor_photo' => $doctor->photo,
                'visit_fee' => $doctor->visit_fee,
                'expected_price' => $appointment->expected_price,
                'paid_price' => $appointment->paid_price,
                'reservation_date' => $appointment->reservation_date,
                'payment_status' => $appointment->payment_status,
                'reservation_hour' => $appointment->timeSelected,
                'status' => $appointment->status,
                'queue_number' => $appointment->queue_number,
                'discount_points' => $appointment->discount_points,
            ];
        }

        return response()->json($response, 200);
    }

    public function filteringAppointmentByClinic(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'clinic_id' => ['required', 'exists:clinics,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' =>  $validator->errors()->all()
            ], 400);
        }

        if ($request->has('status')) {
            if ($request->status == 'today') {
                $today = now()->format('Y-m-d');
                $appointments = Appointment::with('patient.user', 'schedule.doctor.user', 'schedule.clinic')
                    ->where('status', $request->status)
                    ->where('reservation_date', $today)
                    ->whereHas('schedule', function ($query) use ($request) {
                        $query->where('clinic_id', $request->clinic_id);
                    })
                    ->get();
            } else {
                $appointments = Appointment::with('patient.user', 'schedule.doctor.user', 'schedule.doctor.clinic')
                    ->where('status', $request->status)
                    ->whereHas('schedule.doctor', function ($query) use ($request) {
                        $query->where('clinic_id', $request->clinic_id);
                    })
                    ->get();
            }
        } else {
            if ($request->has('date')) {
                $date = Carbon::createFromFormat('m-Y', $request->date);
                $startOfMonth = $date->startOfMonth()->toDateString();
                $endOfMonth = $date->endOfMonth()->toDateString();

                $appointments = Appointment::with('patient.user', 'schedule.doctor.user', 'schedule.doctor.clinic')
                    ->whereBetween('reservation_date', [$startOfMonth, $endOfMonth])
                    ->whereHas('schedule.doctor', function ($query) use ($request) {
                        $query->where('clinic_id', $request->clinic_id);
                    })
                    ->get();
            } else {
                $appointments = Appointment::with('patient.user', 'schedule.doctor.user', 'schedule.doctor.clinic')
                    ->whereHas('schedule.doctor', function ($query) use ($request) {
                        $query->where('clinic_id', $request->clinic_id);
                    })
                    ->get();
            }
        }

        $response = [];
        foreach ($appointments as $appointment) {
            $patient = $appointment->patient;
            $doctor = $appointment->schedule->doctor;
            $patientUser = $patient->user;
            $doctorUser = $doctor->user;

            $response[] = [
                'id' => $appointment->id,
                'patient' => $patient->first_name . ' ' . $patient->last_name,
                'patient_phone' => $patientUser ? $patientUser->phone : null,
                'doctor' => $doctor->first_name . ' ' . $doctor->last_name,
                'doctor_id' => $doctor->id,
                'doctor_phone' => $doctorUser ? $doctorUser->phone : null,
                'doctor_photo' => $doctor->photo,
                'visit_fee' => $doctor->visit_fee,
                'expected_price' => $appointment->expected_price,
                'paid_price' => $appointment->paid_price,
                'reservation_date' => $appointment->reservation_date,
                'payment_status' => $appointment->payment_status,
                'reservation_hour' => $appointment->timeSelected,
                'status' => $appointment->status,
                'queue_number' => $appointment->queue_number,
                'discount_points' => $appointment->discount_points,
            ];
        }

        return response()->json($response, 200);
    }

    public function editSchedule(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        return $this->editDoctorSchedule($request, $request->doctor_id);
    }

    public function cancelAppointment(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        return $this->cancelAnAppointment($request);
    }

    public function showCanceledAppointments(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $notificationsQuery = DatabaseNotification::where('type', 'App\Notifications\AppointmentCancelled')
            ->where('created_at', '>=', now()->subDays(7));

        $response = $this->paginateResponse($request, $notificationsQuery, 'CanceledAppointments', function ($notification) {
            $patient = User::find($notification->notifiable_id);

            $read = $notification->is_read == 0 ? 'not seen' : 'seen';

            return [
                'patient_first_name' => $patient->first_name,
                'patient_last_name' => $patient->last_name,
                'phone' => $patient->phone,
                'read' => $read,
                'appointment_date' => $notification->data['reservation_date'],
                'appointment_time' => $notification->data['timeSelected'],
                'doctor_name' => $notification->data['doctor_name'],
            ];
        });

        return response()->json($response, 200);
    }


    public function showClinics(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $clinicsQuery = Clinic::select('name', 'numOfDoctors', 'photo', 'money');

        $usePagination = $request->has('page') || $request->has('size');

        if ($usePagination) {
            $response = $this->paginateResponse($request, $clinicsQuery, 'Clinics', function ($clinic) {
                return [
                    'name' => $clinic->name,
                    'numOfDoctors' => $clinic->numOfDoctors,
                    'photo' => $clinic->photo,
                    'money' => $clinic->money,
                ];
            });

            return $response;
        } else {
            $clinics = $clinicsQuery->get();
            $data = [];

            foreach ($clinics as $clinic) {
                $data[] = [
                    'name' => $clinic->name,
                    'numOfDoctors' => $clinic->numOfDoctors,
                    'photo' => $clinic->photo,
                    'money' => $clinic->money,
                ];
            }

            return response()->json($data, 200);
        }
    }



    public function showDoctors(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $doctorsQuery = Doctor::select('id', 'photo', 'first_name', 'last_name', 'speciality', 'status', 'finalRate', 'clinic_id', 'average_visit_duration');

        $usePagination = $request->has('page') || $request->has('size');

        if ($usePagination) {
            $response = $this->paginateResponse($request, $doctorsQuery, 'Doctors', function ($doctor) {
                return [
                    'id' => $doctor->id,
                    'photo' => $doctor->photo,
                    'first_name' => $doctor->first_name,
                    'last_name' => $doctor->last_name,
                    'speciality' => $doctor->speciality,
                    'status' => $doctor->status,
                    'finalRate' => $doctor->finalRate,
                    'clinic_id' => $doctor->clinic_id,
                    'average_visit_duration' => $doctor->average_visit_duration,
                ];
            });

            return $response;
        } else {
            $doctors = $doctorsQuery->get();
            $data = [];

            foreach ($doctors as $doctor) {
                $data[] = [
                    'id' => $doctor->id,
                    'photo' => $doctor->photo,
                    'first_name' => $doctor->first_name,
                    'last_name' => $doctor->last_name,
                    'speciality' => $doctor->speciality,
                    'status' => $doctor->status,
                    'finalRate' => $doctor->finalRate,
                    'clinic_id' => $doctor->clinic_id,
                    'average_visit_duration' => $doctor->average_visit_duration,
                ];
            }

            return response()->json($data, 200);
        }
    }

    public function showClinicDoctors(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;

        $doctorsQuery = Doctor::where('clinic_id', $request->clinic_id)
            ->select(
                'id',
                'first_name',
                'last_name',
                'user_id',
                'clinic_id',
                'photo',
                'speciality',
                'professional_title',
                'finalRate',
                'average_visit_duration',
                'visit_fee',
                'sign',
                'experience',
                'treated',
                'status',
                'booking_type',
                'created_at',
                'updated_at'
            );

        $usePagination = $request->has('page') || $request->has('size');

        if ($usePagination) {
            $response = $this->paginateResponse($request, $doctorsQuery, 'Doctors', function ($doctor) {
                return [
                    'id' => $doctor->id,
                    'first_name' => $doctor->first_name,
                    'last_name' => $doctor->last_name,
                    'user_id' => $doctor->user_id,
                    'clinic_id' => $doctor->clinic_id,
                    'photo' => $doctor->photo,
                    'speciality' => $doctor->speciality,
                    'professional_title' => $doctor->professional_title,
                    'finalRate' => $doctor->finalRate,
                    'average_visit_duration' => $doctor->average_visit_duration,
                    'visit_fee' => $doctor->visit_fee,
                    'sign' => $doctor->sign,
                    'experience' => $doctor->experience,
                    'treated' => $doctor->treated,
                    'status' => $doctor->status,
                    'booking_type' => $doctor->booking_type,
                    'created_at' => $doctor->created_at,
                    'updated_at' => $doctor->updated_at,
                ];
            });
            return $response;
        } else {
            $doctors = $doctorsQuery->get();
            $data = [];

            foreach ($doctors as $doctor) {
                $data[] = [
                    'id' => $doctor->id,
                    'first_name' => $doctor->first_name,
                    'last_name' => $doctor->last_name,
                    'user_id' => $doctor->user_id,
                    'clinic_id' => $doctor->clinic_id,
                    'photo' => $doctor->photo,
                    'speciality' => $doctor->speciality,
                    'professional_title' => $doctor->professional_title,
                    'finalRate' => $doctor->finalRate,
                    'average_visit_duration' => $doctor->average_visit_duration,
                    'visit_fee' => $doctor->visit_fee,
                    'sign' => $doctor->sign,
                    'experience' => $doctor->experience,
                    'treated' => $doctor->treated,
                    'status' => $doctor->status,
                    'booking_type' => $doctor->booking_type,
                    'created_at' => $doctor->created_at,
                    'updated_at' => $doctor->updated_at,
                ];
            }

            return response()->json($data, 200);
        }
    }




    public function showAllDoctors(Request $request)
    {
        $doctorsQuery = Doctor::select('id', 'photo', 'first_name', 'last_name', 'speciality', 'status', 'finalRate', 'clinic_id', 'average_visit_duration');

        $usePagination = $request->has('page') || $request->has('size');

        if ($usePagination) {
            $response = $this->paginateResponse($request, $doctorsQuery, 'Doctors', function ($doctor) {
                return [
                    'id' => $doctor->id,
                    'photo' => $doctor->photo,
                    'first_name' => $doctor->first_name,
                    'last_name' => $doctor->last_name,
                    'speciality' => $doctor->speciality,
                    'status' => $doctor->status,
                    'finalRate' => $doctor->finalRate,
                    'clinic_id' => $doctor->clinic_id,
                    'average_visit_duration' => $doctor->average_visit_duration,
                ];
            });

            return $response;
        } else {
            $doctors = $doctorsQuery->get();
            $data = [];

            foreach ($doctors as $doctor) {
                $data[] = [
                    'id' => $doctor->id,
                    'photo' => $doctor->photo,
                    'first_name' => $doctor->first_name,
                    'last_name' => $doctor->last_name,
                    'speciality' => $doctor->speciality,
                    'status' => $doctor->status,
                    'finalRate' => $doctor->finalRate,
                    'clinic_id' => $doctor->clinic_id,
                    'average_visit_duration' => $doctor->average_visit_duration,
                ];
            }

            return response()->json($data, 200);
        }
    }


    public function auth()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ], 401);
        }
        if ($user->role != 'secretary') {
            return response()->json('You do not have permission in this page', 400);
        }
    }
}
