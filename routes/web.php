<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CasinoController;
use App\Http\Controllers\AuthController;

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', [CasinoController::class, 'home'])->name('home');
Route::get('/slots', [CasinoController::class, 'slots'])->name('slots');
Route::get('/roulette', [CasinoController::class, 'roulette'])->name('roulette');
Route::get('/blackjack', [CasinoController::class, 'blackjack'])->name('blackjack');
Route::get('/dice', [CasinoController::class, 'dice'])->name('dice');
Route::get('/sports', [CasinoController::class, 'sports'])->name('sports');

// API
Route::get('/api/balance', [CasinoController::class, 'getBalance']);
Route::post('/api/slots/spin', [CasinoController::class, 'spinSlots']);
Route::post('/api/roulette/spin', [CasinoController::class, 'spinRoulette']);
Route::post('/api/blackjack/new', [CasinoController::class, 'bjNew']);
Route::post('/api/blackjack/hit', [CasinoController::class, 'bjHit']);
Route::post('/api/blackjack/stand', [CasinoController::class, 'bjStand']);
Route::post('/api/dice/roll', [CasinoController::class, 'rollDice']);
Route::post('/api/sports/bet', [CasinoController::class, 'placeBet']);
Route::post('/api/sports/simulate', [CasinoController::class, 'simulateBets']);

// Wallet (Deposit / Withdraw)
Route::get('/api/wallet/history', [\App\Http\Controllers\WalletController::class, 'history']);
Route::post('/api/wallet/deposit', [\App\Http\Controllers\WalletController::class, 'deposit']);
Route::post('/api/wallet/withdraw', [\App\Http\Controllers\WalletController::class, 'withdraw']);
