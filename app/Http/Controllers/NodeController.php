<?php

namespace App\Http\Controllers;

use App\Models\Layer;
use App\Models\Node;

class NodeController extends Controller
{
    public function run(string $tx_id, string $wallet_address, Layer $layer)
    {
        $node = self::create($tx_id, $wallet_address, $layer);

        self::run_all_transactions($tx_id, $wallet_address, $node);

        return $node;
    }

    public function create(string $tx_id, string $wallet_address, Layer $layer)
    {
        $node = new Node;
        $node->tx_id = $tx_id;
        $node->wallet_address = $wallet_address;
        $node->layerId = $layer->id;
        $node->save();

        return $node;
    }

    public function run_all_transactions(string $tx_id, string $wallet_address, Node $node)
    {
        // run(string $payment_wallet_address, string $collection_wallet_address, string $output_txid, int $fees, int $sent_value, int $output_index, int $transaction_type, int $nodeid)
        Transaction::run($payment_wallet_address, $collection_wallet_address, $output_txid, $fees, $sent_value, $output_index, $transaction_type $node);
    }

}
