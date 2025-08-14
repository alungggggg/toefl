<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReadingQuestion extends Model
{
    // Nama tabel
    protected $table = 'reading_questions';

    // Primary key
    protected $primaryKey = 'id';

    // Kolom yang bisa diisi (mass assignable)
    protected $fillable = [
        'bundler_id',
        'section',
        'passage',
        'question_text',
        'options',
        'correct_answer',
        'question_index',
        'score',
    ];

    // Kolom yang bertipe tanggal
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
