<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public function makeReport(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $user = Auth::user();
        $patient = Patient::where('user_id', $user->id)->first();
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:Technical issue,Offense,Privacy violation,Poor cleanliness,Bad experience,Billing issue,Mismanagement,Misdiagnosis,Unclear instructions,other',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->all()
            ], 400);
        }

        $report = Report::create([
            'patient_id' => $patient->id,
            'type' => $request->type,
            'description' => $request->description,
        ]);
        return response()->json(['message' => 'report created successfully', 'report' => $report], 201);
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
        if ($user->role != 'patient') {
            return response()->json(['message' => 'You do not have permission in this page'], 400);
        }
    }
}
