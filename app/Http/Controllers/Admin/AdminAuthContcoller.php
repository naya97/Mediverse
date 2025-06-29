<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AuthTrait;

class AdminAuthContcoller extends Controller
{
    use AuthTrait;

    public function adminLogin(Request $request)
    {
        return $this->login($request, 'admin');
    }
    /////
    public function adminLogout()
    {
        return $this->logout();
    }
    /////
    public function adminSaveFcmToken(Request $request)
    {
        return $this->saveFcmToken($request, 'admin');
    }
}
