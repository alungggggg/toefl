<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StructuringQuestion extends Model
{
    // Nama tabel
    protected $table = 'structuring_questions';

    // Primary key
    protected $primaryKey = 'id';

    // Kolom yang bisa diisi
    protected $fillable = [
        'bundler_id',
        'section',
        'question_text',
        'options',
        'correct_answer',
        'question_index',
        'score',
    ];

    // Kolom bertipe tanggal
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function QuestionBundler()
    {
        return $this->belongsTo(QuestionBundler::class, 'bundler_id');
    }
}
