<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AuthTrait;

class DoctorAuthController extends Controller
{
    use AuthTrait;

    public function doctorLogin(Request $request)
    {
        return $this->login($request, 'doctor');
    }
    /////
    public function doctorLogout()
    {
        return $this->logout();
    }
    /////
    public function doctorSaveFcmToken(Request $request)
    {
        return $this->saveFcmToken($request, 'doctor');
    }
}
