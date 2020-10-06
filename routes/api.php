<?php

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

Route::get('/', function () {
    return response()->json(['message' => 'Lipa Mobile API']);
});

Route::post('wallet/create', 'WalletController@create');
Route::get('wallet/{phoneNumber}', 'WalletController@show');

Route::get('/wallet/balance/{phoneNumber}', 'WalletController@showBalance');

// Wallet Topup - A Deposit Transaction - Pending, Failed & Successful
Route::post('/wallet/depositMoney', 'WalletController@depositMoneyToWallet');

// moving money from my wallet to another (PhoneNumber)
Route::post('/wallet/sendMoney', 'WalletController@sendMoneyToPhoneNumber');

// GetTransactionStatus
Route::get('/getTransactionStatus/{transactionId}', 'TransactionController@getTransactionStatus');

// List of Transaction using phoneNumber
Route::get('/getAllTransactions/{phoneNumber}', 'TransactionController@getAllTransactions');
