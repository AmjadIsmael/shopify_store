<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopifyController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/products', [ShopifyController::class, 'getProducts'])->name('products.index');
Route::put('/products/{productId}', [ShopifyController::class, 'updateProducts'])->name('products.update');
