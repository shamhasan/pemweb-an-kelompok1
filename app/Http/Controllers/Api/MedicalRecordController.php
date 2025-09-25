<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicalRecordController extends Controller
{
    /**
     * Menampilkan semua riwayat kesehatan milik user yang sedang login.
     */
    public function index()
    {
        $records = Auth::user()->medicalRecords()->orderBy('recorded_at', 'desc')->get();
        return response()->json($records);
    }

    /**
     * Menyimpan riwayat kesehatan baru.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'record_type' => 'required|string|in:alergi,penyakit,operasi',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'recorded_at' => 'required|date',
        ]);

        $record = Auth::user()->medicalRecords()->create($validatedData);

        return response()->json($record, 201);
    }

    /**
     * Menampilkan detail satu riwayat kesehatan.
     */
    public function show(MedicalRecord $medicalRecord)
    {
        // Otorisasi: Pastikan user hanya bisa melihat data miliknya sendiri
        if ($medicalRecord->user_id !== Auth::id()) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        return response()->json($medicalRecord);
    }

    /**
     * Mengupdate riwayat kesehatan.
     */
    public function update(Request $request, MedicalRecord $medicalRecord)
    {
        // Otorisasi
        if ($medicalRecord->user_id !== Auth::id()) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $validatedData = $request->validate([
            'record_type' => 'sometimes|required|string|in:alergi,penyakit,operasi',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'recorded_at' => 'sometimes|required|date',
        ]);

        $medicalRecord->update($validatedData);

        return response()->json($medicalRecord);
    }

    /**
     * Menghapus riwayat kesehatan.
     */
    public function destroy(MedicalRecord $medicalRecord)
    {
        // Otorisasi
        if ($medicalRecord->user_id !== Auth::id()) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $medicalRecord->delete();

        return response()->json(['message' => 'Riwayat kesehatan berhasil dihapus'], 200);
    }
}
