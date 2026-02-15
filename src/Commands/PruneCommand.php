<?php

namespace Skywalker\LogViewer\Commands;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

/**
 * Class     PruneCommand
 *
 * @author   Mradul Sharma <skywalkerlknw@gmail.com>
 */
class PruneCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log-viewer:prune {--force : Force the operation to run.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune old log files based on the retention policy.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->displayLogViewer();

        if (! config('log-viewer.retention.enabled', false) && ! $this->option('force')) {
            $this->warn('Retention policy is disabled. Use --force to prune anyway.');
            return static::SUCCESS;
        }

        $this->info('Pruning old log files...');

        $logs = $this->logViewer->all();
        $prunedCount = 0;
        $defaultDays = config('log-viewer.retention.default', 30);

        foreach ($logs as $log) {
            $date = Carbon::parse($log->date);
            $daysOld = $date->diffInDays(now());

            // Simple pruning: if file is older than default retention
            if ($daysOld > $defaultDays) {
                if ($this->logViewer->delete($log->date)) {
                    $this->line("Pruned log file for date: [{$log->date}] ({$daysOld} days old)");
                    $prunedCount++;
                }
            }
        }

        if ($prunedCount === 0) {
            $this->info('No log files matched the pruning criteria.');
        } else {
            $this->info("Successfully pruned {$prunedCount} log file(s).");
        }

        return static::SUCCESS;
    }
}
