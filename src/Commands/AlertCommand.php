<?php

namespace Skywalker\LogViewer\Commands;

use Illuminate\Support\Carbon;

/**
 * Class     AlertCommand
 *
 * @author   Mradul Sharma <skywalkerlknw@gmail.com>
 */
class AlertCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log-viewer:alert {--minutes=5 : The number of minutes to check back.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for high-severity log entries and trigger alerts.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->displayLogViewer();

        $minutes = (int) $this->option('minutes');
        $this->info("Checking for high-severity logs in the last {$minutes} minutes...");

        $date    = now()->format('Y-m-d');
        try {
            $log = $this->logViewer->get($date);
        } catch (\Exception $e) {
            $this->warn("No log file found for today ({$date}).");
            return static::SUCCESS;
        }

        $threshold = now()->subMinutes($minutes);
        $severities = ['emergency', 'alert', 'critical'];
        $foundCount = 0;
        $allFoundEntries = [];

        foreach ($severities as $level) {
            $entries = $log->entries($level)->filter(function ($entry) use ($threshold) {
                return $entry->datetime->gt($threshold);
            });

            if ($entries->isNotEmpty()) {
                $this->error("Found " . $entries->count() . " " . strtoupper($level) . " entries!");
                foreach ($entries as $entry) {
                    $this->line("  [{$entry->datetime}] - {$entry->header}");
                    $allFoundEntries[] = $entry;
                }
                $foundCount += $entries->count();
            }
        }

        if ($foundCount === 0) {
            $this->info("No high-severity logs found. Everything looks good.");
        } else {
            $this->warn("Total high-severity entries found: {$foundCount}");
            $this->notifyWebhooks($allFoundEntries ?? []);
        }

        return static::SUCCESS;
    }

    /**
     * Notify webhooks about the found entries.
     * 
     * @param array $entries
     */
    protected function notifyWebhooks(array $entries)
    {
        if (! config('log-viewer.webhooks.enabled') || ! $url = config('log-viewer.webhooks.url')) {
            return;
        }

        if (empty($entries)) return;

        $message = "*LogViewer Alert!* Found " . count($entries) . " high-severity entries in the last " . $this->option('minutes') . " minutes.\n";
        foreach (array_slice($entries, 0, 10) as $entry) {
            $message .= "> [{$entry->datetime}] *" . strtoupper($entry->level) . "*: {$entry->header}\n";
        }

        if (count($entries) > 10) {
            $message .= "... and " . (count($entries) - 10) . " more.";
        }

        try {
            \Illuminate\Support\Facades\Http::post($url, [
                'text' => $message, // Slack
                'content' => $message, // Discord
            ]);
            $this->info("Webhook notification sent successfully.");
        } catch (\Exception $e) {
            $this->error("Failed to send webhook notification: " . $e->getMessage());
        }
    }
}
