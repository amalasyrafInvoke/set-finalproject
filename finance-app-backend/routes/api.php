<?php

use App\Http\Controllers\ApiController;
use Facade\FlareClient\Api;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});

Route::group(['middleware' => 'api'], function () {
  Route::post('/create-wallet', [ApiController::class, 'createWallet']);
  Route::get('/fetch-wallet/{id}', [ApiController::class, 'fetchWallet']);
  Route::get('/transactions/all/{accountId}', [ApiController::class, 'getTransactionsList']);
  Route::get('/transactions/pastSevenDays/{accountId}', [ApiController::class, 'getSevenDaysTransactions']);
  Route::post('/transactions/create/{accountId}', [ApiController::class, 'createTransactions']);
  Route::get('/savings/all/{userId}', [ApiController::class, 'getSavingsList']);
  Route::get('/savings/get/{id}', [ApiController::class, 'getSingleSavings']);
  Route::post('/savings/create/{userId}', [ApiController::class, 'createNewSavings']);
  Route::put('/savings/update/{savingsId}', [ApiController::class, 'updateSavings']);
  Route::put('/savings/delete/{savingsId}', [ApiController::class, 'deleteSavings']);
  Route::get('/savings/getTransactions/{savingsId}', [ApiController::class, 'getSavingsTransactions']);
  Route::post('/savings/create-transaction/{savingsId}', [ApiController::class, 'createSavingsTransaction']);
});

Route::group([
  'prefix' => 'auth'
], function ($router) {
  Route::post('/login', [ApiController::class, 'login']);

  Route::group(['middleware' => 'api'], function () {
    Route::post('/register', [ApiController::class, 'register']);
    Route::post('/logout', [ApiController::class, 'logout']);
    Route::post('/refresh', [ApiController::class, 'refresh']);
    Route::get('/user-profile', [ApiController::class, 'userProfile']);
    Route::post('/update-user', [ApiController::class, 'updateUser']);
  });
});
