<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamsBundler extends Model
{
    // Nama tabel secara eksplisit (karena Laravel default-nya plural)
    protected $table = 'exam_bundler';

    // Kolom yang bisa diisi (mass assignable)
    protected $fillable = [
        'exam_id',
        'bundler_id',
    ];

    // Relasi ke model Exam
    public function exam()
    {
        return $this->belongsTo(ExamsForum::class, 'exam_id');
    }

    // Relasi ke model Bundler
    public function bundler()
    {
        return $this->belongsTo(QuestionBundler::class, 'bundler_id');
    }
}
