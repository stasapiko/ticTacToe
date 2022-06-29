<?php

use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\TicTacToeController;
use App\Http\Middleware\VerifyJwtToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/auth', [ApiAuthController::class, 'getToken'])->name('getToken');

Route::middleware([VerifyJwtToken::class])->group(function () {
    Route::get('/', [TicTacToeController::class, 'getBoardAction'])->name('ticTacToe.getBoard');
    Route::post('/move', [TicTacToeController::class, 'moveAction'])->name('ticTacToe.move');
    Route::post('/restart', [TicTacToeController::class, 'restartAction'])->name('ticTacToe.restart');
    Route::delete('/clear', [TicTacToeController::class, 'clearAction'])->name('ticTacToe.clear');
});


