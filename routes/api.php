<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeIndexController;
use App\Http\Controllers\NoteController;
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


    // public routes
Route::get('/notes/{uuid}', [NoteController::class, 'show']);

    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');

Route::get('/', [HomeIndexController::class, 'index']);

Route::group(['middleware' => ['auth:api']], function () {


    Route::patch('/notes/{uuid}/detach', [NoteController::class, 'detach_file']);
    Route::post('/notes/{uuid}/share', [NoteController::class, 'share']);
    Route::get('/notes-shared-you', [NoteController::class, 'notes_shared_you']);


    Route::resource('/notes', NoteController::class, [
        'parameters' => [
            'notes' => 'uuid'
        ],
    ])->except(['show']);
});




