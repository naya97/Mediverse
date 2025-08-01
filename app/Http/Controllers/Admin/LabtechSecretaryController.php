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

        if ($request->has('is_secretary')) {
            if($request->is_secretary == 1) {
                $secretaries = User::where('role', 'secretary')->get();
                return response()->json($secretaries, 200);
            }
            else if($request->is_secretary == 0) {
                $labtechs = User::where('role', 'labtech')->get();
                return response()->json($labtechs, 200);
            }
            else  {
                $employees = User::whereIn('role', ['labtech', 'secretary'])->get();
                return response()->json($employees, 200);

            }
        }else {
            $employees = User::whereIn('role', ['secretary', 'labtech'])->get();
        }

        return response()->json($employees, 200);
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
        ],[
            'phone.phone' => 'enter a valid syrian phone number' ,
            'phone.unique' => 'this phone has already been taken'
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
            'email' => [
            'string',
            'email',
            'max:255',
            'unique:users,email,' . $request->user_id,
            ],
            'phone' => [
            'phone:SY',
            'unique:users,phone,' . $request->user_id,
            ],
            'password' => [ 'string', 'min:8', 'regex:/[0-9]/', 'regex:/[a-z]/', 'regex:/[A-Z]/',],
        ],[
            'phone.phone' => 'enter a valid syrian phone number' ,
            'phone.unique' => 'this phone has already been taken'
        ]);

        if ($validator->fails()) {
            return response()->json([
               'message' =>  $validator->errors()->all()
            ], 400);
        }

        $user = User::where('id',$request->user_id)->first();

        if(!$user) return response(['message'=>'user not found'],404);

        if($user->role == 'secretary') {
            $user->update([
                'first_name' => $request->first_name ?? $user->first_name,
                'last_name' => $request->last_name ?? $user->last_name,
                'email' => $request->email ?? $user->email,
                'phone' => $request->phone ?? $user->phone,
                'password' => Hash::make($request->get('password')) ?? $user->password,
            ]);

            return response()->json('edited successfully', 200);
        }

        $user->update([
            'first_name' => $request->first_name ?? $user->first_name,
            'last_name' => $request->last_name ?? $user->last_name,
            'email' => $request->email ?? $user->email,
            'phone' => $request->phone ?? $user->phone,
            'password' => Hash::make($request->get('password')) ?? $user->password,
        ]);

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

    public function showEmployeeByID(Request $request) {
        $auth = $this->auth();
        if($auth) return $auth;

        $user = User::where('id',$request->user_id)->whereIn('role', ['labtech', 'secretary'])->first();
        if(!$user) return response(['message'=>'employee not found'],404);

        return response()->json($user, 200);
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
