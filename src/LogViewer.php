<?php



namespace Ermradulsharma\LogViewer;

use Ermradulsharma\LogViewer\Contracts\Utilities\Filesystem as FilesystemContract;
use Ermradulsharma\LogViewer\Contracts\Utilities\Factory as FactoryContract;
use Ermradulsharma\LogViewer\Contracts\Utilities\LogLevels as LogLevelsContract;
use Ermradulsharma\LogViewer\Contracts\LogViewer as LogViewerContract;
use Ermradulsharma\LogViewer\Entities\Log;
use Ermradulsharma\LogViewer\Entities\LogCollection;
use Ermradulsharma\LogViewer\Entities\LogEntryCollection;
use Ermradulsharma\LogViewer\Tables\StatsTable;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Skywalker\Support\Http\Concerns\ApiResponse;

/**
 * Class     LogViewer
 *
 * @author   Mradul Sharma <skywalkerlknw@gmail.com>
 */
class LogViewer implements LogViewerContract
{
    use ApiResponse;

    /**
     * The callback that should be used to determine the user's role.
     *
     * @var \Closure|null
     */
    public static $authUsing;

    /**
     * Set the callback that should be used to determine the user's role.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public static function auth(\Closure $callback)
    {
        static::$authUsing = $callback;
    }
    /* -----------------------------------------------------------------
     |  Constants
     | -----------------------------------------------------------------
     */

    /**
     * LogViewer Version
     */
    const VERSION = '1.0.0';

    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /**
     * The factory instance.
     *
     * @var \Ermradulsharma\LogViewer\Contracts\Utilities\Factory
     */
    protected $factory;

    /**
     * The filesystem instance.
     *
     * @var \Ermradulsharma\LogViewer\Contracts\Utilities\Filesystem
     */
    protected $filesystem;

    /**
     * The log levels instance.
     *
     * @var \Ermradulsharma\LogViewer\Contracts\Utilities\LogLevels
     */
    protected $levels;

    /* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

    /**
     * Create a new instance.
     *
     * @param \Ermradulsharma\LogViewer\Contracts\Utilities\Factory $factory
     * @param \Ermradulsharma\LogViewer\Contracts\Utilities\Filesystem $filesystem
     * @param \Ermradulsharma\LogViewer\Contracts\Utilities\LogLevels $levels
     */
    public function __construct(
        FactoryContract $factory,
        FilesystemContract $filesystem,
        LogLevelsContract $levels
    ) {
        $this->factory = $factory;
        $this->filesystem = $filesystem;
        $this->levels = $levels;
    }

    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Get the log levels.
     *
     * @param bool $flip
     *
     * @return array
     */
    public function levels($flip = false): array
    {
        return $this->levels->lists($flip);
    }

    /**
     * Get the translated log levels.
     *
     * @param string|null $locale
     *
     * @return array
     */
    public function levelsNames($locale = null): array
    {
        return $this->levels->names($locale);
    }

    /**
     * Set the log storage path.
     *
     * @param string $path
     *
     * @return self
     */
    public function setPath($path): self
    {
        $this->factory->setPath($path);

        return $this;
    }

    /**
     * Get the log pattern.
     *
     * @return string
     */
    public function getPattern(): string
    {
        return $this->factory->getPattern();
    }

    /**
     * Set the log pattern.
     *
     * @param string $date
     * @param string $prefix
     * @param string $extension
     *
     * @return self
     */
    public function setPattern(
        $prefix = FilesystemContract::PATTERN_PREFIX,
        $date = FilesystemContract::PATTERN_DATE,
        $extension = FilesystemContract::PATTERN_EXTENSION
    ): self {
        $this->factory->setPattern($prefix, $date, $extension);

        return $this;
    }

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get all logs.
     *
     * @return \Ermradulsharma\LogViewer\Entities\LogCollection
     */
    public function all(): LogCollection
    {
        return $this->factory->all();
    }

    /**
     * Paginate all logs.
     *
     * @param int $perPage
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = 30): LengthAwarePaginator
    {
        return $this->factory->paginate($perPage);
    }

    /**
     * Get a log.
     *
     * @param string $date
     *
     * @return \Ermradulsharma\LogViewer\Entities\Log
     */
    public function get($date): Log
    {
        return $this->factory->log($date);
    }

    /**
     * Get the log entries.
     *
     * @param string $date
     * @param string $level
     *
     * @return \Ermradulsharma\LogViewer\Entities\LogEntryCollection
     */
    public function entries($date, $level = 'all'): LogEntryCollection
    {
        return $this->factory->entries($date, $level);
    }

    /**
     * Download a log file.
     *
     * @param string $date
     * @param string|null $filename
     * @param array $headers
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($date, $filename = null, $headers = []): BinaryFileResponse
    {
        if (is_null($filename)) {
            $filename = sprintf(
                "%s{$date}.%s",
                config('log-viewer.download.prefix', 'laravel-'),
                config('log-viewer.download.extension', 'log')
            );
        }

        $path = $this->filesystem->path($date);

        return response()->download($path, $filename, $headers);
    }

    /**
     * Get logs statistics.
     *
     * @return array
     */
    public function stats(): array
    {
        return $this->factory->stats();
    }

    /**
     * Get logs statistics table.
     *
     * @param string|null $locale
     *
     * @return \Ermradulsharma\LogViewer\Tables\StatsTable
     */
    public function statsTable($locale = null): StatsTable
    {
        return $this->factory->statsTable($locale);
    }

    /**
     * Delete the log.
     *
     * @param string $date
     *
     * @return bool
     */
    public function delete($date): bool
    {
        return $this->filesystem->delete($date);
    }

    /**
     * Clear the log files.
     *
     * @return bool
     */
    public function clear(): bool
    {
        return $this->filesystem->clear();
    }

    /**
     * Get all valid log files.
     *
     * @return array
     */
    public function files(): array
    {
        return $this->filesystem->logs();
    }

    /**
     * List the log files (only dates).
     *
     * @return array
     */
    public function dates(): array
    {
        return $this->factory->dates();
    }

    /**
     * Get logs count.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->factory->count();
    }

    /**
     * Get entries total from all logs.
     *
     * @param string $level
     *
     * @return int
     */
    public function total($level = 'all'): int
    {
        return $this->factory->total($level);
    }

    /**
     * Get logs tree.
     *
     * @param bool $trans
     *
     * @return array
     */
    public function tree($trans = false): array
    {
        return $this->factory->tree($trans);
    }

    /**
     * Get logs menu.
     *
     * @param bool $trans
     *
     * @return array
     */
    public function menu($trans = true): array
    {
        return $this->factory->menu($trans);
    }

    /* -----------------------------------------------------------------
     |  Check Methods
     | -----------------------------------------------------------------
     */

    /**
     * Determine if the log folder is empty or not.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->factory->isEmpty();
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get the LogViewer version.
     *
     * @return string
     */
    public function version(): string
    {
        return self::VERSION;
    }
}
