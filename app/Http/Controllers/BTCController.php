<?php

namespace App\Http\Controllers;

use BitWasp\Bitcoin\Address\AddressCreator;
use BitWasp\Bitcoin\Address\PayToPubKeyHashAddress;
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Key\Factory\PrivateKeyFactory;
use BitWasp\Bitcoin\Network\NetworkFactory;
use BitWasp\Bitcoin\Script\ScriptFactory;
use BitWasp\Bitcoin\Script\WitnessScript;
use BitWasp\Bitcoin\Transaction\Factory\Signer;
use BitWasp\Bitcoin\Transaction\Factory\TxBuilder;
use BitWasp\Bitcoin\Transaction\OutPoint;
use BitWasp\Bitcoin\Transaction\TransactionOutput;
use BitWasp\Buffertools\Buffer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class BTCController extends Controller
{
    public function createWallet()
    {
        Bitcoin::setNetwork(NetworkFactory::bitcoinTestnet());
        $network = Bitcoin::getNetwork();

        $random = new Random();
        $private_key_factory = new PrivateKeyFactory();
        $private_key = $private_key_factory->generateCompressed($random);
        $public_key = $private_key->getPublicKey();

        // Address in p2pkh format
        $address = new PayToPubKeyHashAddress($public_key->getPubKeyHash());

        // Save the generated wallet to the database
        // $wallet = new Wallet;
        // $wallet->wif = $private_key->toWif($network);
        // $wallet->address = $address->getAddress();

        // $wallet->save();

        // Save to log file
        // $log = "wif: " . $wallet->wif . PHP_EOL .
        // "address: " . $wallet->address . PHP_EOL .
        // "-------------" . date('Y-m-d Hs') . "------------" . PHP_EOL;

        $wallet_wif = $private_key->toWif($network);
        $wallet_address = $address->getAddress();
        $log = "wif: " . $wallet_wif . PHP_EOL .
        "address: " . $wallet_address . PHP_EOL .
        "-------------" . date('Y-m-d Hs') . "------------" . PHP_EOL;
        var_dump($address);
        echo '<br>';
        echo $log;
        file_put_contents('./wallets.log', $log, FILE_APPEND);
        // object(BitWasp\Bitcoin\Address\PayToPubKeyHashAddress)#316 (1) { ["hash":protected]=> object(BitWasp\Buffertools\Buffer)#310 (2) { ["size"]=> int(20) ["buffer"]=> string(42) "0xbb0922725605219611a595846d1abeabc09c4aef" } }
        // wif: cW9yzoYt9DHyUXhPNPAjVbJZMuvhJL9jpCXJ7kjkpEoaBFMBjuFV address: mxZubc8QMx4VuX8VnLc1oxQURthfuSsNqx -------------2021-11-30 0421-----------
    }

    public function createTransaction()
    {
        Bitcoin::setNetwork(NetworkFactory::bitcoinTestnet());
        $network = Bitcoin::getNetwork();
        // Private key of payment wallet (wif format)
        $wif = 'cW9yzoYt9DHyUXhPNPAjVbJZMuvhJL9jpCXJ7kjkpEoaBFMBjuFV';
        // id of the last transaction of the payment Wallet
        $txid = '4b52a0e40704c73493bf75d2de1c79387978a58bae52793c33a6515839c140fb';
        // Collection wallet address (p2pkh format)
        $address = 'mpQ4jpbDTGQRHtx3u9MD31rb8NEa7VU89S';

        $tx_value = 57131;
        $outpoint_index = 1;

        $fee = 250;
        $send_value = 10000;
        $rest_value = $tx_value - $fee - $send_value;

        $privKeyFactory = new PrivateKeyFactory;
        $key = $privKeyFactory->fromWif($wif);

        $witnessScript = new WitnessScript(
            ScriptFactory::scriptPubKey()->payToPubKeyHash($key->getPubKeyHash())
        );

        $addressCreator = new AddressCreator();

        // UTXO
        $outpoint = new OutPoint(Buffer::hex($txid, 32), $outpoint_index);
        // A total of 100000 (0.00000001 BTC = 1 Cong) will be spent here
        $txOut = new TransactionOutput(100000, $witnessScript);

        // The payee will receive 90000 Cong, and the difference between them will be the handling charge of the miner
        $builder = (new TxBuilder())
            ->spendOutPoint($outpoint)
            ->payToAddress($send_value, $addressCreator->fromString($address))
            ->payToAddress($rest_value, $addressCreator->fromString('mxZubc8QMx4VuX8VnLc1oxQURthfuSsNqx'));

        $signer = new Signer($builder->get(), Bitcoin::getEcAdapter());
        $input = $signer->input(0, $txOut);
        $input->sign($key);

        $signed = $signer->get();

        echo "txid: {$signed->getTxId()->getHex()}\n";
        echo "raw: {$signed->getHex()}\n";
        echo "input valid? " . ($input->verify() ? "true" : "false") . '<br>';
        // dd($signed);
        // $new = $signer->get();

        // Transactions requiring broadcast
        $broadcast = $signed->getBaseSerialization()->getHex();
        echo '<br>' . $broadcast;
        // die;

        // I use it here blockchain.info  You can use your own node, or an existing RPC interface
        // echo $signed->getBaseSerialization()->getHex();
        // echo '<br>';
        // echo $signed->getTxId()->getHex();
        // echo '<br>';
        $client = new Client;
        try {
            // $response = $client->request('POST', 'https://blockchain.info/rawtx/' . $signed->getBaseSerialization()->getHex());
            $response = $client->request('POST', 'https://api.blockcypher.com/v1/btc/test3/txs/push', [
                'json' => [
                    'tx' => $signed->getBaseSerialization()->getHex(),
                ],
            ]);
            echo 'ok<br>';
            dd(json_decode($response->getBody(), true));
        } catch (ClientException $e) {
            echo 'error<br>';
            // var_dump($e->getResponse());
            dd($e->getResponse());
        }
    }
}
