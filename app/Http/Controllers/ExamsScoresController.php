<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExamsScores;
use Exception;

class ExamsScoresController extends Controller
{
    public function index()
    {
        try {
            $scores = ExamsScores::with([
                'user',
                'exam' // hanya sampai exam
            ])->get();

            return response()->json([
                'success' => true,
                'data' => $scores
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function show($id)
    {
        try {
            $score = ExamsScores::with([
                'user',
                'exam',
                'details.bundler'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $score
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }


    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'exam_id' => 'required|exists:exams,id',
                'score' => 'required|numeric|min:0',
                'max_score' => 'required|numeric|min:0'
            ]);

            $examScore = ExamsScores::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Exam score berhasil disimpan',
                'data' => $examScore
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'score' => 'nullable|numeric|min:0',
                'max_score' => 'nullable|numeric|min:0'
            ]);

            $examScore = ExamsScores::findOrFail($id);
            $examScore->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Exam score berhasil diupdate',
                'data' => $examScore
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $examScore = ExamsScores::findOrFail($id);
            // $examScore->ScoreDetail()->delete();
            $examScore->delete();

            return response()->json([
                'success' => true,
                'message' => 'Exam score dan detail berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
