<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Consultation;
use App\Services\GeminiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    /** ========= Helpers ========= */
    private function authId(Request $request): int
    {
        return (int) $request->user()->id;
    }

    private function getUserRole(Request $request): string
    {
        return (string) $request->user()->role;
    }

    private function ensureOwner(Request $request, Consultation $consultation): void
    {
        $authId = $this->authId($request);
        $userRole = $this->getUserRole($request);
        abort_if($consultation->user_id !== $authId && $userRole !== 'admin', 403, 'Forbidden');
    }

    /** ========= Actions ========= */

    public function index(Request $request)
    {
        $v = Validator::make($request->all(), [
            'sender_type'     => 'sometimes|in:user,ai',
            'consultation_id' => 'sometimes|integer|exists:consultations,id',
        ]);

        if ($v->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $v->errors(),
            ], 422);
        }
        $validated = $v->validated();

        $cid = $validated['consultation_id'] ?? null;

        if ($cid) {
            Consultation::findOrFail($cid);
        }

        $messages = Message::query()
            ->when($cid, fn($q) => $q->where('consultation_id', $cid))
            ->when(
                isset($validated['sender_type']),
                fn($q) => $q->where('sender_type', $validated['sender_type'])
            )
            ->with(['consultation.user'])
            ->orderBy('sent_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'status'  => 'success',
            'message' => $messages->isEmpty() ? 'Tidak ada pesan ditemukan' : 'Semua pesan berhasil diambil',
            'data'    => $messages,
            'meta'    => ['count' => $messages->count()],
        ]);
    }

    public function store(Request $request, GeminiClient $gemini)
    {
        $authId = $this->authId($request);

        $v = Validator::make($request->all(), [
            'consultation_id' => 'required|integer|exists:consultations,id',
            'content'         => ['required', 'string', 'regex:/\S/', 'max:6000'], // harus ada non-whitespace
            'sender_type'     => 'prohibited', // server-controlled
        ], [
            'consultation_id.required' => 'ID konsultasi wajib diisi',
            'consultation_id.exists'   => 'Konsultasi tidak ditemukan',
            'content.required'         => 'Pesan tidak boleh kosong',
            'content.regex'            => 'Pesan tidak boleh hanya spasi/kosong',
            'content.max'              => 'Pesan terlalu panjang (maksimal 6000 karakter)',
            'sender_type.prohibited'   => 'Field sender_type dikendalikan oleh server',
        ]);

        if ($v->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $v->errors(),
            ], 422);
        }
        $validated = $v->validated();

        $consultation = Consultation::where('id', (int)$validated['consultation_id'])
            ->where('user_id', $authId)
            ->firstOrFail();

        if ($consultation->status !== 'aktif') {
            return response()->json(['message' => 'Konsultasi sudah selesai'], 409);
        }

        $userMsg = Message::create([
            'consultation_id' => $consultation->id,
            'sender_type'     => 'user',
            'content'         => trim((string)$validated['content']),
            'sent_at'         => now(),
        ]);

        try {
            $assistantMsg = $this->generateAIResponse($consultation, $gemini);
        } catch (\Throwable $e) {
            Log::error('Gemini API Error', [
                'consultation_id' => $consultation->id,
                'user_id'         => $consultation->user_id,
                'error'           => $e->getMessage(),
            ]);

            $assistantMsg = Message::create([
                'consultation_id' => $consultation->id,
                'sender_type'     => 'ai',
                'content'         => 'Maaf, sistem sedang sibuk. Coba lagi.',
                'sent_at'         => now(),
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Pesan berhasil dikirim',
            'data'    => [
                'user_message'      => $userMsg->load('consultation'),
                'assistant_message' => $assistantMsg->load('consultation'),
            ]
        ], 201);
    }

    public function show(Request $request, Message $message)
    {
        $this->ensureOwner($request, $message->consultation);
        return response()->json([
            'message' => 'Pesan berhasil diambil',
            'data'    => $message->load('consultation.user')
        ]);
    }

    public function update(Request $request, Message $message)
    {
        $this->ensureOwner($request, $message->consultation);

        $v = Validator::make($request->all(), [
            'content'     => ['required', 'string', 'regex:/\S/', 'max:6000'],
            'sent_at'     => 'prohibited',
            'sender_type' => 'prohibited',
        ], [
            'sender_type.prohibited' => 'Tipe pengirim tidak bisa diubah',
            'sent_at.prohibited'     => 'Waktu pengiriman tidak bisa diubah',
        ]);

        if ($v->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $v->errors(),
            ], 422);
        }

        $validated = $v->validated();

        $message->update($validated);

        return response()->json([
            'message' => 'Pesan berhasil diperbarui',
            'data' => $message
        ]);
    }

    public function destroy(Message $message)
    {
        $message->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Pesan berhasil dihapus.'
        ]);
    }

    private function generateAIResponse(Consultation $consultation, GeminiClient $gemini): Message
    {
        $recent = $consultation->messages()
            ->orderByDesc('id')->take(20)->get()->sortBy('id');

        $history = [[
            'role'  => 'user',
            'parts' => [['text' => env('CHAT_PREAMBLE')]],
        ]];

        foreach ($recent as $m) {
            $history[] = [
                'role'  => $m->sender_type === 'ai' ? 'model' : 'user',
                'parts' => [['text' => $m->content]],
            ];
        }

        $response = $gemini->generate($history);

        $content = trim($response['text'] ?? '');
        if ($content === '') {
            $content = 'Maaf, saya tidak dapat memberikan respons saat ini. Silakan coba lagi atau hubungi dokter.';
        }

        return Message::create([
            'consultation_id' => $consultation->id,
            'sender_type'     => 'ai',
            'content'         => $content,
            'sent_at'         => now(),
        ]);
    }
}
