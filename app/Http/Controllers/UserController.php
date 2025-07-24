<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    /**
     * Gets users except yourself
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $users = User::where('id', '!=', Auth::user()->id)->get();
        return $this->success($users);
    }
}