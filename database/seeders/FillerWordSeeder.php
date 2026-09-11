<?php

namespace Database\Seeders;

use App\Models\FillerWord;
use Illuminate\Database\Seeder;

class FillerWordSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'hesitation' => [
                'uh', 'uhh', 'um', 'umm', 'uhm', 'er', 'err', 'ah', 'ahh',
                'hm', 'hmm', 'mm', 'mmm',
            ],
            'discourse' => [
                'like', 'well', 'so', 'actually', 'basically', 'literally', 'anyway',
            ],
            'phrase' => [
                'you know', 'i mean', 'kind of', 'kinda', 'sort of', 'how to say',
                'what is it', 'what do you call it', 'let me see', 'let me think',
                'something like that',
            ],
        ];

        foreach ($data as $category => $phrases) {
            foreach ($phrases as $phrase) {
                FillerWord::updateOrCreate(
                    ['phrase' => $phrase],
                    ['category' => $category, 'is_active' => true],
                );
            }
        }
    }
}
