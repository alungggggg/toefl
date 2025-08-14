<?php

namespace App\Http\Controllers;

use App\Models\ScoreDetail;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ScoreDetailController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'exam_score_id' => 'required|exists:exam_scores,id',
                'bundler_id' => 'required|exists:question_bundler,id',
                'score' => 'required|integer|min:0',
                'max_score' => 'required|integer|min:0'
            ]);

            $detail = ScoreDetail::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Score detail berhasil disimpan.',
                'data' => $detail
            ], 201);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
                'error' => $e->getMessage()
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan score detail.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
