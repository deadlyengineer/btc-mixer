<?php

namespace App\Http\Controllers;

use App\Jobs\CreateWallet;
use App\Models\Mixer;
use Illuminate\Http\Request;

class MixerController extends Controller
{
    public function index()
    {
        return view('mixer.index');
    }
    public function start_mixer(Request $request)
    {
        $wallet_data = CreateWallet::dispatchSync(1, 3);

        $mixer = new Mixer;
        $mixer->from_wallet_address = $request->from_wallet_address;
        $mixer->from_wallet_private_key = $request->from_wallet_private_key;
        $mixer->from_txid = $request->from_txid;
        $mixer->value = $request->value;
        $mixer->to_wallet_address = $request->to_wallet_address;
        $mixer->level = $request->level;
        $mixer->deep = $request->deep;
        $mixer->start_wallet_id = $wallet_data['start_wallet_id'];
        $mixer->end_wallet_id = $wallet_data['end_wallet_id'];
        $mixer->is_test = $request->is_test == 1;
        $mixer->is_process = true;
        $mixer->save();

        return $mixer;
    }
}
