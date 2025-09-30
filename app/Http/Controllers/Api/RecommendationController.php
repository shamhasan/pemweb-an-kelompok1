<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RecommendationController extends Controller
{
    /**
     * Menghitung dan mengembalikan rekomendasi kalori harian untuk user yang sedang login.
     */
    public function getCalorieRecommendation()
    {
        $user = Auth::guard('api')->user();

        // Pastikan semua data yang dibutuhkan ada
        if (!$user->gender || !$user->weight_kg || !$user->height_cm || !$user->date_of_birth) {
            return response()->json(['message' => 'Data profil Anda belum lengkap. Harap lengkapi berat, tinggi, jenis kelamin, dan tanggal lahir.'], 422);
        }

        // 1. Hitung Umur dari Tanggal Lahir
        $age = Carbon::parse($user->date_of_birth)->age;

        // 2. Hitung BMR (Basal Metabolic Rate) menggunakan Rumus Harris-Benedict
        $bmr = 0;
        if ($user->gender == 'male') {
            // Rumus untuk pria
            $bmr = 88.362 + (13.397 * $user->weight_kg) + (4.799 * $user->height_cm) - (5.677 * $age);
        } else { // 'female'
            // Rumus untuk wanita
            $bmr = 447.593 + (9.247 * $user->weight_kg) + (3.098 * $user->height_cm) - (4.330 * $age);
        }

        // 3. Tentukan Faktor Aktivitas (Activity Factor)
        $activityFactor = 1.2; // Default untuk 'jarang'
        switch ($user->activity) {
            case 'olahraga_ringan':
                $activityFactor = 1.375;
                break;
            case 'olahraga_sedang':
                $activityFactor = 1.55;
                break;
            case 'olahraga_berat':
                $activityFactor = 1.725;
                break;
            case 'sangat_berat':
                $activityFactor = 1.9;
                break;
        }

        // 4. Hitung TDEE (Total Daily Energy Expenditure) atau Kebutuhan Kalori Harian
        $dailyCalories = $bmr * $activityFactor;

        // 5. Kembalikan hasil dalam format JSON
        return response()->json([
            'message' => 'Rekomendasi kalori berhasil dihitung.',
            'data' => [
                'user_profile' => [
                    'age' => $age,
                    'gender' => $user->gender,
                    'height_cm' => $user->height_cm,
                    'weight_kg' => $user->weight_kg,
                    'activity_level' => $user->activity,
                ],
                'calculation_result' => [
                    'basal_metabolic_rate' => round($bmr),
                    'daily_calorie_intake' => round($dailyCalories)
                ]
            ]
        ]);
    }
}