<?php



namespace Ermradulsharma\LogViewer\Helpers;

use Ermradulsharma\LogViewer\Utilities\LogLevels;
use Illuminate\Support\Str;

/**
 * Class     LogParser
 *
 * @author   Mradul Sharma <skywalkerlknw@gmail.com>
 */
class LogParser
{
    /* -----------------------------------------------------------------
     |  Constants
     | -----------------------------------------------------------------
     */

    const REGEX_DATE_PATTERN     = '\d{4}(-\d{2}){2}';
    const REGEX_TIME_PATTERN     = '\d{2}(:\d{2}){2}';
    const REGEX_DATETIME_PATTERN = self::REGEX_DATE_PATTERN . ' ' . self::REGEX_TIME_PATTERN;

    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /**
     * Parsed data.
     *
     * @var array
     */
    protected static $parsed = [];

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Parse file content.
     *
     * @param  string  $raw
     * @param  string  $channel
     *
     * @return array
     */
    public static function parse($raw, $channel = 'laravel')
    {
        static::$parsed = [];
        $pattern = config("log-viewer.channels.{$channel}.pattern");

        if (! $pattern) {
            // Fallback to basic Laravel pattern if channel not found
            $pattern = '/^\[(?P<datetime>.*?)\] (?P<env>\w+)\.(?P<level>\w+): (?P<header>.*)/m';
        }

        // Split by lines and try to match
        $lines = explode("\n", $raw);
        $currentEntry = null;

        foreach ($lines as $line) {
            if (empty(trim($line))) continue;

            if (preg_match($pattern, $line, $matches)) {
                if ($currentEntry) {
                    static::$parsed[] = $currentEntry;
                }

                $currentEntry = [
                    'datetime' => $matches['datetime'] ?? '',
                    'level'    => strtolower($matches['level'] ?? 'info'),
                    'env'      => $matches['env'] ?? 'local',
                    'header'   => $matches['header'] ?? '',
                    'ip'       => $matches['ip'] ?? null,
                    'cid'      => $matches['cid'] ?? null,
                    'stack'    => '',
                ];
            } elseif ($currentEntry) {
                // It's a stack trace or continuation
                $currentEntry['stack'] .= $line . "\n";
            }
        }

        if ($currentEntry) {
            static::$parsed[] = $currentEntry;
        }

        return array_reverse(static::$parsed);
    }

    /**
     * Extract the date from a string.
     *
     * @param  string  $string
     *
     * @return string
     */
    public static function extractDate(string $string): string
    {
        return preg_replace('/.*(' . self::REGEX_DATE_PATTERN . ').*/', '$1', $string);
    }

    /**
     * Check if header has a log level.
     *
     * @param  string  $heading
     * @param  string  $level
     *
     * @return bool
     */
    private static function hasLogLevel($heading, $level)
    {
        return Str::contains(Str::lower($heading), strtolower(".{$level}:"));
    }
}
