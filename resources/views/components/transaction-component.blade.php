<div class="container mt-4">
    <div class="card" style="margin-bottom: 30px;">
    <div class="card-body">
        <h4 class="card-title">Transaction Hash <br>
            <a href="{{ $istest ? "https://live.blockcypher.com/btc-testnet/tx/" . $transaction->txid : "https://live.blockcypher.com/btc/tx/" . $transaction->txid }}">{{ $transaction->txid }}</a>
        </h4>
        <p class="card-text">
            <div class="row">
                <div class="col-4">{{ $transaction->input_address }}</div>
                <div class="col-2">

                    <svg enable-background="new 0 0 32 32" height="32px" id="svg2" version="1.1" viewBox="0 0 32 32" width="32px" class="sc-1ub63u6-0 hDAkGl"><g id="background"><rect fill="none" height="32" width="32"></rect></g><g id="arrow_x5F_full_x5F_right"><polygon points="16,2.001 16,10 2,10 2,22 16,22 16,30 30,16  "></polygon></g></svg>
                </div>
                <div class="col-4">{{ $transaction->output1_address }}</div>
                <div class="col-2 text-right">{{ $transaction->output1_value }} Satoshi</div>
            </div>
            <div class="row">
                <div class="col-6">Fee: {{ $transaction->total_value - $transaction->output1_value - $transaction->output2_value }} Satoshi</div>
                <div class="col-6 text-right">{{ $transaction->created_at }}</div>
            </div>
        </p>
    </div>
    </div>
</div>
