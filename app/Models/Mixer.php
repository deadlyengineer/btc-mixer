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
}
