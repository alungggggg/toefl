<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamsScores extends Model
{
    protected $table = 'exam_scores';

    protected $fillable = [
        'user_id',
        'exam_id',
        'score',
        'max_score'
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke Exam
    public function exam()
    {
        return $this->belongsTo(ExamsForum::class, 'exam_id');
    }

    public function details()
    {
        return $this->hasMany(ScoreDetail::class, 'exam_score_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($examScore) {
            $examScore->details()->delete();
        });
    }

}
