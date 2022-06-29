<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Game extends Model
{
    use HasFactory;

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_FINISHED = 'finished';

    protected $fillable = ['status', 'board', 'token', 'current_turn'];

    public function session(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    public function latestSession(): HasOne
    {
        return $this->hasOne(Session::class)->latest();
    }
}
