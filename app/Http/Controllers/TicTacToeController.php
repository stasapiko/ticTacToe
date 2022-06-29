<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\MoveRequest;
use App\Services\TicTacToeService;
use App\Responses\TicTacToeResponseSchema;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

class TicTacToeController extends Controller
{
    /**
     * @var TicTacToeService
     */
    private TicTacToeService $ticTacToe;

    /**
     * @var TicTacToeResponseSchema
     */
    private TicTacToeResponseSchema $tacToeResponseSchema;

    /**
     * @param TicTacToeService $ticTacToeService
     * @param TicTacToeResponseSchema $tacToeResponseSchema
     */
    public function __construct(
        TicTacToeService $ticTacToeService,
        TicTacToeResponseSchema $tacToeResponseSchema,
    ) {
        $this->ticTacToe = $ticTacToeService;
        $this->tacToeResponseSchema = $tacToeResponseSchema;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getBoardAction(
        Request $request
    ): JsonResponse {
        $game = $this->ticTacToe->getGame($request->get('token'));

        $responseBody = $this->tacToeResponseSchema->gameBoardResponse($game);

        return response()->json($responseBody);
    }

    /**
     * @param MoveRequest $request
     * @return JsonResponse
     */
    public function moveAction(MoveRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $game = $this->ticTacToe->setPiece($validated);
        } catch (NotFoundHttpException $exception) {
            return response()->json($this->tacToeResponseSchema->gameBoardErrorResponse($exception->getMessage()), 404);
        } catch (ConflictHttpException $exception) {
            return response()->json($this->tacToeResponseSchema->gameBoardErrorResponse($exception->getMessage()), 409);
        } catch (NotAcceptableHttpException $exception) {
            return response()->json($this->tacToeResponseSchema->gameBoardErrorResponse($exception->getMessage()), 406);
        } catch (\Exception $exception) {
            return response()->json($this->tacToeResponseSchema->gameBoardErrorResponse($exception->getMessage()), 400);
        }

        $responseBody = $this->tacToeResponseSchema->gameBoardResponse($game);

        return response()->json($responseBody);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function restartAction(Request $request): JsonResponse
    {
        $game = $this->ticTacToe->restart($request->get('token'));

        $responseBody = $this->tacToeResponseSchema->gameBoardResponse($game);

        return response()->json($responseBody);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function clearAction(Request $request): JsonResponse
    {
        $game = $this->ticTacToe->clearGameSessionHistory($request->get('token'));

        return response()->json(['currentTurn' => $game->current_turn]);
    }
}
