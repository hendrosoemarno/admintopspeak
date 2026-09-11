<?php
require 'vendor/autoload.php';
$unit7 = App\Models\Unit::where('unit_number', 7)->first();
$lesson3 = App\Models\Lesson::where('lesson_number', 3)->where('unit_id', $unit7->id)->first();
$questions = $lesson3->questions;
echo "Lesson 3 question count: " . $questions->count() . "\n";
echo "Total questions in unit 7: " . App\Models\Question::whereHas('lesson', fn($l) => $l->where('lesson_id', App\Models\Lesson::where('unit_id', $unit7->id)->where('lesson_number', 3)->first()->id))->count() . "\n";