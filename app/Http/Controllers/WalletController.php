<?php

namespace App\Http\Controllers;

use App\Models\Wallet;

class WalletController extends Controller
{
    public function get_private_key_by_address($address)
    {
        $wallet = Wallet::where('address', $address)->first();

        if (isset($wallet)) {
            return $wallet->wif;
        } else {
            return '0';
        }
    }
}
