<?php

namespace Database\Factories;

use App\Models\Game;
use App\Services\TicTacToeService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    protected $model = Game::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'status' => Game::STATUS_IN_PROGRESS,
            'board' => json_encode(TicTacToeService::BOARD),
            'token' => Str::random(20),
            'current_turn' => 'x'
        ];
    }
}
