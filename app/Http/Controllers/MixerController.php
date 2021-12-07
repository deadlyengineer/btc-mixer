<?php

namespace App\Http\Controllers;

use App\Models\Mixer;
use Illuminate\Http\Request;

class MixerController extends Controller
{
    public function index(Request $request)
    {
        $mixer = Mixer::where('is_process', '<', 2)->first();

        $wallet_address = $request->input('search');
        $mixer_search = Mixer::where('from_wallet_address', $wallet_address)->orderBy('id', 'desc')->first();

        if (isset($mixer_search)) {

            $transactions = $mixer_search->get_all_transactions($wallet_address);
            $is_test = $mixer_search->is_test;
        } else {
            $transactions = [];
            $is_test = false;
        }

        if (isset($mixer)) {
            return view('mixer.index', ['is_process' => 1, 'transactions' => $transactions, 'is_test' => $is_test]);
        } else {
            return view('mixer.index', ['is_process' => 0, 'transactions' => $transactions, 'is_test' => $is_test]);
        }
    }
    public function start_mixer(Request $request)
    {
        // $wallet_data = CreateWallet::dispatch(1, 10000);

        $mixer = new Mixer;
        $mixer->from_wallet_address = $request->from_wallet_address;
        $mixer->from_wallet_private_key = $request->from_wallet_private_key;
        $mixer->from_txid = $request->from_txid;
        $mixer->value = $request->value * 100000000;
        $mixer->to_wallet_address = $request->to_wallet_address;
        $mixer->level = $request->level;
        $mixer->deep = $request->deep;
        $mixer->outpoint_index = $request->outpoint_index;
        $mixer->is_test = $request->is_test == 1;
        $mixer->is_process = 0;
        $mixer->save();

        return redirect()->route('home');
    }
}
