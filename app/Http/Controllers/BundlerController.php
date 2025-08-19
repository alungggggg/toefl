<?php

namespace App\Http\Controllers;

use App\Models\QuestionBundler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BundlerController extends Controller
{
    /**
     * Menampilkan semua bundler beserta soal-soalnya dengan caching
     */
    public function index()
    {
        $bundlers = Cache::remember('bundlers_all', 60 * 60, function () {
            $allBundlers = QuestionBundler::all();

            return $allBundlers->map(function ($bundler) {
                // Load relasi sesuai kategori
                switch ($bundler->category) {
                    case 'listening':
                        $bundler->load('listeningQuestions');
                        $bundler->questions = $bundler->listeningQuestions;
                        unset($bundler->listeningQuestions);
                        break;

                    case 'reading':
                        $bundler->load('readingQuestions');
                        $bundler->questions = $bundler->readingQuestions;
                        unset($bundler->readingQuestions);
                        break;

                    case 'structuring':
                        $bundler->load('structuringQuestions');
                        $bundler->questions = $bundler->structuringQuestions;
                        unset($bundler->structuringQuestions);
                        break;
                }

                return $bundler;
            });
        });

        return response()->json([
            'status' => true,
            'data' => $bundlers
        ]);

    }

    /**
     * Menyimpan bundler baru dan menghapus cache
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'category' => 'required|string|max:20',
                'level' => 'required|string|max:20',
                'is_active' => 'required|boolean'
            ]);

            $bundler = QuestionBundler::create($validated);

            // Hapus cache agar data terbaru muncul
            Cache::forget('bundlers_all');

            return response()->json([
                'status' => true,
                'message' => 'Bundler berhasil dibuat',
                'data' => $bundler
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
        }

    }

    /**
     * Menampilkan bundler tertentu beserta soal dengan caching
     */
    public function show($id)
    {
        $bundler = Cache::remember("bundler_{$id}", 60 * 60, function () use ($id) {
            $bundler = QuestionBundler::find($id);

            if (!$bundler)
                return null;

            // pilih relasi sesuai kategori
            $relation = null;
            switch ($bundler->category) {
                case 'listening':
                    $relation = 'listeningQuestions';
                    break;
                case 'reading':
                    $relation = 'readingQuestions';
                    break;
                case 'structuring':
                    $relation = 'structuringQuestions';
                    break;
            }

            if ($relation) {
                $bundler->load($relation);

                // ubah nama relasi menjadi questions
                $bundler->questions = $bundler->{$relation};
                unset($bundler->{$relation});
            }

            return $bundler;
        });

        if (!$bundler) {
            return response()->json([
                'status' => false,
                'message' => 'Bundler tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $bundler
        ]);
    }
    /**
     * Mengupdate bundler dan menghapus cache
     */
    public function update(Request $request, $id)
    {
        try {
            $bundler = QuestionBundler::find($id);

            if (!$bundler) {
                return response()->json([
                    'status' => false,
                    'message' => 'Bundler tidak ditemukan'
                ], 404);
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'category' => 'required|string|max:20',
                'level' => 'required|string|max:20',
                'is_active' => 'required|boolean'
            ]);

            $bundler->update($validated);

            // Hapus cache
            Cache::forget('bundlers_all');
            Cache::forget("bundler_{$id}");

            return response()->json([
                'status' => true,
                'message' => 'Bundler berhasil diperbarui',
                'data' => $bundler
            ]);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * Menghapus bundler dan menghapus cache
     */
    public function destroy($id)
    {
        $bundler = QuestionBundler::find($id);

        if (!$bundler) {
            return response()->json([
                'status' => false,
                'message' => 'Bundler tidak ditemukan'
            ], 404);
        }

        $relations = ['readingQuestions', 'listeningQuestions', 'structuringQuestions'];

        foreach ($relations as $relation) {
            if ($bundler->$relation()->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Bundler tidak dapat dihapus karena masih memiliki data question'
                ], 400);
            }
        }

        $bundler->delete();

        // Hapus cache
        Cache::forget('bundlers_all');
        Cache::forget("bundler_{$id}");

        return response()->json([
            'status' => true,
            'message' => 'Bundler berhasil dihapus',
            "data" => $bundler
        ]);
    }
}
