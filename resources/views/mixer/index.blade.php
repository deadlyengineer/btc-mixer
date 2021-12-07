<!DOCTYPE html>
<html>
<head>
    <title>BTC Mixer</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
  @if(session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>
  @endif
  <div class="card">
    <div class="card-header text-center font-weight-bold">
        Welcome to Bitcoin Mixer
    </div>
    <div class="card-body">
      @if ($is_process == 1)
        <div class="alert alert-warning">
          Mixer is processing now. You cannot start a new mixer.
        </div>
      @endif
      <form name="bitcoin-mixer-form" id="bitcoin-mixer-form" method="post" action="{{route('mixer.start')}}">
       @csrf
        <div class="form-group">
          <label for="from_wallet_address">Wallet Address</label>
          <input type="text" id="from_wallet_address" name="from_wallet_address" class="form-control" required="">
        </div>
        <div class="form-group">
          <label for="from_wallet_private_key">Wallet Private Key</label>
          <input type="text" id="from_wallet_private_key" name="from_wallet_private_key" class="form-control" required="">
        </div>
        <div class="form-group">
          <label for="from_txid">Last transaction Hash</label>
          <input type="text" id="from_txid" name="from_txid" class="form-control" required="">
        </div>
        <div class="form-group">
          <label for="outpoint_index">Last transaction Outpoint Index</label>
          <input type="number" id="outpoint_index" name="outpoint_index" class="form-control" min="0" required="">
        </div>
        <div class="form-group">
          <label for="to_wallet_address">Target Wallet Address</label>
          <input type="text" id="to_wallet_address" name="to_wallet_address" class="form-control" required="">
        </div>
        <div class="form-group">
          <label for="value">Total Value (BTC)</label>
          <input type="number" id="value" name="value" class="form-control" min="0" step="any" required="">
        </div>
        <div class="form-group">
          <label for="level">Level</label>
          <input type="number" id="level" name="level" class="form-control" min="1" required="" value="3">
        </div>
        <div class="form-group">
          <label for="deep">Deep</label>
          <input type="number" id="deep" name="deep" class="form-control" min="2" required="" value="4">
        </div>
        <div class="form-group">
            <label for="is_test">Network Type</label>
            <select id="is_test" name="is_test">
                <option value="0" disabled>Mainnet</option>
                <option value="1" selected>Testnet</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" {{ $is_process == 1 ? 'disabled' : '' }}>Start Mixer</button>

      </form>
    </div>
  </div>

  <div class="container mt-4">
    <div class="row">
      <div class="col-6">
        <h2 class="text-primary">Transaction History</h2>
      </div>
      <div class="col-6 d-flex justify-content-end">
        <form action="{{ route('home') }}" method="GET">
          <input type="text" name="search" placeholder="Enter wallet address..."/>
          <button type="submit" class="btn btn-primary">Search</button>
        </form>
      </div>
    </div>
    @if (count($transactions) > 0)

    <div class="d-flex justify-content-center">
        {{ $transactions->links("pagination::bootstrap-4") }}
    </div>
    @foreach ($transactions as $transaction)
      <x-transaction-component :transaction="$transaction" :istest="$is_test" />
    @endforeach
    <div class="d-flex justify-content-center">
        {{ $transactions->links("pagination::bootstrap-4") }}
    </div>
    @else
    <div class="d-flex justify-content-center">
      Not Found Transaction
    </div>
    @endif
  </div>
</div>
</body>
</html>
