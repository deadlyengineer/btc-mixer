<?php

namespace App\Jobs;

use App\Models\Wallet;
use BitWasp\Bitcoin\Address\PayToPubKeyHashAddress;
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Key\Factory\PrivateKeyFactory;
use BitWasp\Bitcoin\Network\NetworkFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateWallet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $is_test;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($is_test)
    {
        $this->is_test = $is_test;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->is_test) {
            Bitcoin::setNetwork(NetworkFactory::bitcoinTestnet());
        }
        $network = Bitcoin::getNetwork();

        $random = new Random();
        $private_key_factory = new PrivateKeyFactory();
        $private_key = $private_key_factory->generateCompressed($random);
        $public_key = $private_key->getPublicKey();

        // Address in p2pkh format
        $address = new PayToPubKeyHashAddress($public_key->getPubKeyHash());

        //Save the generated wallet to the database
        $wallet = new Wallet;
        $wallet->wif = $private_key->toWif($network);
        $wallet->address = $address->getAddress();
        $wallet->is_test = $this->is_test;

        $wallet->save();

        return $wallet;
    }
}
