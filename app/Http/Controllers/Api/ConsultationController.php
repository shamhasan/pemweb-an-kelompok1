<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsultationController extends Controller
{
    // Menampilkan daftar sesi konsultasi milik user
    public function index()
    {
        $consultations = Auth::user()->consultationsAsUser()->with('nutritionist')->latest()->get();
        return response()->json($consultations);
    }

    // Memulai sesi konsultasi baru
    public function store(Request $request)
    {
        $request->validate([
            'nutritionist_id' => 'required|exists:users,id'
        ]);

        $consultation = Auth::user()->consultationsAsUser()->create([
            'nutritionist_id' => $request->nutritionist_id,
            'status' => 'active',
            'started_at' => now(),
        ]);

        return response()->json($consultation, 201);
    }

    // Menampilkan detail dan semua pesan dari satu sesi konsultasi
    public function show(Consultation $consultation)
    {
        // Otorisasi: pastikan user yang login adalah bagian dari konsultasi ini
        if (Auth::id() !== $consultation->user_id && Auth::id() !== $consultation->nutritionist_id) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        // Ambil konsultasi beserta semua pesan dan pengirimnya
        $data = $consultation->load(['messages.sender']);

        return response()->json($data);
    }

    // Mengirim pesan ke dalam sebuah sesi konsultasi
    public function sendMessage(Request $request, Consultation $consultation)
    {
        // Otorisasi
        if (Auth::id() !== $consultation->user_id && Auth::id() !== $consultation->nutritionist_id) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $validatedData = $request->validate([
            'content' => 'required|string|min:1'
        ]);

        $message = $consultation->messages()->create([
            'sender_id' => Auth::id(),
            'content' => $validatedData['content'],
        ]);

        return response()->json($message, 201);
    }
}
