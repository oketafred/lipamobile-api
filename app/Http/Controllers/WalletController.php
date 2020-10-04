<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
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
     * Wallet Number Generator
     *
     * @return void
     */
    public function walletNumberGenerator()
    {
        return strtoupper((substr(md5(time()), 0, 10)));
    }
}
