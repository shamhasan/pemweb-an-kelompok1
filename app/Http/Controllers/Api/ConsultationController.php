<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConsultationController extends Controller
{
    // Helper Functions

    private function getAuthenticatedUserId(Request $request): int
    {
        $id = $request->user()?->id;
        if (!$id) abort(401, 'User not authenticated');
        return (int) $id;
    }

    private function ensureOwner(Request $request, Consultation $consultation): void
    {
        $authId = $this->getAuthenticatedUserId($request);
        abort_if($consultation->user_id !== $authId, 403, 'Forbidden');
    }

    public function index(Request $request)
    {
        $authId  = $this->getAuthenticatedUserId($request);

        // Validasi query param sederhana
        $v = Validator::make($request->all(), [
            'status'   => 'sometimes|in:aktif,selesai',
        ], [
            'status.in' => 'Status tidak valid',
        ]);

        if ($v->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $v->errors(),
            ], 422);
        }

        $validated = $v->validated();

        $limit = max(1, min(50, 100));

        $consultations = Consultation::query()
            ->where('user_id', $authId)
            ->when(
                isset($validated['status']),
                fn($q) => $q->where('status', $validated['status'])
            )
            ->with('user')
            ->orderByDesc('started_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        return response()->json([
            'status'  => 'success',
            'message' => $consultations->isEmpty() ? 'Data konsultasi tidak ditemukan' : 'Data konsultasi berhasil diambil',
            'data'    => $consultations,
        ]);
    }

    public function store(Request $request)
    {
        $userId = $this->getAuthenticatedUserId($request);

        $v = Validator::make($request->all(), [
            'user_id'    => 'sometimes|required|integer|exists:users,id',
            'status'     => 'sometimes|required|string|in:aktif,selesai',
            'started_at' => 'sometimes|required|date',
        ], [
            'user_id.exists'   => 'User tidak ditemukan',
            'user_id.integer'  => 'User ID harus berupa angka',
            'status.in'        => 'Status tidak valid',
            'started_at.date'  => 'Tanggal mulai tidak valid',
        ]);

        if ($v->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $v->errors(),
            ], 422);
        }

        $consultation = Consultation::create([
            'user_id'    => $userId,
            'status'     => 'aktif',
            'started_at' => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Konsultasi berhasil dibuat',
            'data'    => $consultation->load('user'),
        ], 201);
    }


    public function show(Request $request, Consultation $consultation)
    {
        $this->ensureOwner($request, $consultation);

        $v = Validator::make($request->all(), [
            'limit' => 'sometimes|integer|min:1|max:200',
        ]);
        if ($v->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $v->errors(),
            ], 422);
        }
        $limit = (int)($v->validated()['limit'] ?? 100);

        $consultation->load('user');

        $messages = $consultation->messages()
            ->orderBy('sent_at', 'asc')
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get();

        return response()->json([
            'status'  => 'success',
            'message' => $messages->isEmpty() ? 'Tidak ada pesan' : 'Detail konsultasi berhasil diambil',
            'data'    => [
                'consultation' => $consultation,
                'messages'     => $messages,
            ],
        ]);
    }

    public function update(Request $request, Consultation $consultation)
    {
        $this->ensureOwner($request, $consultation);

        $v = Validator::make($request->all(), [
            'started_at' => 'sometimes|date|before_or_equal:now',
            'status'     => 'sometimes|required|in:aktif,selesai',
            'ended_at'   => 'prohibited', // server-controlled
        ], [
            'status.in'              => 'Status tidak valid',
            'ended_at.prohibited'    => 'Field ended_at dikendalikan oleh server.',
            'started_at.before_or_equal' => 'Tanggal mulai tidak boleh di masa depan.',
        ]);

        if ($v->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $v->errors(),
            ], 422);
        }

        $changes = [];

        if ($request->filled('started_at')) {
            $changes['started_at'] = $request->date('started_at');
        }

        if ($request->filled('status')) {
            $target = $request->string('status')->toString();

            if ($target === 'selesai') {
                if ($consultation->status !== 'selesai') {
                    $changes['status']   = 'selesai';
                    $changes['ended_at'] = now();
                }
            } elseif ($target === 'aktif') {
                if ($consultation->status === 'selesai') {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Konsultasi yang sudah selesai tidak bisa dibuka kembali.'
                    ], 409);
                }
            }
        }

        if (empty($changes)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tidak ada perubahan'
            ], 409);
        }

        try {
            $consultation->update($changes);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui konsultasi.',
                'error'   => config('app.debug') ? $e->getMessage() : 'INTERNAL_ERROR',
            ], 500);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Konsultasi berhasil diperbarui.',
            'data'    => $consultation->fresh()->load('user'),
        ]);
    }

    public function destroy(Request $request, Consultation $consultation)
    {
        $this->ensureOwner($request, $consultation);

        $consultation->delete();

        return response()->noContent();
    }

    // Ini untuk menampilkan pesannya juga
    public function activeConsultations(Request $request)
    {
        $authId = $this->getAuthenticatedUserId($request);

        $consultations = Consultation::where('user_id', $authId)
            ->where('status', 'aktif')
            ->with('messages')
            ->orderByDesc('started_at')
            ->get();

        return response()->json([
            'message' => 'Konsultasi aktif berhasil diambil',
            'data' => $consultations
        ]);
    }
}
