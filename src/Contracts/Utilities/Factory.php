<?php

declare(strict_types=1);

namespace Skywalker\LogViewer\Contracts\Utilities;

use Skywalker\LogViewer\Contracts\Patternable;
use Skywalker\LogViewer\Contracts\Utilities\{Filesystem, LogLevels};

/**
 * Interface  Factory
 *
 * @author    Mradul Sharma <skywalkerlknw@gmail.com>
 */
interface Factory extends Patternable
{
    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Get the filesystem instance.
     *
     * @return \Skywalker\LogViewer\Contracts\Utilities\Filesystem
     */
    public function getFilesystem();

    /**
     * Set the filesystem instance.
     *
     * @param  \Skywalker\LogViewer\Contracts\Utilities\Filesystem  $filesystem
     *
     * @return self
     */
    public function setFilesystem(Filesystem $filesystem);

    /**
     * Get the log levels instance.
     *
     * @return  \Skywalker\LogViewer\Contracts\Utilities\LogLevels  $levels
     */
    public function getLevels();

    /**
     * Set the log levels instance.
     *
     * @param  \Skywalker\LogViewer\Contracts\Utilities\LogLevels  $levels
     *
     * @return self
     */
    public function setLevels(LogLevels $levels);

    /**
     * Set the log storage path.
     *
     * @param  string  $storagePath
     *
     * @return self
     */
    public function setPath($storagePath);

    /**
     * Get all logs.
     *
     * @return \Skywalker\LogViewer\Entities\LogCollection
     */
    public function logs();

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get all logs (alias).
     *
     * @see logs
     *
     * @return \Skywalker\LogViewer\Entities\LogCollection
     */
    public function all();

    /**
     * Paginate all logs.
     *
     * @param  int  $perPage
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = 30);

    /**
     * Get a log by date.
     *
     * @param  string  $date
     *
     * @return \Skywalker\LogViewer\Entities\Log
     */
    public function log($date);

    /**
     * Get a log by date (alias).
     *
     * @param  string  $date
     *
     * @return \Skywalker\LogViewer\Entities\Log
     */
    public function get($date);

    /**
     * Get log entries.
     *
     * @param  string  $date
     * @param  string  $level
     *
     * @return \Skywalker\LogViewer\Entities\LogEntryCollection
     */
    public function entries($date, $level = 'all');

    /**
     * List the log files (dates).
     *
     * @return array
     */
    public function dates();

    /**
     * Get logs count.
     *
     * @return int
     */
    public function count();

    /**
     * Get total log entries.
     *
     * @param  string  $level
     *
     * @return int
     */
    public function total($level = 'all');

    /**
     * Get tree menu.
     *
     * @param  bool  $trans
     *
     * @return array
     */
    public function tree($trans = false);

    /**
     * Get tree menu.
     *
     * @param  bool  $trans
     *
     * @return array
     */
    public function menu($trans = true);

    /**
     * Get logs statistics.
     *
     * @return array
     */
    public function stats();

    /**
     * Get logs statistics table.
     *
     * @param  string|null  $locale
     *
     * @return \Skywalker\LogViewer\Tables\StatsTable
     */
    public function statsTable($locale = null);

    /* -----------------------------------------------------------------
     |  Check Methods
     | -----------------------------------------------------------------
     */

    /**
     * Determine if the log folder is empty or not.
     *
     * @return bool
     */
    public function isEmpty();
}
