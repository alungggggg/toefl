<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoreDetail extends Model
{
    protected $table = 'exam_score_details';

    protected $fillable = [
        'exam_score_id',
        'bundler_id',
        'score',
        'max_score'
    ];

    public function examScore()
    {
        return $this->belongsTo(ExamsScores::class, 'exam_score_id');
    }

    public function bundler()
    {
        return $this->belongsTo(QuestionBundler::class, 'bundler_id');
    }

}
