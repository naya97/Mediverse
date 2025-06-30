<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\PaginationTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    use PaginationTrait;

    public function showAllReports(Request $request)
    {
        $auth = $this->auth();
        if ($auth) return $auth;
        $reports = Report::with('patient');
        $response = $this->paginateResponse($request, $reports, 'Reports', function ($report) {
            return [
                'id' => $report->id,
                'patient_first_name' => $report->patient->first_name,
                'patient_last_name' => $report->patient->last_name,
                'type' => $report->type,
                'description' => $report->description,
            ];
        });
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
