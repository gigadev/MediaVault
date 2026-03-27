<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('mediavault:send-overdue-reminders')->dailyAt('09:00');
