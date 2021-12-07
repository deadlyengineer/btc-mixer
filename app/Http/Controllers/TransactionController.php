<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Models\Transaction;
use BitWasp\Bitcoin\Address\AddressCreator;
use BitWasp\Bitcoin\Bitcoin;
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
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    public function run(string $payment_wallet_private_key, string $payment_wallet_address, string $collection_wallet_address, string $output_txid, int $tx_value, int $fees, int $sent_value, int $outpoint_index, bool $is_test, Node $node)
    {
        $txid = self::transaction($payment_wallet_private_key, $payment_wallet_address, $collection_wallet_address, $output_txid, $tx_value, $fees, $sent_value, $outpoint_index, $is_test);

        $transaction = self::create($txid, $payment_wallet_address, $collection_wallet_address, $output_txid, $tx_value, $fees, $sent_value, 0, $is_test, $node);

        return $transaction;
    }

    public function transaction(string $payment_wallet_private_key, string $payment_wallet_address, string $collection_wallet_address, string $output_txid, int $tx_value, int $fees, int $sent_value, int $outpoint_index, bool $is_test)
    {
        Log::info('payment wallet address: ' . $payment_wallet_address);
        Log::info('collection wallet address: ' . $collection_wallet_address);
        if ($is_test) {
            Bitcoin::setNetwork(NetworkFactory::bitcoinTestnet());
            $url = 'https://api.blockcypher.com/v1/btc/test3/txs/push';
        } else {
            $url = 'https://api.blockcypher.com/v1/btc/txs/push';
        }

        $network = Bitcoin::getNetwork();
        // Private key of payment wallet (wif format)
        // $payment_wallet_private_key = 'cW9yzoYt9DHyUXhPNPAjVbJZMuvhJL9jpCXJ7kjkpEoaBFMBjuFV';
        // id of the last transaction of the payment Wallet
        // $output_txid = '4b52a0e40704c73493bf75d2de1c79387978a58bae52793c33a6515839c140fb';
        // Collection wallet address (p2pkh format)
        // $collection_wallet_address = 'mpQ4jpbDTGQRHtx3u9MD31rb8NEa7VU89S';

        // $tx_value = 57131;
        // $outpoint_index = 0;

        // $fees = 250; //satoshi
        $rest_value = $tx_value - $fees - $sent_value;

        $privKeyFactory = new PrivateKeyFactory;
        $key = $privKeyFactory->fromWif($payment_wallet_private_key);

        $witnessScript = new WitnessScript(
            ScriptFactory::scriptPubKey()->payToPubKeyHash($key->getPubKeyHash())
        );

        $addressCreator = new AddressCreator();

        // UTXO
        $outpoint = new OutPoint(Buffer::hex($output_txid, 32), $outpoint_index);
        // A total of 100000 (0.00000001 BTC = 1 Cong) will be spent here
        $txOut = new TransactionOutput(100000, $witnessScript); //??????????

        $builder = (new TxBuilder())
            ->spendOutPoint($outpoint)
            ->payToAddress($sent_value, $addressCreator->fromString($collection_wallet_address))
            ->payToAddress($rest_value, $addressCreator->fromString($payment_wallet_address));

        $signer = new Signer($builder->get(), Bitcoin::getEcAdapter());
        $input = $signer->input(0, $txOut);
        $input->sign($key);

        $signed = $signer->get();

        Log::info("raw: {$signed->getHex()}\n");
        Log::info("txid: {$signed->getTxId()->getHex()}\n");
        Log::info("input valid? " . ($input->verify() ? "true" : "false") . '<br>');

        // Transactions requiring broadcast
        $broadcast = $signed->getBaseSerialization()->getHex();
        Log::info('<br>' . $broadcast);
        $client = new Client;
        try {
            $response = $client->request('POST', $url, [
                'json' => [
                    'tx' => $signed->getBaseSerialization()->getHex(),
                ],
            ]);
            Log::info('ok');
            Log::info(json_decode($response->getBody(), true));
            return $signed->getTxId()->getHex();

        } catch (ClientException $e) {
            Log::info('error');
            Log::info($e->getResponse());
            return 0;
        }
    }

    public function create(string $txid, string $input_address, string $output1_address, string $input_outpoint_txid, int $total_value, int $fees, int $output1_value, int $sent_index, bool $is_test, Node $node)
    {
        $transaction = new Transaction;
        $transaction->txid = $txid;
        $transaction->total_value = $total_value;
        $transaction->fees = $fees;
        $transaction->input_address = $input_address;
        $transaction->input_outpoint_txid = $input_outpoint_txid;
        $transaction->output1_address = $output1_address;
        $transaction->output1_value = $output1_value;
        $transaction->output2_address = $input_address;
        $transaction->output2_value = $total_value - $output1_value - $fees;
        $transaction->sent_index = $sent_index;
        $transaction->layer_deep = $node->layer->layer_deep;
        $transaction->nodeid = $node->id;
        $transaction->save();

        return $transaction;
    }
}
