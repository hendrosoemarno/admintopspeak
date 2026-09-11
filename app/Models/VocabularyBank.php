<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['word', 'part_of_speech', 'cefr_level', 'topic_category'])]
class VocabularyBank extends Model
{
    public function getTable(): string
    {
        return 'vocabulary_bank';
    }
}