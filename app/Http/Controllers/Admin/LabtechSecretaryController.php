<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LabtechSecretaryController extends Controller
{
    public function showEmployee(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        if($request->is_secretary == 1) {
            $secretaries = User::where('role', 'secretary')->get();
            return response()->json($secretaries, 200);
        }
        else {
            $labtechs = User::where('role', 'labtech')->get();
            return response()->json($labtechs, 200);
        }
    }

    public function addEmployee(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'first_name' => 'string|required',
            'last_name' => 'string|required',
            'email' => 'string|email|max:255|required|unique:users',
            'phone' => 'required|phone:SY|unique:users',
            'password' => ['required', 'string', 'min:8', 'regex:/[0-9]/', 'regex:/[a-z]/', 'regex:/[A-Z]/',],
        ]);

        if ($validator->fails()) {
            return response()->json([
               'message' =>  $validator->errors()->all()
            ], 400);
        }

        if($request->is_secretary == 1) {
            $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->get('password')),
            'role' => 'secretary',
            ]);

         return response()->json($user->first_name.' added successfully', 201);
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->get('password')),
            'role' => 'labtech',
        ]);

        return response()->json($user->first_name.' added successfully', 201);


    }

    public function editEmployee(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $validator = Validator::make($request->all(), [
            'first_name' => 'string',
            'last_name' => 'string',
            'email' => 'string|email|max:255|unique:users',
            'phone' => 'phone:SY|unique:users',
            'password' => [ 'string', 'min:8', 'regex:/[0-9]/', 'regex:/[a-z]/', 'regex:/[A-Z]/',],
        ]);

        if ($validator->fails()) {
            return response()->json([
               'message' =>  $validator->errors()->all()
            ], 400);
        }

        $user = User::where('id',$request->user_id)->first();

        if(!$user) return response(['message'=>'user not found'],404);

        if($request->is_secretary == 1) {
            $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->get('password')),
            ]);

            $user->save();

         return response()->json('edited successfully', 200);
        }

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->get('password')),
        ]);

        $user->save();

        return response()->json('edited successfully', 200);

    }

    public function removeEmployee(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $user = User::where('id',$request->user_id)->first();
        if(!$user) return response(['message'=>'user not found'],404);

        $user->delete();

        return response()->json('deleted successfully', 200);
    }

    public function auth() {
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
