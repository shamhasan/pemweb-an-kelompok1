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
     * Mengupdate riwayat kesehatan.
     */
    public function update(Request $request, MedicalRecord $id){
    $validatedData = $request->validate([
        'record_type' => 'sometimes|required|string|in:alergi,penyakit,operasi',
        'name' => 'sometimes|required|string|max:255',
        'description' => 'nullable|string',
        'recorded_at' => 'sometimes|required|date',
    ]);

    $id->update($validatedData);

    return response()->json([
        'message' => 'Data riwayat kesehatan berhasil diupdate',
        'data' => $id
    ], 200);
}


    /**
     * Menghapus riwayat kesehatan.
     */
    public function destroy(MedicalRecord $id)
    {
        $id->delete();

        return response()->json(['message' => 'Riwayat kesehatan berhasil dihapus'], 200);
    }
}
