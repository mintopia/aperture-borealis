<?php

namespace App\Observers;

use App\Models\DeviceCode;
use Carbon\CarbonImmutable;
use Ramsey\Uuid\Uuid;

class DeviceCodeObserver
{
    public function saving(DeviceCode $deviceCode)
    {
        if (!$deviceCode->device_code) {
            $deviceCode->makeDeviceCode();
        }
        if (!$deviceCode->user_code) {
            do {
                $deviceCode->makeUserCode();
                $count = DeviceCode::whereUserCode($deviceCode->user_code)->count();
            } while ($count > 0);
        }
    }

    public function creating(DeviceCode $deviceCode)
    {
        if (!$deviceCode->expires_at) {
            $deviceCode->expires_at = CarbonImmutable::now()
                ->addSeconds($deviceCode->client->expires_in ?? 0);
        }
    }
}
