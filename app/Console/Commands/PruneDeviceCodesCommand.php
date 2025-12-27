<?php

namespace App\Console\Commands;

use App\Models\DeviceCode;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class PruneDeviceCodesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'borealis:prune-device-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove expired device codes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $numberDeleted = DeviceCode::where('expires_at', '<', CarbonImmutable::now()->subDay())->delete();
        $this->output->info("Deleted {$numberDeleted} expired device codes");
    }
}
