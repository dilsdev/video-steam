<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('earnings:calculate')->dailyAt('01:00');
Schedule::command('tokens:clean')->hourly();
Schedule::command('memberships:refresh')->dailyAt('00:00');
