<?php

namespace Tests\Unit;

use App\Models\Game;
use App\Models\Session;
use App\Services\TicTacToeService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Tests\TestCase;

class TicTacToeTest extends TestCase
{
    use DatabaseMigrations;

    public TicTacToeService $ticTacToeService;

    private string $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1lIjpudWxsLCJjcmVhdGVkQXQiOjE2NTY0MjgyNDN9.-4gOvhTyikRIgTvDxUrTRFg22rZHIaFRvn-jrpyGeVg';

    public function setUp(): void
    {
        parent::setUp();

        $this->ticTacToeService = new TicTacToeService();
    }

    public function test_setPieceWhenOk()
    {
        $game = Game::create([
            'status' => Game::STATUS_IN_PROGRESS,
            'board' => json_encode(TicTacToeService::BOARD),
            'token' => $this->token,
            'current_turn' => 'x'
        ]);
        $game->save();

        $session = Session::create([
            'game_id' => $game->id,
            'winner' => '',
        ]);
        $session->save();

        $dummyData = ['token' => $this->token, 'x' => 0, 'y' => 1];

        $game = $this->ticTacToeService->setPiece($dummyData);

        $this->assertInstanceOf(Game::class, $game);
    }

    /**
     * @dataProvider requestDataProvider
     */
    public function test_setPieceWhenFailed($requestData, $expectedMessage)
    {
        $game = Game::create([
            'status' => Game::STATUS_IN_PROGRESS,
            'board' => json_encode(TicTacToeService::BOARD),
            'token' => $this->token,
            'current_turn' => 'x'
        ]);
        $game->save();

        $session = Session::create([
            'game_id' => $game->id,
            'winner' => '',
        ]);
        $session->save();

        $dummyData = ['token' => $this->token, 'x' => 0, 'y' => 1];
        $this->ticTacToeService->setPiece($dummyData);

        try {
            $this->ticTacToeService->setPiece($requestData);
        } catch (\Exception $exception) {
            // then
            $this->assertEquals($expectedMessage, $exception->getMessage());
        }

    }

    public function test_setPieceWhenConflict()
    {
        $dummyData = ['token' => Str::random(20), 'x' => 0, 'y' => 1];

        $game = Game::create([
            'status' => Game::STATUS_FINISHED,
            'board' => json_encode(TicTacToeService::BOARD),
            'token' => $dummyData['token'],
            'current_turn' => 'x'
        ]);
        $game->save();

        $session = Session::create([
            'game_id' => $game->id,
            'winner' => '',
        ]);
        $session->save();

        try {
            $this->ticTacToeService->setPiece($dummyData);
        } catch (\Exception $exception) {
            // then
            $this->assertEquals('Conflict', $exception->getMessage());
        }

    }

    public function requestDataProvider()
    {
        yield [['token' => '', 'x' => 0, 'y' => 1], 'No game found'];
        yield [['token' => $this->token, 'x' => 0, 'y' => 1], 'Not acceptable'];
    }

    public function test_getScores()
    {
        $dummyData = ['token' => $this->token, 'x' => 0, 'y' => 1];

        $game = Game::create([
            'status' => Game::STATUS_IN_PROGRESS,
            'board' => json_encode(TicTacToeService::BOARD),
            'token' => $this->token,
            'current_turn' => 'x'
        ]);
        $game->save();

        $session1 = Session::create([
            'game_id' => $game->id,
            'winner' => 'x',
        ]);
        $session1->save();

        $session2 = Session::create([
            'game_id' => $game->id,
            'winner' => 'o',
        ]);
        $session2->save();

        $session3 = Session::create([
            'game_id' => $game->id,
            'winner' => 'x',
        ]);

        $session3->save();

        $this->assertIsArray(TicTacToeService::getScoreForGame($game->id));
    }

    public function test_getLastGameSession()
    {
        $game = Game::create([
            'status' => Game::STATUS_IN_PROGRESS,
            'board' => json_encode(TicTacToeService::BOARD),
            'token' => $this->token,
            'current_turn' => 'x'
        ]);
        $game->save();

        $session = Session::create([
            'game_id' => $game->id,
            'winner' => 'x',
        ]);
        $session->save();

        $session = Session::create([
            'game_id' => $game->id,
            'winner' => 'o',
        ]);
        $session->save();

        $session = Session::create([
            'game_id' => $game->id,
            'winner' => 'x',
        ]);

        $session->save();

        $this->assertInstanceOf(Session::class, TicTacToeService::getLastGameSession($game->id));
    }

    public function test_restart()
    {
        $game = Game::create([
            'status' => Game::STATUS_IN_PROGRESS,
            'board' => json_encode(TicTacToeService::BOARD),
            'token' => $this->token,
            'current_turn' => 'x'
        ]);
        $game->save();

        $session = Session::create([
            'game_id' => $game->id,
            'winner' => 'x',
        ]);
        $session->save();

        $game = $this->ticTacToeService->restart($this->token);

        $this->assertInstanceOf(Game::class, $game);
    }

    public function test_clearGameSessionHistory()
    {
        $game = Game::create([
            'status' => Game::STATUS_IN_PROGRESS,
            'board' => json_encode(TicTacToeService::BOARD),
            'token' => $this->token,
            'current_turn' => 'x'
        ]);
        $game->save();

        $session = Session::create([
            'game_id' => $game->id,
            'winner' => 'x',
        ]);
        $session->save();

        $game = $this->ticTacToeService->clearGameSessionHistory($this->token);

        $this->assertInstanceOf(Game::class, $game);
        $this->assertDatabaseCount('sessions', 1);
    }

    public function test_getGame()
    {
        $game = Game::create([
            'status' => Game::STATUS_IN_PROGRESS,
            'board' => json_encode(TicTacToeService::BOARD),
            'token' => $this->token,
            'current_turn' => 'x'
        ]);
        $game->save();

        $game = $this->ticTacToeService->getGame($this->token);

        $this->assertInstanceOf(Game::class, $game);
    }


    /**
     * @dataProvider boardDataProvider
     */
    public function test_gameHasWinner($boardData)
    {
        $game = Game::create([
            'status' => Game::STATUS_IN_PROGRESS,
            'board' => json_encode($boardData),
            'token' => $this->token,
            'current_turn' => 'x'
        ]);
        $game->save();

        $session = Session::create([
            'game_id' => $game->id,
            'winner' => 'x',
        ]);
        $session->save();

        $dummyData = ['token' => $this->token, 'x' => 1, 'y' => 2];

        $game = $this->ticTacToeService->setPiece($dummyData);

        $this->assertInstanceOf(Game::class, $game);
        $this->assertDatabaseHas('games', ['token' => $this->token, 'status' => Game::STATUS_FINISHED]);
    }

    public function boardDataProvider()
    {
        yield [[
            ['x', '', ''],
            ['x', '', ''],
            ['x', '', ''],
        ]];
        yield [[
            ['o', '', ''],
            ['', 'o', ''],
            ['', '', 'o'],
        ]];
        yield [[
            ['x', 'x', 'x'],
            ['', '', ''],
            ['', '', ''],
        ]];
        yield [[
            ['', '', 'x'],
            ['', 'x', ''],
            ['x', '', ''],
        ]];
    }
}
