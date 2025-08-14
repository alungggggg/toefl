<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionBundler extends Model
{
    use HasFactory;

    protected $table = 'question_bundler'; // ganti jika nama tabel berbeda

    protected $fillable = [
        'title',
        'description',
        'category',
        'level',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function listeningQuestions()
    {
        return $this->hasMany(ListeningQuestion::class, 'bundler_id');
    }

    // Relasi ke ReadingQuestion
    public function readingQuestions()
    {
        return $this->hasMany(ReadingQuestion::class, 'bundler_id');
    }

    // Relasi ke StructuringQuestion
    public function structuringQuestions()
    {
        return $this->hasMany(StructuringQuestion::class, 'bundler_id');
    }
}
