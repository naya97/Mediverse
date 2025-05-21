<?php

namespace App\Http\Controllers\Patient\Medical_Analysis;

use App\Http\Controllers\Controller;
use App\Models\Analyse;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MedicalAnalysisController extends Controller
{
    public function showAnalysis() {
        $user = Auth::user(); // 

         //check the auth
         if(!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ],401);
        }

        if(!$user->role == 'patient') {
            return response()->json([
                'message' => 'you dont have permission'
            ],401);
        }

        $patient = Patient::where('user_id',$user->id)->first();

        $analysis = Analyse::where('patient_id', $patient->id)
            ->select(
                'name',
                'description',
                'result_file',
                'result_photo',
            )
        ->get();

        return response()->json($analysis , 200);

    }


    public function addAnalysis(Request $request) {
       $user = Auth::user(); // 

         //check the auth
         if(!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ],401);
        }

        if(!$user->role == 'patient') {
            return response()->json([
                'message' => 'you dont have permission'
            ],401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|required',
            'description' => 'string',
            'result_file' => 'file|required_without:result_photo',
            'result_photo' => 'image|required_without:result_file',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $patient = Patient::where('user_id',$user->id)->first();

        if($request->hasFile('result_file')){
            $file_path = $request->result_file->store('files/patients/analysis', 'public');
            $result_file = '/storage/' . $file_path;
        }

         if($request->hasFile('result_photo')){
            $photo_path = $request->result_photo->store('files/patients/analysis', 'public');
            $result_photo = '/storage/' . $photo_path;
        }

        $analyse = Analyse::create([
            'patient_id' => $patient->id,
            'name' => $request->name,
            'description' => $request->description,
            'result_file' => $result_file,
            'result_photo' => $result_photo,
            'status' => 'finished',
        ]);

        return response()->json($analyse, 201);

    }

    public function deleteAnalysis(Request $request) {
        $user = Auth::user(); // 

         //check the auth
         if(!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ],401);
        }

        if(!$user->role == 'patient') {
            return response()->json([
                'message' => 'you dont have permission'
            ],401);
        }

        $analyse = Analyse::where('id', $request->analyse_id)->first();
        $analyse->delete();

        return response()->json('deleted successfully', 200);
    }
}
