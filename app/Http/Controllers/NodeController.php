<?php

namespace App\Http\Controllers;

use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use App\Models\Layer;
use App\Models\Node;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\Log;

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
        $fees = 250; //satoshi
        $layer = $node->layer;
        $mixer = $layer->mixer;
        $layer_deep = $layer->layer_deep;
        $deep = $mixer->deep;
        $is_test = $mixer->is_test;
        $start_wallet_id = $mixer->start_wallet_id;

        if ($layer_deep == 0) {
            $level = $mixer->get_wallet_count();
            $tx_value = $mixer->value;
            $payment_wallet_address = $mixer->from_wallet_address;
            $payment_wallet_private_key = $mixer->from_wallet_private_key;

            $devided_values = self::devide_random_value($tx_value, $level, $layer_deep, $deep);

            $output_txid = $mixer->from_txid;
            $outpoint_index = 0;

            for ($index = 0; $index < count($devided_values); $index++) {

                $collection_wallet_address = Wallet::where('id', $start_wallet_id + $index)->first()->address;
                $sent_value = $devided_values[$index];

                $transaction = TransactionController::run($payment_wallet_private_key, $payment_wallet_address, $collection_wallet_address, $output_txid, $tx_value, $fees, $sent_value, $outpoint_index, $is_test, $node);

                $tx_value = $transaction->output2_value;
                $output_txid = $transaction->txid;
                $outpoint_index = 1;
            }

            return 1;
        }

        if ($layer_deep == $deep - 1) {
            $payment_wallet_private_key = WalletController::get_private_key_by_address($wallet_address);
            $payment_wallet_address = $wallet_address;
            $collection_wallet_address = $mixer->to_wallet_address;
            $output_txid = $tx_id;

            $transaction = Transaction::where('txid', $tx_id)->first();
            $tx_value = $transaction->output1_value;
            $sent_value = $tx_value - $fees;
            $outpoint_index = 0;

            $transaction = TransactionController::run($payment_wallet_private_key, $payment_wallet_address, $collection_wallet_address, $output_txid, $tx_value, $fees, $sent_value, $outpoint_index, $is_test, $node);
            return 1;
        }

        $level = $mixer->level;
        $transaction = Transaction::where('txid', $tx_id)->first();

        $tx_value = $transaction->output1_value;
        $payment_wallet_private_key = WalletController::get_private_key_by_address($wallet_address);
        $payment_wallet_address = $wallet_address;

        $devided_values = self::devide_random_value($tx_value, $level, $layer_deep, $deep);

        $output_txid = $tx_id;
        $outpoint_index = 0;

        for ($index = 0; $index < count($devided_values); $index++) {
            $wallet_count = $mixer->get_wallet_count();
            $wallet_index = rand(0, $wallet_count - 1);

            $collection_wallet_address = Wallet::where('id', $start_wallet_id + $wallet_index)->first()->address;
            $sent_value = $devided_values[$index];

            $transaction = TransactionController::run($payment_wallet_private_key, $payment_wallet_address, $collection_wallet_address, $output_txid, $tx_value, $fees, $sent_value, $outpoint_index, $is_test, $node);

            $tx_value = $transaction->output2_value;
            $output_txid = $transaction->txid;
            $outpoint_index = 1;
        }

        return 1;
    }

    public function devide_random_value(int $value, int $count, int $layer_deep, int $deep)
    {
        $mass = [];
        $result = [];
        $fees = 250; //satoshi

        Log::info('value: ' . $value);
        $min_value = ($deep - $layer_deep) * $fees;
        Log::info('min value: ' . $min_value);

        if ($value < 2 * $min_value * $count) {
            Log::info('The wallets are too many...');
            $count = floor($value / $min_value / 2);
            Log::info('wallet count: ' . $count);
        }

        if ($count == 0) {
            array_push($result, $value - $fees);
            return $result;
        }

        for ($index = 0; $index < $count; $index++) {
            array_push($mass, rand(1, 100));
        }

        $sum = array_sum($mass);

        for ($index = 0; $index < $count - 1; $index++) {
            array_push($result, floor($mass[$index] * ($value - $count * $min_value) / $sum + $min_value - $fees));
        }

        $result[$count - 1] = $value - $fees * $count - array_sum($result);

        return $result;

    }
}
