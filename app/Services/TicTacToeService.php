<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Session;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

class TicTacToeService
{
    public const BOARD = [
        ['', '', ''],
        ['', '', ''],
        ['', '', ''],
    ];

    /**
     * @param string $token
     * @return void
     */
    public function createGame(string $token): void
    {
        $game = Game::create([
            'status' => Game::STATUS_IN_PROGRESS,
            'board' => json_encode(self::BOARD),
            'token' => $token,
            'current_turn' => 'x'
        ]);

        $game->save();

        $session = Session::create([
            'game_id' => $game->id,
            'winner' => '',
        ]);

        $session->save();
    }

    /**
     * @param string $token
     * @return Game
     */
    public function getGame(string $token): Game
    {
        return Game::where('token', $token)->take(1)->first();
    }

    /**
     * @param array $params
     * @return Game
     * @throws \Exception
     */
    public function setPiece(array $params): Game
    {
        $game = Game::where('token', $params['token'])->with('latestSession')->take(1)->first();

        if (!$game instanceof Game) {
            throw new NotFoundHttpException('No game found');
        }

        $boardArray = json_decode($game->board);

        if ($game->status !== Game::STATUS_IN_PROGRESS || $game->latestSession->moves === 9) {
            throw new ConflictHttpException('Conflict');
        }

        if ($boardArray[$params['y']][$params['x']] !== '') {
            throw new NotAcceptableHttpException('Not acceptable');
        }

        $boardArray[$params['y']][$params['x']] = $game->current_turn;
        $game->board = json_encode($boardArray);
        $game->current_turn = ($game->current_turn == 'x') ? 'o' : 'x';

        if ($winner = $this->checkIfWinnerExist($boardArray) || $game->latestSession->moves === 8) {
            $game->status = Game::STATUS_FINISHED;
            $game->current_turn = '';
            $game->latestSession()->update([
                'winner' => $winner
            ]);
        }

        $game->latestSession()->increment('moves');
        $game->save();

        return $game;
    }

    /**
     * @param $gameId
     * @return int[]
     */
    public static function getScoreForGame($gameId): array
    {
        $gameSessions = Session::where(['game_id' => $gameId])->get();

        $score = ['x' => 0, 'o' => 0];

        foreach ($gameSessions as $gameSession) {
            if (array_key_exists($gameSession->winner, $score)) {
                $score[$gameSession->winner]++;
            }
        }

        return $score;
    }

    /**
     * @param $gameId
     * @return Session
     */
    public static function getLastGameSession($gameId): Session
    {
        return Session::where(['game_id' => $gameId])->latest()->first();
    }

    /**
     * @param string $token
     * @return Game
     */
    public function restart(string $token): Game
    {
        $game = Game::where('token', $token)->take(1)->first();

        $game->board = json_encode(self::BOARD);
        $game->current_turn = 'x';
        $game->status = Game::STATUS_IN_PROGRESS;

        $game->save();

        $session = Session::create([
            'game_id' => $game->id,
            'winner' => '',
        ]);

        $session->save();

        return Game::where('token', $token)->with('latestSession')->take(1)->first();
    }


    /**
     * @param string $token
     * @return Game
     */
    public function clearGameSessionHistory(string $token): Game
    {
        $game = Game::where('token', $token)->with('latestSession')->take(1)->first();

        Session::where('game_id', $game->id)->where('id', '!=', $game->latestSession->id)->delete();

        return $game;
    }

    /**
     * @param array $board
     * @return string
     */
    private function checkIfWinnerExist(array $board): string
    {
        // Checking Rows
        for ($row = 0; $row < 3; $row++) {
            if ($board[$row][0] && $board[$row][0] == $board[$row][1] && $board[$row][1] == $board[$row][2]) {
                return $board[$row][0];
            }
        }

        // Checking Columns
        for ($col = 0; $col < 3; $col++) {
            if ($board[0][$col] && $board[0][$col] == $board[1][$col] && $board[1][$col] == $board[2][$col]) {
                return $board[0][$col];
            }
        }

        // Checking Diagonals
        if ($board[0][0] && $board[0][0] == $board[1][1] && $board[1][1] == $board[2][2]) {
            return $board[0][0];
        }

        if ($board[0][2] && $board[0][2] == $board[1][1] && $board[1][1] == $board[2][0]) {
            return $board[0][2];
        }

        return '';
    }
}
