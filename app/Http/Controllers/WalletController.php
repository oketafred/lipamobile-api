<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Rules\AmountValidation;
use App\Rules\AmountMaxValidation;

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
            'phone_number' => 'required|digits:12|unique:wallets',
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
        ], 200);
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
            return response()->json(['errors' => 'No Wallet Found'], 422);
        } else {
            return response()->json(['message' => $wallet_details], 200);
        }
    }

    public function showBalance($phoneNumber)
    {
        $wallet = Wallet::where('phone_number', $phoneNumber)->first();
        if (!$wallet) {
            return response()->json(['errors' => 'No Wallet Found'], 422);
        }
        return response()->json([
            'walletNo'            => $wallet->walletNo,
            'accountBalance'      => number_format($wallet->account_balance, 2),
            'currencyCode'        => $wallet->currency,
        ], 200);
    }

    public function depositMoneyToWallet(Request $request)
    {
        $this->validate($request, [
            'phone_number'      => 'required',
            'depositAmount'     => new AmountValidation(),
            'depositAmount'     => new AmountMaxValidation(),
        ]);
        $walletInformation                  = Wallet::where('phone_number', $request->phone_number)->first();
        // Store the DB
        if ($request->depositAmount <= 499) {
            // Store Transaction
            $transaction                     = new Transaction();
            $transaction->transaction_status = 'FAILED';
            $transaction->reason             = 'Deposit amount must be atleast 500 Ugandan shillings';
            $transaction->walletNo           = $walletInformation->walletNo;
            $transaction->transaction_type   = 'DEPOSIT';
            $transaction->transaction_id     = $this->referenceNumberGenerator();
            $transaction->amount             = $request->depositAmount;
            $transaction->save();

            return response()->json([
                'transaction' => $transaction,
            ], 422);
        } else {
            // Store Transaction
            $transaction                     = new Transaction();
            $transaction->transaction_status = 'SUCCESS';
            $transaction->reason             = '';
            $transaction->walletNo           = $walletInformation->walletNo;
            $transaction->transaction_type   = 'DEPOSIT';
            $transaction->transaction_id     = $this->referenceNumberGenerator();
            $transaction->amount             = $request->depositAmount;
            $transaction->save();

            $walletInformation->account_balance = (intval($walletInformation->account_balance) + $request->depositAmount);
            $walletInformation->save();

            return response()->json([
                'message'        => 'You have deposited UGX ' . number_format($request->depositAmount, 0) . ' in your wallet',
                'accountBalance' => 'UGX ' . number_format($walletInformation->account_balance, 0),
                'transaction'    => $transaction,
            ], 200);
        }
    }

    public function sendMoneyToPhoneNumber(Request $request)
    {
        $this->validate($request, [
            'senderPhoneNumber'            => 'required|digits:12',
            'receiverPhoneNumber'          => 'required|digits:12',
            'amount'                       => new AmountValidation(),
            'amount'                       => new AmountMaxValidation(),
        ]);
        $wallet = Wallet::where('phone_number', $request->senderPhoneNumber)->first();
        if (!$wallet) {
            return response()->json([
                'errors' => 'No Wallet found with the Phone Number Provided',
            ], 422);
        } else {
            if ($request->amount <= 499) {
                // Store Transaction
                $transaction                     = new Transaction();
                $transaction->transaction_status = 'FAILED';
                $transaction->reason             = 'Transaction amount must be atleast 500 Ugandan shillings';
                $transaction->walletNo           = $wallet->walletNo;
                $transaction->transaction_type   = 'SENDING';
                $transaction->transaction_id     = $this->referenceNumberGenerator();
                $transaction->amount             = $request->amount;
                $transaction->save();

                return response()->json([
                    'transaction' => $transaction,
                ], 422);
            } else {
                if ($wallet->account_balance < $request->amount) {
                    return response()->json([
                        'errors' => 'Insufficient funds, Please topup and try again',
                    ], 422);
                } else {
                    // Store Transaction
                    $transaction                     = new Transaction();
                    $transaction->transaction_status = 'SUCCESS';
                    $transaction->reason             = '';
                    $transaction->walletNo           = $wallet->walletNo;
                    $transaction->transaction_type   = 'SENDING';
                    $transaction->transaction_id     = $this->referenceNumberGenerator();
                    $transaction->amount             = $request->amount;
                    $transaction->save();

                    $wallet->account_balance = (intval($wallet->account_balance) - $request->amount);
                    $wallet->save();

                    return response()->json([
                        'message'            => 'Transaction is successful UGX ' . number_format($request->amount, 2) . " to $request->receiverPhoneNumber",
                        'transaction'        => $transaction,
                    ], 200);
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
