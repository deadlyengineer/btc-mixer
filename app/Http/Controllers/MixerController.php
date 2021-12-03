<?php

namespace App\Http\Controllers;

use App\Models\Mixer;
use Illuminate\Http\Request;

class MixerController extends Controller
{
    public function index()
    {
        $mixer = Mixer::where('is_process', '<', 2)->first();

        if (isset($mixer)) {
            return view('mixer.index', ['is_process' => 1]);
        } else {
            return view('mixer.index', ['is_process' => 0]);
        }
    }
    public function start_mixer(Request $request)
    {
        // $wallet_data = CreateWallet::dispatch(1, 10000);

        $mixer = new Mixer;
        $mixer->from_wallet_address = $request->from_wallet_address;
        $mixer->from_wallet_private_key = $request->from_wallet_private_key;
        $mixer->from_txid = $request->from_txid;
        $mixer->value = $request->value;
        $mixer->to_wallet_address = $request->to_wallet_address;
        $mixer->level = $request->level;
        $mixer->deep = $request->deep;
        $mixer->is_test = $request->is_test == 1;
        $mixer->is_process = 0;
        $mixer->save();

        return redirect()->route('home');
    }
}
