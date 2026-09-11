<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VocabularyBank extends Model
{
    protected $fillable = ['word', 'part_of_speech', 'cefr_level', 'topic_category'];
    public function getTable(): string
    {
        return 'vocabulary_bank';
    }
}