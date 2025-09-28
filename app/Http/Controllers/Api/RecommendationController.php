<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    /**
     * Menampilkan semua rekomendasi milik pengguna yang sedang login.
     */
    public function index(Request $request)
    {
        $recommendations = $request->user()
            ->recommendations()
            ->latest() // Diurutkan dari yang terbaru
            ->paginate(10); // Paginasi

        return response()->json($recommendations);
    }
}
