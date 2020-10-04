<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'walletNo',
        'currency',
        'country',
        'phone_number',
        'total_amount',
    ];

    /**
     * Wallet has many transactions
     */
    public function transactions()
    {
        return $this->hasMany('App\Models\Transaction', 'walletNo');
    }
}
