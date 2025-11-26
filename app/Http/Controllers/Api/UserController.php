<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function getprofile(Request $request)
    {
        return response()->json($request->user());
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female',
            'height_cm' => 'required|integer|min:50|max:300',
            'weight_kg' => 'required|numeric|min:20|max:500',
            'activity' => 'nullable|in:jarang,olahraga_ringan,olahraga_sedang,olahraga_berat,sangat_berat',
            'photo' => 'nullable|image|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->hasFile('photo')) {

            if ($user->photo_url && \Storage::disk('public')->exists($user->photo_url)) {
                \Storage::disk('public')->delete($user->photo_url);
            }

            $path = $request->file('photo')->store('users', 'public');
            $user->photo_url = $path;
        }

        $user->update([
            'name' => $request->name,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'height_cm' => $request->height_cm,
            'weight_kg' => $request->weight_kg,
            'activity' => $request->activity,
            'photo_url' => $user->photo_url,
        ]);

        return response()->json([
            'message' => 'Profile updated',
            'data' => $user
        ]);
    }

}
