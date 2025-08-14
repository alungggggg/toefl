<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamsForum extends Model
{
    // Nama tabel
    protected $table = 'exams';

    // Kolom yang bisa diisi mass-assignment
    protected $fillable = [
        'title',
        'description',
        'duration_minutes',
        'total_score',
        'access_date',
        'expired_date'
    ];

    // Casting otomatis tipe data
    protected $casts = [
        'access_date' => 'datetime',
        'expired_date' => 'datetime',
        'duration_minutes' => 'integer',
        'total_score' => 'integer'
    ];

    /**
     * Relasi ke QuestionBundler langsung via tabel pivot exam_bundler
     */
    public function bundlers()
    {
        return $this->belongsToMany(QuestionBundler::class, 'exam_bundler', 'exam_id', 'bundler_id')
            ->withPivot('id') // supaya id di pivot ikut di-load
            ->withTimestamps()
            ->with(['listeningQuestions', 'readingQuestions', 'structuringQuestions']);
    }

}
