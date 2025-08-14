<?php

namespace App\Http\Controllers;

use App\Models\ExamsBundler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ExamsBundlerController extends Controller
{
    /**
     * Menambahkan bundler ke dalam exam
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'exam_id' => 'required|integer|exists:exams,id',
                'bundler_id' => 'required|integer|exists:question_bundler,id',
            ]);

            $examBundler = ExamsBundler::create($validated);

            // Hapus cache terkait exam
            Cache::forget("exam_{$validated['exam_id']}");

            return response()->json([
                'success' => true,
                'message' => 'Bundler berhasil ditambahkan ke exam.',
                'data' => $examBundler
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Gagal menambahkan bundler ke exam: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan bundler ke exam.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus bundler dari exam
     */
    public function destroy($id)
    {
        try {
            $examBundler = ExamsBundler::find($id);

            if (!$examBundler) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data exam bundler tidak ditemukan.'
                ], 404);
            }

            $examId = $examBundler->exam_id;

            $examBundler->delete();

            // Hapus cache terkait exam
            Cache::forget("exam_{$examId}");

            return response()->json([
                'success' => true,
                'message' => 'Bundler berhasil dihapus dari exam.'
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal menghapus bundler dari exam: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus bundler dari exam.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
