<?php

namespace App\Responses;

use App\Models\Game;
use App\Services\TicTacToeService;

class TicTacToeResponseSchema implements ResponseSchemaInterface
{
    /**
     * @param Game $game
     * @return array
     */
    public function gameBoardResponse(Game $game): array
    {
        return [
            'board' => json_decode($game->board),
            'score' => TicTacToeService::getScoreForGame($game->id),
            'current_turn' => $game->current_turn,
            'victory' => TicTacToeService::getLastGameSession($game->id)->winner
        ];
    }

    /**
     * @param string $message
     * @return string[]
     */
    public function gameBoardErrorResponse(string $message): array
    {
        return [
            'error' => $message
        ];
    }
}
