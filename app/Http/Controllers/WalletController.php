<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Create a new Wallet
     *
     * @param Request $request
     * @return void
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'phone_number' => 'required|unique:wallets',
        ]);

        $wallet               = new Wallet();
        $wallet->walletNo     = $this->walletNumberGenerator();
        $wallet->currency     = 'UGX';
        $wallet->country      = 'Uganda';
        $wallet->phone_number = $request->phone_number;
        $wallet->save();

        return response()->json([
            'message' => 'Wallet Created Successfully!',
            'wallet'  => $wallet,
        ]);
    }

    /**
     * Show Wallet Details
     *
     * @param String $phoneNumber
     * @return void
     */
    public function show($phoneNumber)
    {
        $wallet_details = Wallet::where('phone_number', $phoneNumber)->first();
        if (!$wallet_details) {
            return response()->json(['errors' => 'No Wallet Found']);
        } else {
            return response()->json(['message' => $wallet_details]);
        }
    }

    public function showBalance($phoneNumber)
    {
        $wallet = Wallet::where('phone_number', $phoneNumber)->first();
        if (!$wallet) {
            return response()->json(['errors' => 'No Wallet Found']);
        }
        return response()->json([
            'walletNo'            => $wallet->walletNo,
            'accountBalance'      => number_format($wallet->total_amount, 2),
            'currencyCode'        => $wallet->currency,
        ], 200);
    }

    public function depositMoneyToWallet(Request $request)
    {
        $this->validate($request, [
            'walletNo'      => 'required',
            // 'depositAmount' => 'required',
        ]);
        // Store the DB
        // Sleep
        if ($request->depositAmount <= 499) {
            // Store Transaction
            $transaction                     = new Transaction();
            $transaction->transaction_status = 'FAILED';
            $transaction->reason             = 'Deposit amount must be atleast 500 Ugandan shillings';
            $transaction->walletNo           = $request->walletNo;
            $transaction->transaction_type   = 'DEPOSIT';
            $transaction->reference_number   = $this->referenceNumberGenerator();
            $transaction->amount             = $request->depositAmount;
            $transaction->save();

            return response()->json([
                'transaction' => $transaction,
            ]);
        } else {
            // Store Transaction
            $transaction                     = new Transaction();
            $transaction->transaction_status = 'SUCCESS';
            $transaction->reason             = '';
            $transaction->walletNo           = $request->walletNo;
            $transaction->transaction_type   = 'DEPOSIT';
            $transaction->reference_number   = $this->referenceNumberGenerator();
            $transaction->amount             = $request->depositAmount;
            $transaction->save();

            $walletInformation               = Wallet::where('walletNo', $request->walletNo)->first();
            $walletInformation->total_amount = (intval($walletInformation->total_amount) + $request->depositAmount);
            $walletInformation->save();

            return response()->json([
                'message'        => 'You have deposited UGX ' . number_format($request->depositAmount, 0) . ' in your wallet',
                'accountBalance' => 'UGX ' . number_format($walletInformation->total_amount, 0),
                'transaction'    => $transaction,
            ]);
        }
    }

    public function sendMoneyToPhoneNumber(Request $request)
    {
        $this->validate($request, [
            'walletNo'            => 'required',
            'receiverPhoneNumber' => 'required',
            'amount'              => 'required',
        ]);
        $wallet = Wallet::where('walletNo', $request->walletNo)->first();
        if (!$wallet) {
            return response()->json([
                'errors' => 'No Wallet found with the wallet Number Provided',
            ]);
        } else {
            if ($request->amount <= 499) {
                // Store Transaction
                $transaction                     = new Transaction();
                $transaction->transaction_status = 'FAILED';
                $transaction->reason             = 'Transaction amount must be atleast 500 Ugandan shillings';
                $transaction->walletNo           = $request->walletNo;
                $transaction->transaction_type   = 'SENDING';
                $transaction->reference_number   = $this->referenceNumberGenerator();
                $transaction->amount             = $request->amount;
                $transaction->save();

                return response()->json([
                    'transaction' => $transaction,
                ]);
            } else {
                if ($wallet->total_amount < $request->amount) {
                    return response()->json([
                        'errors' => 'Insufficient funds, Please topup and try again',
                    ]);
                } else {
                    // Store Transaction
                    $transaction                     = new Transaction();
                    $transaction->transaction_status = 'SUCCESS';
                    $transaction->reason             = '';
                    $transaction->walletNo           = $request->walletNo;
                    $transaction->transaction_type   = 'SENDING';
                    $transaction->reference_number   = $this->referenceNumberGenerator();
                    $transaction->amount             = $request->amount;
                    $transaction->save();

                    $wallet->total_amount = (intval($wallet->total_amount) - $request->amount);
                    $wallet->save();

                    return response()->json([
                        'message'            => 'Transaction is successful UGX ' . number_format($request->amount, 2) . " to $request->receiverPhoneNumber",
                        'transaction'        => $transaction,
                        // 'wallet'             => $wallet,
                    ]);
                }
            }
        }
    }

    public function referenceNumberGenerator()
    {
        return strtoupper((substr(md5(time()), 0, 11)));
    }

    /**
     * Wallet Number Generator
     *
     * @return void
     */
    public function walletNumberGenerator()
    {
        return strtoupper((substr(md5(time()), 0, 10)));
    }
}
