<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mixer extends Model
{
    use HasFactory;

    public function get_wallet_count()
    {
        $start_wallet_id = $this->start_wallet_id;
        $end_wallet_id = $this->end_wallet_id;

        return $end_wallet_id - $start_wallet_id + 1;
    }

    public function layers()
    {
        return $this->hasMany(Layer::class, 'mixerId');
    }

    public function get_all_transactions($query)
    {
        $transaction_node_ids = [];
        $layers = $this->layers;

        foreach ($layers as $layer) {
            $nodes = $layer->nodes;
            foreach ($nodes as $node) {
                array_push($transaction_node_ids, $node->id);
            }
        }

        $transactions = [];

        foreach ($transaction_node_ids as $key => $node_id) {
            if ($key == 0) {
                $transactions = Transaction::where('nodeId', $node_id);
            } else {
                $transactions = $transactions->orWhere('nodeId', $node_id);
            }
        }

        $transactions = $transactions->paginate(5)->withQueryString();

        return $transactions;
    }
}
