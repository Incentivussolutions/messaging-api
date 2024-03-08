<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\TemplateConfigController;
use App\Http\Controllers\WhatsappConfigController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/token', [ApplicationController::class, 'getCommonToken']);
Route::post('client/search', [ApplicationController::class, 'clientSearch']);
Route::post('client/status', [MessageController::class, 'status']);
Route::post('client/inbound', [MessageController::class, 'inbound']);
Route::group(['middleware'=> ['auth.common']], function() {
    Route::post('app/login', [ApplicationController::class, 'login']);
    Route::post('client/create', [ClientController::class, 'create']);
});

Route::group(['middleware'=> ['auth.api']], function() {
    Route::post('user/index', [UserController::class, 'index']);
    Route::post('user/get-list', [UserController::class, 'getList']);
    Route::post('user/form-data', [UserController::class, 'formData']);
    Route::post('user/store', [UserController::class, 'store']);
    Route::post('user/status', [UserController::class, 'status']);
    Route::post('user/change-password', [UserController::class, 'changePassword']);
    Route::post('config/index', [WhatsappConfigController::class, 'index']);
    Route::post('config/get-list', [WhatsappConfigController::class, 'getList']);
    Route::post('config/form-data', [WhatsappConfigController::class, 'formData']);
    Route::post('config/store', [WhatsappConfigController::class, 'store']);
    Route::post('template-config/index', [TemplateConfigController::class, 'index']);
    Route::post('template-config/get-list', [TemplateConfigController::class, 'getList']);
    Route::post('template-config/form-data', [TemplateConfigController::class, 'formData']);
    Route::post('template-config/store', [TemplateConfigController::class, 'store']);
});
Route::group(['middleware'=> ['auth.client']], function() {
    Route::post('send/message', [MessageController::class, 'sendMessage']);
    Route::post('send/messages', [MessageController::class, 'queueMessage']);
    Route::post('messages/status', [MessageController::class, 'getStatus']);
});
Route::get('download/templates', [MessageController::class, 'downloadWhatsappTemplates']);
