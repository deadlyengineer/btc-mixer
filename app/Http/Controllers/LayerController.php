<?php

namespace App\Http\Controllers;

use App\Http\Controllers\NodeController;
use App\Models\Layer;
use App\Models\Mixer;
use App\Models\Transaction;

class LayerController extends Controller
{
    public function run(int $layer_deep, Mixer $mixer)
    {
        $layer = self::create($layer_deep, $mixer);

        self::run_all_nodes($layer_deep, $layer);

        return $layer;
    }

    public function create(int $layer_deep, Mixer $mixer)
    {
        $layer = new Layer;
        $layer->mixerId = $mixer->id;
        $layer->layer_deep = $layer_deep;
        $layer->save();

        return $layer;
    }

    public function run_all_nodes(int $layer_deep, Layer $layer)
    {
        $mixer = $layer->mixer;
        $deep = $mixer->deep;
        $mixerId = $mixer->id;

        if ($layer_deep == 0) {
            $tx_id = $mixer->from_txid;
            $wallet_address = $mixer->from_wallet_address;
            $node = NodeController::run($tx_id, $wallet_address, $layer);

            return $layer;
        }

        $transactions = Transaction::where('layer_deep', $layer_deep - 1)->where('mixerId', $mixerId)->get();

        foreach ($transactions as $transaction) {
            $tx_id = $transaction->txid;
            $wallet_address = $transaction->output1_address;
            $node = NodeController::run($tx_id, $wallet_address, $layer);
        }

        return $layer;
    }
}
