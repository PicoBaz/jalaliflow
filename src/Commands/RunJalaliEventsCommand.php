<?php

namespace PicoBaz\JalaliFlow\Commands;

use Illuminate\Console\Command;
use PicoBaz\JalaliFlow\JalaliEvent;
use PicoBaz\JalaliFlow\Facades\JalaliFlow;

class RunJalaliEventsCommand extends Command
{
    protected $signature = 'jalali:run-events';
    protected $description = 'Run scheduled Jalali events';

    public function handle()
    {
        $today = JalaliFlow::toJalali(now()->format('Y-m-d'));
        $events = JalaliEvent::where('next_run', $today)->get();

        foreach ($events as $event) {
            try {
                $action = unserialize($event->action);
                if (is_callable($action)) {
                    call_user_func($action);
                } elseif (is_string($action)) {
                    [$class, $method] = explode('@', $action);
                    (new $class)->{$method}();
                }

                $event->update([
                    'next_run' => JalaliFlow::getNextRunDate($event->next_run, $event->frequency),
                ]);

                $this->info("Event '{$event->name}' executed successfully.");
            } catch (Exception $e) {
                $this->error("Failed to execute event '{$event->name}': {$e->getMessage()}");
            }
        }

        if ($events->isEmpty()) {
            $this->info('No events scheduled for today.');
        }
    }
}