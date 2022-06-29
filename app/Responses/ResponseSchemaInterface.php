<?php

namespace App\Responses;

use App\Models\Game;

interface ResponseSchemaInterface
{
    /**
     * @param Game $game
     * @return array
     */
    public function gameBoardResponse(Game $game): array;

    /**
     * @param string $message
     * @return array
     */
    public function gameBoardErrorResponse(string $message): array;
}
