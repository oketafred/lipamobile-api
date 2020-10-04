<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
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
            'message' => 'Wallet Crated Successfully!',
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
        $wallet_details = Wallet::where('phone_number', $phoneNumber)->get();
        return response()->json(['message' => $wallet_details]);
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
