<?php

namespace App\Enums;

enum DeviceCodeStatus
{
    case dcsPending;
    case dcsSuccessful;
    case dcsFailed;
}
