<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function getTransactionStatus($referenceNumber)
    {
        $transaction = Transaction::where('reference_number', $referenceNumber)->first();
        if (!$transaction) {
            return response()->json([
                'errors' => 'No Transaction found with the provided Reference Number',
            ]);
        }

        return response()->json([
            'message' => $transaction,
        ]);
    }

    public function getAllTransactions($phoneNumber)
    {
        $wallet       = Wallet::where('phone_number', $phoneNumber)->first();
        if (!$wallet) {
            return response()->json([
                'errors' => 'Transaction records found with the provided Phone Number',
            ]);
        }
        $transactions = Transaction::where('walletNo', $wallet->walletNo)->get();
        return response()->json([
            'message' => $transactions,
        ]);
    }
}
