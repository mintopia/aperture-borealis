<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('borealis:prune-device-codes')
    ->everyFifteenMinutes()
    ->onOneServer();
