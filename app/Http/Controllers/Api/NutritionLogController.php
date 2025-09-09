<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NutritionLog;
use Illuminate\Http\Request;

class NutritionLogController extends Controller
{
    // Menampilkan semua catatan nutrisi milik user yang login
    public function index(Request $request)
    {
        $query = $request->user()->nutritionLogs();

        // Filter berdasarkan tanggal jika ada parameter ?date=YYYY-MM-DD
        if ($request->has('date')) {
            $query->whereDate('consumed_at', $request->date);
        }

        $logs = $query->latest('consumed_at')->paginate(15);
        return response()->json($logs);
    }

    // Menyimpan catatan nutrisi baru
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'food_name' => 'required|string|max:255',
            'calories' => 'required|integer|min:0',
            'protein_g' => 'required|numeric|min:0',
            'carbs_g' => 'required|numeric|min:0',
            'fat_g' => 'required|numeric|min:0',
            'meal_type' => 'required|in:sarapan,makan_siang,makan_malam,camilan',
            'consumed_at' => 'required|date',
        ]);

        $log = $request->user()->nutritionLogs()->create($validatedData);

        return response()->json($log, 201);
    }

    // Menampilkan detail satu catatan
    public function show(NutritionLog $nutritionLog)
    {
        // Otorisasi: Pastikan log ini milik user yang sedang login
        if ($nutritionLog->user_id !== auth()->id()) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }
        return response()->json($nutritionLog);
    }

    // Mengupdate catatan nutrisi
    public function update(Request $request, NutritionLog $nutritionLog)
    {
        if ($nutritionLog->user_id !== auth()->id()) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $validatedData = $request->validate([
            'food_name' => 'sometimes|required|string|max:255',
            'calories' => 'sometimes|required|integer|min:0',
            // ... (tambahkan validasi lain jika perlu)
        ]);

        $nutritionLog->update($validatedData);
        return response()->json($nutritionLog);
    }

    // Menghapus catatan nutrisi
    public function destroy(NutritionLog $nutritionLog)
    {
        if ($nutritionLog->user_id !== auth()->id()) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $nutritionLog->delete();
        return response()->json(['message' => 'Catatan berhasil dihapus']);
    }
}