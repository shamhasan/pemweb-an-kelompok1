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
        $id = $request->user()?->id;
        if (!$id) return 1; // atau: abort(401, 'Unauthorized');
        return (int) $id;
    }

    private function ensureOwner(Request $request, Consultation $consultation): void
    {
        $authId = $this->authId($request);
        abort_if($consultation->user_id !== $authId, 403, 'Forbidden');
    }

    /** ========= Actions ========= */

    public function index(Request $request)
    {
        $request->validate([
            'per_page'        => 'sometimes|integer|min:1|max:100',
            'sender_type'     => 'sometimes|in:user,ai',
            'after_id'        => 'sometimes|integer|min:1',
            'consultation_id' => 'sometimes|integer|exists:consultations,id',
        ]);

        $authId  = $this->authId($request);
        $perPage = min(max($request->integer('per_page', 50), 1), 100);
        $consultationId = $request->integer('consultation_id');

        if ($consultationId) {
            Consultation::where('id', $consultationId)
                ->where('user_id', $authId)
                ->firstOrFail();
        }

        $q = Message::query()
            ->when(
                !$consultationId,
                fn($q) =>
                $q->whereHas('consultation', fn($c) => $c->where('user_id', $authId))
            )
            ->when($consultationId, fn($q) => $q->where('consultation_id', $consultationId))
            ->when(
                $request->filled('sender_type'),
                fn($q) =>
                $q->where('sender_type', $request->string('sender_type')->toString())
            )
            ->with('consultation')
            ->orderBy('sent_at', 'asc')
            ->orderBy('id', 'asc');


        return response()->json($q->paginate($perPage));
    }

    public function store(Request $request, GeminiClient $gemini)
    {
        $request->validate([
            'consultation_id' => 'required|integer|exists:consultations,id',
            'content'         => 'required|string|min:1|max:6000',
            'sender_type'     => 'sometimes|in:user',
        ], [
            'consultation_id.required' => 'ID konsultasi wajib diisi',
            'consultation_id.exists'   => 'Konsultasi tidak ditemukan',
            'content.required'         => 'Pesan tidak boleh kosong',
            'content.max'              => 'Pesan terlalu panjang (maksimal 6000 karakter)',
        ]);

        $authId = $this->authId($request);

        $consultation = Consultation::where('id', $request->integer('consultation_id'))
            ->where('user_id', $authId)
            ->firstOrFail();

        if ($consultation->status !== 'aktif') {
            return response()->json(['message' => 'Konsultasi sudah selesai'], 409);
        }

        $userMsg = Message::create([
            'consultation_id' => $consultation->id,
            'sender_type'     => 'user',
            'content'         => $request->string('content')->toString(),
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
            'user_message'      => $userMsg->load('consultation'),
            'assistant_message' => $assistantMsg->load('consultation'),
        ], 201);
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

    public function show(Request $request, Message $message)
    {
        $this->ensureOwner($request, $message->consultation);
        return response()->json($message->load('consultation.user'));
    }

    public function update(Request $request, Message $message)
    {
        $this->ensureOwner($request, $message->consultation);

        $v = Validator::make($request->all(), [
            'content'     => 'required|string|min:1|max:2000',
            'sent_at'     => 'sometimes|date',
            'sender_type' => 'prohibited',
        ], [
            'sender_type.prohibited' => 'Tipe pengirim tidak bisa diubah',
        ]);

        if ($v->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $v->errors(),
            ], 422);
        }

        $validated = $v->validated();

        $message->update($validated);

        return response()->json($message);
    }

    public function destroy(Request $request, Message $message)
    {
        $this->ensureOwner($request, $message->consultation);
        $message->delete();

        return response()->noContent(); // 204
    }

    public function getConsultationMessages(Request $request, Consultation $consultation)
    {
        $this->ensureOwner($request, $consultation);

        $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $perPage = min(max($request->integer('per_page', 50), 1), 100);

        $messages = $consultation->messages()
            ->orderBy('sent_at', 'asc')
            ->orderBy('id', 'asc')
            ->paginate($perPage);

        return response()->json($messages);
    }
}
