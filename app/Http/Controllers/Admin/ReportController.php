<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function showAllReports()
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $reports = Report::with('patient')->get();
        $response = [];
        foreach ($reports as $report) {
            $response[] = [
                'id' => $report->id,
                'patient first name' => $report->patient->first_name,
                'patient last name' => $report->patient->last_name,
                'type' => $report->type,
                'descriptipon' => $report->description,
            ];
        }
        return response()->json($response, 200);
    }
    /////
    public function showReport(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $report = Report::with('patient')->find($request->report_id);
        if (!$report) {
            return response()->json(['message' => 'report not found'], 404);
        }
        $response = [
            'id' => $report->id,
            'patient first name' => $report->patient->first_name,
            'patient last name' => $report->patient->last_name,
            'type' => $report->type,
            'descriptipon' => $report->description,
        ];
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
        if ($user->role != 'admin') {
            return response()->json('You do not have permission in this page', 400);
        }
    }
}
