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
Route::get('/crash', [CasinoController::class, 'crash'])->name('crash');
Route::get('/mines', [CasinoController::class, 'mines'])->name('mines');
Route::get('/hilo', [CasinoController::class, 'hilo'])->name('hilo');
Route::get('/plinko', [CasinoController::class, 'plinko'])->name('plinko');
Route::get('/chicken', [CasinoController::class, 'chicken'])->name('chicken');

// Poker (offline me bota + online me shokë)
Route::get('/poker', [\App\Http\Controllers\PokerController::class, 'index'])->name('poker');
Route::get('/api/poker/off/state', [\App\Http\Controllers\PokerController::class, 'offState']);
Route::post('/api/poker/off/sit', [\App\Http\Controllers\PokerController::class, 'offSit']);
Route::post('/api/poker/off/action', [\App\Http\Controllers\PokerController::class, 'offAction']);
Route::post('/api/poker/off/next', [\App\Http\Controllers\PokerController::class, 'offNext']);
Route::post('/api/poker/off/rebuy', [\App\Http\Controllers\PokerController::class, 'offRebuy']);
Route::post('/api/poker/off/leave', [\App\Http\Controllers\PokerController::class, 'offLeave']);
Route::post('/api/poker/rooms/create', [\App\Http\Controllers\PokerController::class, 'roomCreate']);
Route::post('/api/poker/rooms/join', [\App\Http\Controllers\PokerController::class, 'roomJoin']);
Route::post('/api/poker/rooms/addbot', [\App\Http\Controllers\PokerController::class, 'roomAddBot']);
Route::post('/api/poker/rooms/start', [\App\Http\Controllers\PokerController::class, 'roomStart']);
Route::post('/api/poker/rooms/next', [\App\Http\Controllers\PokerController::class, 'roomNext']);
Route::post('/api/poker/rooms/action', [\App\Http\Controllers\PokerController::class, 'roomAction']);
Route::get('/api/poker/rooms/state', [\App\Http\Controllers\PokerController::class, 'roomState']);
Route::post('/api/poker/rooms/leave', [\App\Http\Controllers\PokerController::class, 'roomLeave']);
Route::get('/sports', [CasinoController::class, 'sports'])->name('sports');

// API
Route::get('/api/balance', [CasinoController::class, 'getBalance']);
Route::post('/api/slots/spin', [CasinoController::class, 'spinSlots']);
Route::post('/api/roulette/spin', [CasinoController::class, 'spinRoulette']);
Route::post('/api/blackjack/new', [CasinoController::class, 'bjNew']);
Route::post('/api/blackjack/hit', [CasinoController::class, 'bjHit']);
Route::post('/api/blackjack/stand', [CasinoController::class, 'bjStand']);
Route::post('/api/dice/roll', [CasinoController::class, 'rollDice']);
Route::post('/api/crash/start', [CasinoController::class, 'crashStart']);
Route::post('/api/crash/state', [CasinoController::class, 'crashState']);
Route::post('/api/crash/cashout', [CasinoController::class, 'crashCashout']);
Route::post('/api/mines/start', [CasinoController::class, 'minesStart']);
Route::post('/api/mines/reveal', [CasinoController::class, 'minesReveal']);
Route::post('/api/mines/cashout', [CasinoController::class, 'minesCashout']);
Route::post('/api/hilo/start', [CasinoController::class, 'hiloStart']);
Route::post('/api/hilo/guess', [CasinoController::class, 'hiloGuess']);
Route::post('/api/hilo/cashout', [CasinoController::class, 'hiloCashout']);
Route::post('/api/plinko/table', [CasinoController::class, 'plinkoTable']);
Route::post('/api/plinko/drop', [CasinoController::class, 'plinkoDrop']);
Route::post('/api/chicken/table', [CasinoController::class, 'chickenTableApi']);
Route::post('/api/chicken/start', [CasinoController::class, 'chickenStart']);
Route::post('/api/chicken/go', [CasinoController::class, 'chickenGo']);
Route::post('/api/chicken/cashout', [CasinoController::class, 'chickenCashout']);
Route::post('/api/sports/bet', [CasinoController::class, 'placeBet']);
Route::post('/api/sports/simulate', [CasinoController::class, 'simulateBets']);

// Profili (setingjet)
Route::get('/api/profile', [\App\Http\Controllers\ProfileController::class, 'show']);
Route::post('/api/profile/update', [\App\Http\Controllers\ProfileController::class, 'update']);
Route::post('/api/profile/claim', [\App\Http\Controllers\ProfileController::class, 'claim']);
Route::post('/api/profile/password', [\App\Http\Controllers\ProfileController::class, 'password']);

// Wallet (Deposit / Withdraw)
Route::get('/api/wallet/history', [\App\Http\Controllers\WalletController::class, 'history']);
Route::post('/api/wallet/deposit', [\App\Http\Controllers\WalletController::class, 'deposit']);
Route::post('/api/wallet/withdraw', [\App\Http\Controllers\WalletController::class, 'withdraw']);
