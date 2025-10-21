<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LevelProgress extends Model
{
    use HasFactory;

    protected $table = 'level_progress';

    protected $fillable = [
        'user_id',
        'level_id',
        'status',
        'checkpoint',
        'score',
        'quiz_seconds_spent', // <-- AÑADIDO: Permite que este campo se guarde
    ];

    protected $casts = [
        'score' => 'float',
        'checkpoint' => 'integer',
        'quiz_seconds_spent' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }
}