<?php

namespace App\Http\Controllers;

use App\Services\JwtTokenService;
use Illuminate\Http\JsonResponse;
use App\Services\TicTacToeService;

class ApiAuthController extends Controller
{
    /**
     * @param JwtTokenService $jwtTokenService
     * @param TicTacToeService $ticTacToeService
     * @return JsonResponse
     */
    public function getToken(
        JwtTokenService $jwtTokenService,
        TicTacToeService $ticTacToeService
    ): JsonResponse {
        $token = $jwtTokenService->getToken();

        $ticTacToeService->createGame($token);

        return response()->json(['token' => $token]);
    }
}
