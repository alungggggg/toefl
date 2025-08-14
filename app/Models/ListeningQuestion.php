<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListeningQuestion extends Model
{
    use HasFactory;

    protected $table = 'listening_questions'; // ganti sesuai nama tabel di database
    protected $primaryKey = 'id';

    protected $fillable = [
        'bundler_id',
        'section',
        'question_text',
        'audio_url',
        'options',
        'correct_answer',
        'question_index',
        'score',
    ];

    protected $casts = [
        'options' => 'array', // karena longtext/binary, diasumsikan JSON untuk opsi jawaban
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    public function QuestionBundler()
    {
        return $this->belongsTo(QuestionBundler::class, 'bundler_id');
    }
}


