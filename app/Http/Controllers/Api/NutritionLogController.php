<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNutritionLogRequest;
use App\Http\Requests\UpdateNutritionLogRequest;
use App\Http\Resources\NutritionLogResource;
use App\Models\NutritionLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;



class NutritionLogController extends Controller
{
    // Menampilkan semua catatan nutrisi milik user yang login
    public function index(Request $request)
    {
        $user = $request->user();

        $query = $user->nutritionLogs();

        if ($request->has('date')) {
            $query->whereDate('consumed_at', $request->date);
        }

        $logs = $query->latest('consumed_at')->paginate(15);

        return NutritionLogResource::collection($logs);
    }

    /**
     * Menambah catatan nutrisi baru.
     */
    public function store(StoreNutritionLogRequest $request)
    {
        $validatedData = $request->validated();

        $log = $request->user()->nutritionLogs()->create($validatedData);

        return (new NutritionLogResource($log))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED); // 201 Created
    }

    /**
     * Menampilkan detail satu catatan nutrisi.
     */
    public function show(Request $request, string $id)
    {
        $log = $request->user()->nutritionLogs()->find($id);

        if (!$log) {
            return response()->json(['message' => 'Nutrition log not found.'], Response::HTTP_NOT_FOUND);
        }

        return new NutritionLogResource($log);
    }

    /**
     * Memperbarui detail catatan nutrisi.
     */
    public function update(UpdateNutritionLogRequest $request, string $id)
    {
        $log = $request->user()->nutritionLogs()->find($id);

        if (!$log) {
            return response()->json(['message' => 'Nutrition log not found.'], Response::HTTP_NOT_FOUND); // 404 Not Found
        }

        $log->update($request->validated());

        return new NutritionLogResource($log);
    }

    /**
     * Menghapus sebuah catatan nutrisi.
     */
    public function destroy(Request $request, string $id)
    {
        $log = $request->user()->nutritionLogs()->find($id);

        if (!$log) {
            return response()->json(['message' => 'Nutrition log not found.'], Response::HTTP_NOT_FOUND);
        }

        $log->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT); // 204 No Content
    }
}