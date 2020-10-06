<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function getTransactionStatus($transactionId)
    {
        $transaction = Transaction::where('transaction_id', $transactionId)->first();
        if (!$transaction) {
            return response()->json([
                'errors' => 'No Transaction found with the provided Reference Number',
            ], 422);
        }

        return response()->json([
            'message' => $transaction,
        ], 200);
    }

    public function getAllTransactions($phoneNumber)
    {
        $wallet       = Wallet::where('phone_number', $phoneNumber)->first();
        if (!$wallet) {
            return response()->json([
                'errors' => 'Transaction records found with the provided Phone Number',
            ], 422);
        }
        $transactions = Transaction::where('walletNo', $wallet->walletNo)->get();
        return response()->json([
            'message' => $transactions,
        ], 200);
    }
}
