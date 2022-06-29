<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Session extends Model
{
    use HasFactory;

    protected $table = 'sessions';

    protected $fillable = ['winner', 'moves', 'game_id'];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
