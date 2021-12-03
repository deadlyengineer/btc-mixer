<?php

namespace App\Http\Controllers;

use App\Models\Node;

class TransactionController extends Controller
{
    public function run(string $payment_wallet_address, string $collection_wallet_address, string $output_txid, int $fees, int $sent_value, int $output_index, int $transaction_type, Node $node)
    {

    }
}
