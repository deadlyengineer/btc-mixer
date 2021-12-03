<?php

namespace App\Console\Commands;

use App\Http\Controllers\LayerController;
use App\Jobs\CreateWallet;
use App\Models\Mixer;
use Illuminate\Console\Command;

class StartMixerCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'startmixer:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        \Log::info("Cron is working fine!");
        $mixer = Mixer::where('is_process', 0)->first();

        if (isset($mixer)) {
            $this->create_wallet($mixer);

            $this->run_first_layer($mixer);

            $mixer->is_process = 2;
            $mixer->save();
        }

        return Command::SUCCESS;
    }

    /**
     * Create the new bitcoin wallets.
     *
     * @return int
     */
    public function create_wallet(Mixer $mixer)
    {
        $mixer->is_process = 1;
        $mixer->save();

        $wallet_data = CreateWallet::dispatchsync($mixer->is_test, 100);

        $mixer->start_wallet_id = $wallet_data['start_wallet_id'];
        $mixer->end_wallet_id = $wallet_data['end_wallet_id'];
        $mixer->save();

        return $mixer;
    }

    public function run_first_layer($mixer)
    {
        $layer = LayerController::run(0, $mixer);

        return $layer;
    }
}
