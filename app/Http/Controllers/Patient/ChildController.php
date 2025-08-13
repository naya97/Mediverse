<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\ChildRecord;
use App\Models\Patient;
use App\Models\VaccinationRecord;
use App\PaginationTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChildController extends Controller
{
    use PaginationTrait;

    public function showVaccinationRecords(Request $request) {
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

        $parent = Patient::where('user_id', $user->id)->first();
        if(!$parent) return response()->json(['message' => 'patient not found'], 404);

        $child = Patient::where('id', $request->child_id)->first();
        if(!$child) return response()->json(['message' => 'child not found'], 404);

        $vaccinationRecords = VaccinationRecord::with(['vaccine', 'appointment'])->where('child_id', $child->id);

        if($request->has('recommended')) {
            $vaccinationRecords = VaccinationRecord::with(['vaccine', 'appointment'])
            ->where('child_id', $child->id)
            ->where('recommended', $request->recommended);
        }

        $response = $this->paginateResponse($request, $vaccinationRecords, 'VaccinationRecords', function($vaccineRecord){
            return [
                'id' => $vaccineRecord->id,
                'vaccine_id' => $vaccineRecord->vaccine_id ?? null , 
                'vaccine_name' => $vaccineRecord->vaccine->name ?? 'vaccination removed',
                'appointment_id' => $vaccineRecord->appointment_id ? : null,
                'dose' => $vaccineRecord->dose,
                'isTaken' => $vaccineRecord->isTaken,
                'when_to_take' => $vaccineRecord->when_to_take,
                'recommended' => $vaccineRecord->recommended,
                'price' => $vaccineRecord->vaccine->price,
            ];
        });

        return response()->json($response, 200);
    }

    public function showVaccinationRecordDetails(Request $request) {
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

        $record = VaccinationRecord::with(['vaccine', 'appointment.schedule.doctor'])
        ->where('id', $request->record_id)
        ->first();
        if(!$record) return response()->json(['message' => 'record not found'], 404);


        $response = [];

        $response = [
            'id' => $record->id,
            'vaccine_id' => $record->vaccine_id ?? null ,
            'vaccine_name' =>$record->vaccine->name ?? null,
            'vaccine_description' =>$record->vaccine->description ?? null,
            'vaccine_age_group' =>$record->vaccine->age_group ?? null,
            'vaccine_recommended_doses' =>$record->vaccine->recommended_doses ?? null,
            'vaccine_price' =>$record->vaccine->price ?? 0,
            'appointment_id' => $record->appointment_id ?? null,
            'appointment_price' => $record->appointment?->price ? : 0,
            'appointment_payment_status' => $record->appointment?->payment_status ? : null,
            'appointment_reservation_date' => $record->appointment?->reservation_date ? : null ,
            'doctor_id' => $record->appointment?->schedule->doctor->id ? : null,
            'doctor_first_name' => $record->appointment?->schedule->doctor->first_name ? : null,
            'doctor_last_name' => $record->appointment?->schedule->doctor->last_name ? : null,
            'dose_number' => $record->dose_number,
            'notes' => $record->notes,
            'isTaken' => $record->isTaken,
            'when_to_take' => $record->when_to_take,
            'recommended' => $record->recommended,
            'next_vaccine_date' => $record->next_vaccine_date ? : null,
        ];

        return response()->json($response, 200);
    }

    public function editVaccinationRecord(Request $request) {
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

        $record = VaccinationRecord::where('id', $request->record_id)->first();
        if(!$record) return response()->json(['message' => 'record not found'], 404);

        $record->isTaken = $request->isTaken;
        $record->save();

        return response()->json(['message' => 'edited successfully'], 200);
    }

    public function deleteVaccinationRecord(Request $request)  {
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

        $record = VaccinationRecord::where('id', $request->record_id)->first();
        if(!$record) return response()->json(['message' => 'record not found'], 404);

        $record->delete();
        
        return response()->json(['message' => 'deleted successfully'], 200);

    }

    public function showChildRecord(Request $request) {
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

        $parent = Patient::where('user_id', $user->id)->first();
        if(!$parent) {
            return response()->json(['message' => 'Parent not found'], 404);
        }

        $child = Patient::where('id', $request->child_id)->first();
        if(!$child) return response()->json(['message' => 'child not found'], 404);


        $child_record = ChildRecord::where('child_id', $child->id)->first();
        if(!$child_record) return response()->json(['message' => 'there is no record for your child yet'], 404);

        return response()->json($child_record, 200);

    }
}
