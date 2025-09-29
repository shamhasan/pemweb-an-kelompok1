<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    // Helper Functions
    private function getAuthenticatedUserId(Request $request): int
    {
        $id = $request->user()?->id;
        if (!$id) return 1;
        // if (!$id) abort(401, 'User not authenticated');
        return (int) $id;
    }

    private function ensureOwner(Request $request, Consultation $consultation): void
    {
        $authId = $this->getAuthenticatedUserId($request);
        abort_if($consultation->user_id !== $authId, 403, 'Forbidden');
    }

    public function index(Request $request)
    {
        // Validasi query param sederhana
        $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'status'   => 'sometimes|in:aktif,selesai',
        ]);

        $authId  = $this->getAuthenticatedUserId($request);
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        $consultations = Consultation::query()
            ->where('user_id', $authId)
            ->when(
                $request->filled('status'),
                fn($q) =>
                $q->where('status', $request->string('status')->toString())
            )
            ->with('user')
            ->orderByDesc('started_at')
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json($consultations);
    }

    public function store(Request $request)
    {

        $request->validate([
            'user_id'    => 'sometimes|required|integer|exists:users,id',
            'status'     => 'sometimes|required|string|in:aktif,selesai',
            'started_at' => 'sometimes|required|date'
        ], [
            'user_id.exists' => 'User tidak ditemukan',
            'user_id.integer' => 'User ID harus berupa angka',
            'status.in'      => 'Status tidak valid',
            'started_at.date' => 'Tanggal mulai tidak valid',
        ]);

        $userId = $this->getAuthenticatedUserId($request);

        $consultation = Consultation::create([
            'user_id'    => $userId,
            'status'     => 'aktif',
            'started_at' => now(),
        ]);

        return response()->json($consultation->load('user'), 201);
    }


    public function show(Request $request, Consultation $consultation)
    {
        $this->ensureOwner($request, $consultation);

        // Untuk query param pagination
        $request->validate(rules: [
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $perPage = min(max($request->integer('per_page', 50), 1), 100);

        $consultation->load('user');

        $messages = $consultation->messages()
            ->orderBy('sent_at', 'asc')
            ->orderBy('id', 'asc')
            ->paginate($perPage);

        return response()->json(
            [
                'data'      => $consultation,
                'messages'  => $messages,
            ]
        );
    }


    public function update(Request $request, Consultation $consultation)
    {

        $this->ensureOwner($request, $consultation);

        $request->validate([
            'started_at' => 'sometimes|date|before_or_equal:now',
            'status'    => 'sometimes|required|in:aktif,selesai',
            'ended_at'  => 'prohibited', // server-controlled
        ], [
            'status.in'     => 'Status tidak valid',
            'ended_at.date' => 'Tanggal selesai tidak valid',
            'ended_at.prohibited' => 'Field ended_at dikendalikan oleh server.',
        ]);

        $changes = [];

        if ($request->filled('started_at')) {
            $changes['started_at'] = $request->date('started_at');
        }

        if ($request->filled('status')) {
            $target = $request->string('status')->toString();

            if ($target === 'selesai') {
                if ($consultation->status !== 'selesai') {
                    $changes['status'] = 'selesai';
                    $changes['ended_at'] = now();
                }
            } elseif ($target === 'aktif') {
                if ($consultation->status === 'selesai') {
                    return response()->json([
                        'message' => 'Konsultasi yang sudah selesai tidak bisa dibuka kembali.'
                    ], 409);
                }
            }
        }

        if (empty($changes)) {
            return response()->json(['message' => 'Tidak ada perubahan'], 409);
        }

        try {
            $consultation->update($changes);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat memperbarui konsultasi.', 'error' => $e->getMessage()], 500);
        }

        return response()->json($consultation->fresh()->load('user'));
    }

    public function destroy(Request $request, Consultation $consultation)
    {
        $this->ensureOwner($request, $consultation);

        $consultation->delete();

        return response()->json(['message' => 'Consultation deleted successfully'], 204);
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

        return response()->json($consultations);
    }
}
