<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $fillable = ['unit_number', 'title', 'part', 'outcome'];
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }
}