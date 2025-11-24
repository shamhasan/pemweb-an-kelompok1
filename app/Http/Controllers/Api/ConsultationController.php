<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConsultationController extends Controller
{
    // Helper Functions

    private function getAuthenticatedUserId(Request $request): int
    {
        return (int) $request->user()->id;
    }

    private function getUserRole(Request $request): string
    {
        return (string) $request->user()->role;
    }

    private function ensureOwner(Request $request, Consultation $consultation): void
    {
        $authId = $this->getAuthenticatedUserId($request);
        $userRole = $this->getUserRole($request);
        abort_if($consultation->user_id !== $authId && $userRole !== 'admin', 403, 'Forbidden');
    }

    public function index(Request $request)
    {

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
            'meta'    => ['count' => $consultations->count()]
        ]);
    }

    public function store(Request $request)
    {
        $userId = $this->getAuthenticatedUserId($request);

        $v = Validator::make($request->all(), [
            'user_id'    => 'prohibited',
            'status'     => 'prohibited',
            'started_at' => 'prohibited',
        ], [
            'user_id.prohibited'    => 'Field user_id dikendalikan oleh server.',
            'status.prohibited'     => 'Field status dikendalikan oleh server.',
            'started_at.prohibited' => 'Field started_at dikendalikan oleh server.',
        ]);

        if ($v->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $v->errors(),
            ], 422);
        }

        $alreadyActive = Consultation::query()
            ->where('user_id', $userId)
            ->where('status', 'aktif')
            ->exists();

        if ($alreadyActive) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda sudah memiliki konsultasi aktif. Selesaikan konsultasi tersebut sebelum memulai yang baru.'
            ], 409);
        }

        try {
            $consultation = Consultation::create([
                'user_id'    => $userId,
                'status'     => 'aktif',
                'started_at' => now(),
            ]);
        } catch (QueryException $e) {
            // Menghindari race db transaction
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Anda sudah memiliki konsultasi aktif.'
                ], 409);
            }
            throw $e;
        }

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

    public function activeForUser(Request $request)
    {
        $authId = $this->getAuthenticatedUserId($request);

        $consultation = Consultation::where('user_id', $authId)
            ->where('status', 'aktif')
            ->with('messages')
            ->first();

        return response()->json([
            'status'  => 'success',
            'message' => $consultation && $consultation->messages->isEmpty() ? 'Tidak ada pesan' : 'Detail konsultasi berhasil diambil',
            'data' => $consultation,
        ]);
    }


    public function update(Request $request, Consultation $consultation)
    {
        $this->ensureOwner($request, $consultation);

        $v = Validator::make($request->all(), [
            'status'     => 'sometimes|required|in:aktif,selesai',
            'started_at' => 'prohibited',
            'ended_at'   => 'prohibited', // server-controlled
        ], [
            'status.in'              => 'Status tidak valid',
            'ended_at.prohibited'    => 'Field ended_at dikendalikan oleh server.',
            'started_at.prohibited'  => 'Field started_at dikendalikan oleh server.',
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

    public function destroy(Consultation $consultation)
    {

        $consultation->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Konsultasi berhasil dihapus.'
        ]);
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
