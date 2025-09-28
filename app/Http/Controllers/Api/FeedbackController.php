<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    /**
     * Menyimpan feedback baru dari user yang sedang login.
     * (Akses untuk semua user terautentikasi)
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'rating' => 'nullable|integer|between:1,5',
            'comment' => 'required|string',
        ]);

        $feedback = Auth::user()->feedbacks()->create($validatedData);

        return response()->json([
            'message' => 'Terima kasih atas masukan Anda!',
            'data' => $feedback
        ], 201);
    }

    /**
     * Menampilkan semua feedback yang masuk.
     * (Akses khusus untuk Admin)
     */
    public function index(Request $request)
    {
        // Otorisasi: Hanya admin yang boleh mengakses
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $feedbacks = Feedback::with('user:id,name,email')->latest()->get();
        return response()->json($feedbacks);
    }

    /**
     * Menghapus feedback.
     * (Akses khusus untuk Admin)
     */
    public function destroy(Request $request, Feedback $feedback)
    {
        // Otorisasi: Hanya admin yang boleh mengakses
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $feedback->delete();
        return response()->json(['message' => 'Feedback berhasil dihapus']);
    }
}
