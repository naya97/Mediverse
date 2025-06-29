<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AuthTrait;

class SecretaryAuthController extends Controller
{
    use AuthTrait;

    public function secretaryLogin(Request $request)
    {
        return $this->login($request, 'secretary');
    }
    /////
    public function secretaryLogout()
    {
        return $this->logout();
    }
    /////
    public function secretarySaveFcmToken(Request $request)
    {
        return $this->saveFcmToken($request, 'secretary');
    }
}
