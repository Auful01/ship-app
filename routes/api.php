<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\ShipController;
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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/sent', [EmailController::class, 'otpMail']);
Route::post('/confirm', [AuthController::class, 'confirmOTP']);


Route::prefix('/ship-pub')->group(function () {
    Route::get('/', [ShipController::class, 'index']);
    Route::get('/{id}', [ShipController::class, 'show']);
});

Route::group(['middleware' => ['auth']], function () {
    Route::post('/verify', [AuthController::class, 'accountVerif'])->middleware('can:user-verif');
    Route::prefix('/ship')->group(function () {
        Route::get('/', [ShipController::class, 'index'])->middleware('can:ship-list');
        Route::post('/', [ShipController::class, 'store'])->middleware('can:ship-create');
        Route::get('/{id}', [ShipController::class, 'show'])->middleware('can:ship-show');
        Route::put('/{id}', [ShipController::class, 'update'])->middleware('can:ship-edit');
        Route::delete('/{id}', [ShipController::class, 'destroy'])->middleware('can:ship-delete');
    });
});

Route::fallback(function () {
    return response()->json([
        'message' => 'Page Not Found. If error persists, contact',
    ], 404);
});
