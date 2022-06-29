<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Session;
use App\Services\TicTacToeService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Tests\TestCase;

class TicTacToeTest extends TestCase
{
    use DatabaseMigrations;

    private string $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1lIjpudWxsLCJjcmVhdGVkQXQiOjE2NTY0MjgyNDN9.-4gOvhTyikRIgTvDxUrTRFg22rZHIaFRvn-jrpyGeVg';


    public function setUp(): void
    {
        parent::setUp();
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

        $this->ticTacToeService = new TicTacToeService();
    }

    public function test_getBoardActionWhenOk()
    {
        $response = $this->get('/api/?token=' . $this->token);
        $response->assertStatus(200);
    }

    public function test_getBoardActionWhenFail()
    {
        $response = $this->get('/api/');
        $response->assertStatus(401);
    }

    public function test_moveActionWhenOk()
    {
        $formData = [
            'x' => 1,
            'y' => 2
        ];

        $response = $this->post('/api/move?token=' . $this->token, $formData);
        $response->assertStatus(200);
    }

    /**
     * @dataProvider RequestDataProvider
     */
    public function test_moveActionWhenNotAcceptable($requestData, $expectedMessage)
    {
        $formData = [
            'x' => 1,
            'y' => 2
        ];

        $this->post('/api/move?token=' . $this->token, $formData);

        $response = $this->post('/api/move?token=' . $this->token, $requestData);

        $response->assertStatus($expectedMessage);
    }

    /**
     * @dataProvider IncorrectRequestDataProvider
     */
    public function test_moveActionWhenIncorrectParam($incorrectRequest)
    {
        $response = $this->post('/api/move?token=' . $this->token, $incorrectRequest);
        $response->assertStatus(422);
    }

    public function IncorrectRequestDataProvider()
    {
        yield [['token' => $this->token, 'x' => 'asdasdasd', 'y' => 1]];
        yield [['token' => $this->token, 'x' => 1, 'y' => 'asdas']];
        yield [['token' => $this->token, 'x' => 0]];
        yield [['token' => $this->token, 'y' => 0]];
    }

    public function requestDataProvider()
    {
        yield [['token' => $this->token, 'x' => 1, 'y' => 2], 406];
    }

    public function test_restartActionWhenOk()
    {
        $response = $this->post('/api/restart?token=' . $this->token);
        $response->assertStatus(200);
    }

    public function test_clearActionWhenOk()
    {
        $response = $this->delete('/api/clear?token=' . $this->token);
        $response->assertStatus(200);
    }
}
