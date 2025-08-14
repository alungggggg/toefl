<?php

namespace App\Http\Controllers;

use App\Models\ExamsForum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Log;

class ExamsController extends Controller
{
    /**
     * Menampilkan semua exam (dengan caching).
     */
    public function index()
    {
        try {
            // Cache selama 5 menit (300 detik)
            $exams = Cache::remember('exams_all', 60 * 5, function () {
                return ExamsForum::all();
            });

            return response()->json([
                'success' => true,
                'data' => $exams
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'data' => $th->getMessage()
            ], 500);

        }
    }

    /**
     * Menampilkan exam berdasarkan ID (dengan caching).
     */

    public function show($id)
    {
        try {
            $exam = Cache::remember("exam_{$id}", 60 * 5, function () use ($id) {
                return ExamsForum::with(['bundlers'])->find($id);
            });

            $exam->bundlers->transform(function ($bundler) {
                $questions = collect()
                    ->merge($bundler->listeningQuestions)
                    ->merge($bundler->readingQuestions)
                    ->merge($bundler->structuringQuestions)
                    ->values();

                unset($bundler->listeningQuestions, $bundler->readingQuestions, $bundler->structuringQuestions);

                $bundler->questions = $questions;
                $bundler->pivot_id = $bundler->pivot->id ?? null; // ambil id pivot

                return $bundler;
            });

            return response()->json([
                'success' => true,
                'data' => $exam
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal mengambil data exam: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data exam',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    /**
     * Menyimpan exam baru.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'duration_minutes' => 'required|integer',
                'total_score' => 'nullable|integer',
                'access_date' => 'nullable|date',
                'expired_date' => 'nullable|date',
            ]);

            $exam = ExamsForum::create($validated);

            // Hapus cache list exam agar ter-refresh
            Cache::forget('exams_all');

            return response()->json([
                'success' => true,
                'data' => $exam
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'data' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Mengupdate exam.
     */
    public function update(Request $request, $id)
    {
        try {
            $exam = ExamsForum::find($id);

            if (!$exam) {
                return response()->json([
                    'success' => false,
                    'message' => 'Exam tidak ditemukan'
                ], 404);
            }

            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'duration_minutes' => 'sometimes|required|integer',
                'total_score' => 'nullable|integer',
                'access_date' => 'nullable|date',
                'expired_date' => 'nullable|date',
            ]);

            $exam->update($validated);

            // Hapus cache supaya update terlihat
            Cache::forget('exams_all');
            Cache::forget("exam_{$id}");

            return response()->json([
                'success' => true,
                'data' => $exam
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'data' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus exam.
     */
    public function destroy($id)
    {
        $exam = ExamsForum::find($id);

        if (!$exam) {
            return response()->json([
                'success' => false,
                'message' => 'Exam tidak ditemukan'
            ], 404);
        }

        $exam->delete();

        // Hapus cache terkait
        Cache::forget('exams_all');
        Cache::forget("exam_{$id}");

        return response()->json([
            'success' => true,
            'message' => 'Exam berhasil dihapus'
        ]);
    }
}
