<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    /**
     * Transactions belongs to a wallet
     */
    public function wallet()
    {
        return $this->belongsTo('App\Models\Wallet', 'walletNo');
    }
}
