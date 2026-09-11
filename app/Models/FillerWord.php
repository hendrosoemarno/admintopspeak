<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FillerWord extends Model
{
    protected $fillable = ['phrase', 'category', 'is_active'];
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
