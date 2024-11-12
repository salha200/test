<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');


});
use App\Http\Controllers\UserController;


Route::prefix('users')->group(function () {
    // Get all users
    Route::get('/', [UserController::class, 'index']);

    // Get a specific user by ID
    Route::get('{id}', [UserController::class, 'show']);

    // Update a specific user by ID
    Route::put('{id}', [UserController::class, 'update']);

    // Delete a specific user by ID
    Route::delete('{id}', [UserController::class, 'destroy']);
});

Route::get('/users', [UserController::class, 'index']);
use App\Http\Controllers\RoleController;

Route::apiResource('roles', RoleController::class);
Route::post('/tasks', [TaskController::class, 'store']);

Route::prefix('api')->group(function () {
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{id}/status', [TaskController::class, 'updateStatus']);
    Route::put('/tasks/{id}/reassign', [TaskController::class, 'reassign']);
    Route::post('/tasks/{id}/comments', [TaskController::class, 'addComment']);
    Route::post('/tasks/{id}/attachments', [TaskController::class, 'addAttachment']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks/{id}/assign', [TaskController::class, 'assign']);
    Route::get('/reports/daily-tasks', [TaskController::class, 'dailyReport']);
});
