<?php

namespace App\Http\Controllers;

use App\Models\StructuringQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StructuringController extends Controller
{
    /**
     * Menampilkan semua structuring questions (dengan caching)
     */
    public function index(Request $request)
    {
        $bundlerId = $request->bundler_id;
        $cacheKey = $bundlerId ? "structuring_questions_bundler_{$bundlerId}" : 'structuring_questions_all';

        $data = Cache::remember($cacheKey, 3600, function () use ($bundlerId) {
            return $bundlerId
                ? StructuringQuestion::where('bundler_id', $bundlerId)->orderBy('question_index', 'asc')->get()
                : StructuringQuestion::orderBy('question_index', 'asc')->get();
        });

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    /**
     * Menyimpan structuring question baru
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'bundler_id' => 'required|integer',
                'section' => 'required|string|max:50',
                'question_text' => 'required|string',
                'options' => 'required|array',
                'correct_answer' => 'required|string',
                'question_index' => 'required|integer',
                'score' => 'required|integer',
            ]);

            $question = StructuringQuestion::create($validated);

            // Hapus cache terkait
            $this->clearCache($question->id, $question->bundler_id);

            return response()->json([
                'status' => true,
                'message' => 'Structuring question berhasil ditambahkan',
                'data' => $question
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Menampilkan structuring question berdasarkan ID
     */
    public function show($id)
    {
        $question = Cache::remember("structuring_question_{$id}", 3600, function () use ($id) {
            return StructuringQuestion::find($id);
        });

        if (!$question) {
            return response()->json([
                'status' => false,
                'message' => 'Structuring question tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $question
        ]);
    }

    /**
     * Update structuring question
     */
    public function update(Request $request, $id)
    {
        try {
            $question = StructuringQuestion::find($id);

            if (!$question) {
                return response()->json([
                    'status' => false,
                    'message' => 'Structuring question tidak ditemukan'
                ], 404);
            }

            $validated = $request->validate([
                'bundler_id' => 'required|integer',
                'section' => 'required|string|max:50',
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
                'status' => true,
                'message' => 'Structuring question berhasil diupdate',
                'data' => $question
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }

    /**
     * Hapus structuring question
     */
    public function destroy($id)
    {
        $question = StructuringQuestion::find($id);

        if (!$question) {
            return response()->json([
                'status' => false,
                'message' => 'Structuring question tidak ditemukan'
            ], 404);
        }

        $question->delete();

        // Hapus cache terkait
        $this->clearCache($id, $question->bundler_id);

        return response()->json([
            'status' => true,
            'message' => 'Structuring question berhasil dihapus',
            'data' => $question
        ]);
    }

    /**
     * Hapus cache untuk question tertentu dan bundler
     */
    private function clearCache($questionId, $bundlerId)
    {
        Cache::forget('structuring_questions_all');
        Cache::forget("structuring_questions_bundler_{$bundlerId}");
        Cache::forget("structuring_question_{$questionId}");
    }
}
