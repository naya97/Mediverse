<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DiscountController extends Controller
{
    // public function addDiscount(Request $request)
    // {
    //     $auth = $this->auth();
    //     if ($auth) return $auth;

    //     $validator = Validator::make($request->all(), [
    //         'company' => 'required|string',
    //         'discount_code' => 'required|unique:discounts,discount_code',
    //         'discount_rate' => 'required|string',
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json([
    //             'message' =>  $validator->errors()->all()
    //         ], 400);
    //     }

    //     $discount = Discount::create([
    //         'company' => $request->company,
    //         'discount_code' => $request->discount_code,
    //         'discount_rate' => $request->discount_rate,
    //     ]);

    //     return response()->json([
    //         'message' => 'discount created successfully',
    //         'date' => $discount,
    //     ], 201);
    // }
    /////
    // public function deleteDiscount(Request $request)
    // {
    //     $auth = $this->auth();
    //     if ($auth) return $auth;
    //     $discount = Discount::find($request->discount_id);
    //     if (!$discount) return response()->json(['message' => 'discount not found'], 404);
    //     $discount->delete();
    //     return response()->json([
    //         'message' => 'discount deleted successfully',
    //     ], 200);
    // }
    /////
    // public function editDiscount(Request $request)
    // {
    //     $auth = $this->auth();
    //     if ($auth) return $auth;

    //     $discount = Discount::find($request->discount_id);
    //     if (!$discount) return response()->json(['message' => 'discount not found'], 404);

    //     $validator = Validator::make($request->all(), [
    //         'company' => 'string',
    //         'discount_code' => 'unique:discounts,discount_code,' . $discount->id,
    //         'discount_rate' => 'string',
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json([
    //             'message' =>  $validator->errors()->all()
    //         ], 400);
    //     }

    //     $discount->update($request->all());
    //     $discount->save();

    //     return response()->json([
    //         'message' => 'discount updated successfully',
    //         'date' => $discount,
    //     ], 200);
    // }
    /////
    // public function showDiscounts()
    // {
    //     $auth = $this->auth();
    //     if ($auth) return $auth;

    //     $discounts = Discount::select('company', 'discount_code', 'discount_rate')->get();

    //     return response()->json($discounts, 200);
    // }
    /////
    // public function auth()
    // {
    //     $user = Auth::user();
    //     if (!$user) {
    //         return response()->json([
    //             'message' => 'unauthorized'
    //         ], 401);
    //     }
    //     if ($user->role != 'admin') {
    //         return response()->json('You do not have permission in this page', 400);
    //     }
    // }
}
