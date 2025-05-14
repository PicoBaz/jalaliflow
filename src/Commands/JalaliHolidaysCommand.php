<?php

namespace PicoBaz\JalaliFlow\Commands;

use Illuminate\Console\Command;
use PicoBaz\JalaliFlow\Facades\JalaliFlow;

class JalaliHolidaysCommand extends Command
{
    protected $signature = 'jalali:holidays {year}';
    protected $description = 'Display holidays for a given Jalali year';

    public function handle()
    {
        $year = $this->argument('year');
        $holidays = JalaliFlow::getHolidays();

        $this->info("Holidays for $year:");
        foreach ($holidays as $date => $name) {
            if (strpos($date, $year) === 0) {
                $this->line("$date: $name");
            }
        }
    }
}
