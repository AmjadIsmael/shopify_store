<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopifyController;
use App\Http\Controllers\TestController;

use App\GPT\Actions\TranslateText\TranslateTextGPTAction;

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

Route::get('products', [ShopifyController::class, 'getProducts']);
Route::put('products/{productId}', [ShopifyController::class, 'updateProduct']);

Route::post('/test-translate', [TestController::class, 'testTranslate']);
