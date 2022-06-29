<?php

namespace Tests\Unit;

use App\Http\Controllers\ApiAuthController;
use App\Services\JwtTokenService;
use App\Services\TicTacToeService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class GameAuthTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_receiveJwtToken()
    {
        $token = JwtTokenService::getToken();

        $this->assertIsString($token);
    }

    public function test_validateJwtTokenWhenOk()
    {
        $dummyToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1lIjpudWxsLCJjcmVhdGVkQXQiOjE2NTY0MjgyNDN9.-4gOvhTyikRIgTvDxUrTRFg22rZHIaFRvn-jrpyGeVg';
        $result = JwtTokenService::validateToken($dummyToken);
        $this->assertTrue($result);
    }

    public function test_createGameAndGetToken()
    {
        $apiAuthController = new ApiAuthController();
        $response = $apiAuthController->getToken(
            new JwtTokenService(),
            new TicTacToeService()
        );

        $response = $response->getContent();
        $json = json_decode($response);
        $this->assertIsString($json->token);
    }

    /**
     * @dataProvider tokenDataProvider
     */
    public function test_validateJwtTokenWhenWrongToken($tokenData, $expectedMessage)
    {
        $result = JwtTokenService::validateToken($tokenData);
        $this->assertEquals($expectedMessage, $result);
    }

    public function tokenDataProvider()
    {
        yield ['donjnoddsf', false];
        yield ['', false];
    }
}
