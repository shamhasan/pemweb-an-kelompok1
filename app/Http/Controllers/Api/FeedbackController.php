<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FeedbackResource;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Admin: List semua feedback
     * Route: GET /admin/feedbacks (middleware: auth:api, admin)
     * Query optional:
     *   - per_page (int) => jika > 0 akan paginate, default 15. Jika = 0, kembalikan semua.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);

        $query = Feedback::with(['user:id,name,email'])
            ->latest('created_at');

        if ($perPage > 0) {
            $paginator = $query->paginate($perPage);
            // Resource collection akan otomatis menyertakan meta & links pagination
            return FeedbackResource::collection($paginator);
        }

        $items = $query->get();
        return FeedbackResource::collection($items);
    }

    /**
     * User/Admin: Buat feedback
     * Route: POST /feedbacks (middleware: auth:api)
     * Body:
     *   - rating (nullable|integer|min:1|max:5)
     *   - comment (required|string)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rating'  => 'nullable|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        if (empty($validated['rating']) && empty($validated['comment'])) {
            return response()->json(['message' => 'Rating atau komentar harus diisi.'], 422);
        }
        //  Pastikan ambil user dari guard 'api' (JWT)
        $userId = auth('api')->id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $feedback = Feedback::create([
            'user_id' => $userId,
            'rating'  => $validated['rating'] ?? null,
            'comment' => $validated['comment'],
        ])->load('user:id,name,email');

        // Single resource + message + 201
        return (new FeedbackResource($feedback))
            ->additional(['message' => 'Feedback berhasil dibuat.'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Admin: Hapus satu feedback
     * Route: DELETE /admin/feedbacks/{id} (middleware: auth:api, admin)
     */
    public function destroy($id)
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->delete();

        return response()->json([
            'message' => 'Feedback berhasil dihapus.',
            'id'      => (int) $id,
        ], 200);
    }

    /**
     * (Opsional) Admin: Hapus semua feedback
     * Route: DELETE /admin/feedbacks (middleware: auth:api, admin)
     * Hati-hati: ini akan menghapus seluruh data feedback!
     */
    public function destroyAll(Request $request)
    {
        $count = Feedback::count();

        if ($count === 0) {
            return response()->json([
                'message'       => 'Tidak ada feedback untuk dihapus.',
                'deleted_count' => 0,
            ], 200);
        }

        Feedback::query()->delete();

        return response()->json([
            'message'       => 'Semua feedback berhasil dihapus.',
            'deleted_count' => $count,
        ], 200);
    }
}
