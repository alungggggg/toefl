<?php

namespace App\Http\Controllers;

use App\Models\ReadingQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ReadingController extends Controller
{
    /**
     * Menampilkan semua reading questions (dengan caching)
     */
    public function index(Request $request)
    {
        $bundlerId = $request->bundler_id;
        $cacheKey = $bundlerId ? "reading_questions_bundler_{$bundlerId}" : 'reading_questions_all';

        $data = Cache::remember($cacheKey, 3600, function () use ($bundlerId) {
            return $bundlerId
                ? ReadingQuestion::where('bundler_id', $bundlerId)->get()
                : ReadingQuestion::all();
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Menyimpan reading question baru
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'bundler_id' => 'required|integer',
                'section' => 'required|string|max:50',
                'passage' => 'required|string',
                'question_text' => 'required|string',
                'options' => 'required|array',
                'correct_answer' => 'required|string',
                'question_index' => 'required|integer',
                'score' => 'required|integer',
            ]);

            $question = ReadingQuestion::create($validated);

            // Hapus cache terkait bundler dan semua questions
            $this->clearCache($question->id, $question->bundler_id);

            return response()->json([
                'success' => true,
                'message' => 'Reading question berhasil ditambahkan',
                'data' => $question
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan reading question berdasarkan ID
     */
    public function show($id)
    {
        $question = Cache::remember("reading_question_{$id}", 3600, function () use ($id) {
            return ReadingQuestion::find($id);
        });

        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Reading question tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $question
        ]);
    }

    /**
     * Update reading question
     */
    public function update(Request $request, $id)
    {
        try {
            $question = ReadingQuestion::find($id);

            if (!$question) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reading question tidak ditemukan'
                ], 404);
            }

            $validated = $request->validate([
                'bundler_id' => 'required|integer',
                'section' => 'required|string|max:50',
                'passage' => 'required|string',
                'question_text' => 'required|string',
                'options' => 'required|array',
                'correct_answer' => 'required|string',
                'question_index' => 'required|integer',
                'score' => 'required|integer',
            ]);

            $question->update($validated);

            // Hapus cache terkait
            $this->clearCache($id, $question->bundler_id);

            return response()->json([
                'success' => true,
                'message' => 'Reading question berhasil diupdate',
                'data' => $question
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus reading question
     */
    public function destroy($id)
    {
        $question = ReadingQuestion::find($id);

        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Reading question tidak ditemukan'
            ], 404);
        }

        $question->delete();

        // Hapus cache terkait
        $this->clearCache($id, $question->bundler_id);

        return response()->json([
            'success' => true,
            'message' => 'Reading question berhasil dihapus'
        ]);
    }

    /**
     * Hapus cache untuk question tertentu dan bundler
     */
    private function clearCache($questionId, $bundlerId)
    {
        Cache::forget('reading_questions_all');
        Cache::forget("reading_questions_bundler_{$bundlerId}");
        Cache::forget("reading_question_{$questionId}");
    }
}
