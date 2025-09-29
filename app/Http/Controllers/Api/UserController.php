<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //getprofile
    public function getprofile(Request $request){
        $user = $request->user();
        return response()->json($user);
    }

    //update profile
    public function updateProfile(Request $request){
        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'date_of_birth' => 'required|date|before_or_equal:today',
            'gender' => 'required|in:male,female',
            'height_cm' => 'required|integer|min:50|max:300',
            'weight_kg' => 'required|numeric|min:20|max:500',
            'role'=>'nullable|in:user,admin',
            'activity' => 'nullable|in:jarang,olahraga_ringan,olahraga_sedang,olahraga_berat,sangat_berat',
        ]);

        $user->update($validator->validated());
        return response()->json([
            'message' => 'Profile berhasil diupdate',
            'data' => $user
        ], 200);
    }
}
