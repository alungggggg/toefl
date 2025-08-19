<?php

namespace App\Http\Controllers;

use App\Models\ListeningQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ListeningController extends Controller
{
    // Ambil semua data listening questions (pakai cache)
    public function index(Request $request)
    {
        $bundlerId = $request->bundler_id;
        $cacheKey = $bundlerId ? "listening_questions_bundler_{$bundlerId}" : 'listening_questions_all';

        $questions = Cache::remember($cacheKey, 3600, function () use ($bundlerId) {
            return $bundlerId
                ? ListeningQuestion::where('bundler_id', $bundlerId)
                    ->orderBy('question_index', 'asc')
                    ->get()
                : ListeningQuestion::orderBy('question_index', 'asc')->get();
        });

        return response()->json([
            'status' => true,
            'data' => $questions
        ]);
    }

    // Ambil detail berdasarkan ID (pakai cache)
    public function show($id)
    {
        $question = Cache::remember("listening_question_{$id}", 3600, function () use ($id) {
            return ListeningQuestion::find($id);
        });

        if (!$question) {
            return response()->json(['status' => false, 'message' => 'Question not found'], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $question
        ]);
    }

    // Simpan data baru
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'bundler_id' => 'required|string|max:50',
                'section' => 'required|string|max:50',
                'question_text' => 'required|string',
                'audio_file' => 'nullable|file|mimes:mp3,wav,ogg|max:10240', // max 10MB
                'audio_url' => 'nullable|string|max:255',
                'options' => 'required|array',
                'correct_answer' => 'required|string|max:100',
                'question_index' => 'required|integer',
                'score' => 'required|integer',
            ]);

            // Simpan file audio jika ada
            if ($request->hasFile('audio_file')) {
                $path = $request->file('audio_file')->store('audios', 'public');
                $validated['audio_url'] = '/storage/' . $path;
            }

            // $validated['options'] = json_encode($validated['options']);

            $question = ListeningQuestion::create($validated);

            $this->clearCache($question->id, $question->bundler_id);

            return response()->json([
                'status' => true,
                'message' => 'Listening question berhasil ditambahkan',
                'data' => $question
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    // Update data
    public function update(Request $request, $id)
    {
        try {
            $question = ListeningQuestion::find($id);
            if (!$question) {
                return response()->json([
                    'status' => false,
                    'message' => 'Question not found'
                ], 404);
            }

            $validated = $request->validate([
                'bundler_id' => 'required|string|max:50',
                'section' => 'required|string|max:50',
                'question_text' => 'required|string',
                'audio_file' => 'nullable|file|mimes:mp3,wav,ogg|max:10240',
                'audio_url' => 'nullable|string|max:255',
                'options' => 'required|array',
                'correct_answer' => 'required|string|max:100',
                'question_index' => 'required|integer',
                'score' => 'required|integer',
            ]);

            // Simpan file audio jika ada
            if ($request->hasFile('audio_file')) {
                $path = $request->file('audio_file')->store('audios', 'public');
                $validated['audio_url'] = '/storage/' . $path;
            }

            $question->update($validated);

            $this->clearCache($id, $question->bundler_id);

            return response()->json([
                'status' => true,
                'message' => 'Listening question berhasil diupdate',
                'data' => $question
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    // Hapus data
    public function destroy($id)
    {
        $question = ListeningQuestion::find($id);
        if (!$question) {
            return response()->json([
                'status' => false,
                'message' => 'Question not found'
            ], 404);
        }

        // Hapus file audio jika ada
        if ($question->audio_url) {
            $filePath = str_replace('/storage/', '', $question->audio_url); // ambil path relatif
            if (\Storage::disk('public')->exists($filePath)) {
                \Storage::disk('public')->delete($filePath);
            }
        }

        $question->delete();

        $this->clearCache($id, $question->bundler_id);

        return response()->json([
            'status' => true,
            'message' => 'Successfully deleted the question and its audio file',
            'data' => $question
        ]);
    }


    // Hapus cache helper
    private function clearCache($questionId, $bundlerId)
    {
        Cache::forget('listening_questions_all');
        Cache::forget("listening_questions_bundler_{$bundlerId}");
        Cache::forget("listening_question_{$questionId}");
    }
}
