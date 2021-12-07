<?php

use App\Http\Controllers\BTCController;
use App\Http\Controllers\MixerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::get('/', [MixerController::class, 'index'])->name('home');
Route::post('/mixer-start', [MixerController::class, 'start_mixer'])->name('mixer.start');

Route::get('/create-wallet', [BTCController::class, 'createWallet']);
Route::get('/create-transaction', [BTCController::class, 'p2wkh_p2pkh']);
