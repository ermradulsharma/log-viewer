<?php



namespace Ermradulsharma\LogViewer\Utilities;

use Ermradulsharma\LogViewer\Contracts\Utilities\Filesystem as FilesystemContract;
use Ermradulsharma\LogViewer\Exceptions\FilesystemException;
use Ermradulsharma\LogViewer\Helpers\LogParser;
use Exception;
use Illuminate\Filesystem\Filesystem as IlluminateFilesystem;

/**
 * Class     Filesystem
 *
 * @author   Mradul Sharma <skywalkerlknw@gmail.com>
 */
class Filesystem implements FilesystemContract
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected IlluminateFilesystem $filesystem;

    /**
     * The base storage path.
     *
     * @var string
     */
    protected string $storagePath;

    /**
     * The log files prefix pattern.
     *
     * @var string
     */
    protected string $prefixPattern;

    /**
     * The log files date pattern.
     *
     * @var string
     */
    protected string $datePattern;

    /**
     * The log files extension.
     *
     * @var string
     */
    protected string $extension;

    /* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

    /**
     * Filesystem constructor.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  string                             $storagePath
     */
    public function __construct(IlluminateFilesystem $files, $storagePath)
    {
        $this->filesystem  = $files;
        $this->setPath($storagePath);
        $this->setPattern();
    }

    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Get the files instance.
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    public function getInstance()
    {
        return $this->filesystem;
    }

    /**
     * Set the log storage path.
     *
     * @param  string  $storagePath
     *
     * @return $this
     */
    public function setPath($storagePath)
    {
        $this->storagePath = $storagePath;

        return $this;
    }

    /**
     * Get the log pattern.
     *
     * @return string
     */
    public function getPattern(): string
    {
        return $this->prefixPattern . $this->datePattern . $this->extension;
    }

    /**
     * Set the log pattern.
     *
     * @param  string  $date
     * @param  string  $prefix
     * @param  string  $extension
     *
     * @return $this
     */
    public function setPattern(
        $prefix    = self::PATTERN_PREFIX,
        $date      = self::PATTERN_DATE,
        $extension = self::PATTERN_EXTENSION
    ) {
        $this->setPrefixPattern($prefix);
        $this->setDatePattern($date);
        $this->setExtension($extension);

        return $this;
    }

    /**
     * Set the log date pattern.
     *
     * @param  string  $datePattern
     *
     * @return $this
     */
    public function setDatePattern($datePattern)
    {
        $this->datePattern = $datePattern;

        return $this;
    }

    /**
     * Set the log prefix pattern.
     *
     * @param  string  $prefixPattern
     *
     * @return $this
     */
    public function setPrefixPattern($prefixPattern)
    {
        $this->prefixPattern = $prefixPattern;

        return $this;
    }

    /**
     * Set the log extension.
     *
     * @param  string  $extension
     *
     * @return $this
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get all log files.
     *
     * @return array
     */
    public function all()
    {
        return $this->getFiles('*' . $this->extension);
    }

    public function logs()
    {
        return $this->getFiles($this->getPattern());
    }

    /**
     * List the log files (Only dates).
     *
     * @param  bool  $withPaths
     *
     * @return array
     */
    public function dates($withPaths = false)
    {
        $files = array_reverse($this->logs());
        $dateMap = [];

        foreach ($files as $file) {
            $dateFromFilename = LogParser::extractDate(basename($file));

            // Check if filename matches the date pattern (Standard Log)
            if (preg_match('/' . LogParser::REGEX_DATE_PATTERN . '/', $dateFromFilename)) {
                $dateMap[$dateFromFilename] = $file;
                continue;
            }

            // If filename doesn't have a date (e.g. laravel.log), scan content
            try {
                // Read the first 2MB to find dates. 
                // Reading whole file might be too heavy for huge logs, but limiting might miss dates.
                // For now, let's look for dates in the whole file as requested.

                // Using generic file_get_contents via filesystem adapter if possible, 
                // but we have absolute path $file here.
                $content = file_get_contents($file);

                if ($content) {
                    preg_match_all('/\[' . LogParser::REGEX_DATE_PATTERN . '/', $content, $matches);
                    if (!empty($matches[0])) {
                        $dates = array_unique($matches[0]);
                        $baseName = pathinfo($file, PATHINFO_FILENAME);
                        foreach ($dates as $dateStr) {
                            // remove brackets '['
                            $cleanDate = substr($dateStr, 1);
                            // Create unique key: "2026-02-06 (laravel)"
                            $key = $cleanDate . ' (' . $baseName . ')';
                            $dateMap[$key] = $file;
                        }
                    }
                }
            } catch (Exception $e) {
                // Squelch errors reading file
            }
        }

        // Sort dates descending
        krsort($dateMap);

        if ($withPaths) {
            return $dateMap;
        }

        return array_keys($dateMap);
    }

    /**
     * Read the log.
     *
     * @param  string  $date
     *
     * @return string
     *
     * @throws \Ermradulsharma\LogViewer\Exceptions\FilesystemException
     */
    public function read($date)
    {
        try {
            $log = $this->filesystem->get(
                $this->getLogPath($date)
            );
        } catch (Exception $e) {
            throw new FilesystemException($e->getMessage());
        }

        return $log;
    }

    /**
     * Delete the log.
     *
     * @param  string  $date
     *
     * @return bool
     *
     * @throws \Ermradulsharma\LogViewer\Exceptions\FilesystemException
     */
    public function delete(string $date)
    {
        $path = $this->getLogPath($date);

        throw_unless($this->filesystem->delete($path), FilesystemException::cannotDeleteLog());

        return true;
    }

    /**
     * Clear the log files.
     *
     * @return bool
     */
    public function clear()
    {
        return $this->filesystem->delete($this->logs());
    }

    /**
     * Get the log file path.
     *
     * @param  string  $date
     *
     * @return string
     */
    public function path($date)
    {
        return $this->getLogPath($date);
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get all files.
     *
     * @param  string  $pattern
     *
     * @return array
     */
    private function getFiles($pattern)
    {
        $files = $this->filesystem->glob(
            $this->storagePath . DIRECTORY_SEPARATOR . $pattern,
            defined('GLOB_BRACE') ? GLOB_BRACE : 0
        );

        return array_filter(array_map('realpath', $files));
    }

    /**
     * Get the log file path.
     *
     * @param  string  $date
     *
     * @return string
     *
     * @throws \Ermradulsharma\LogViewer\Exceptions\FilesystemException
     */
    private function getLogPath(string $date)
    {
        if (preg_match('/(.+) \((.+)\)$/', $date, $matches)) {
            $date     = $matches[1];
            $filename = $matches[2];
            $path     = $this->storagePath . DIRECTORY_SEPARATOR . $filename . $this->extension;

            if ($this->filesystem->exists($path)) {
                return realpath($path);
            }
        }

        $path = $this->storagePath . DIRECTORY_SEPARATOR . $this->prefixPattern . $date . $this->extension;

        if ($this->filesystem->exists($path)) {
            return realpath($path);
        }

        // Try to check if date is the filename
        $path = $this->storagePath . DIRECTORY_SEPARATOR . $date;

        if ($this->filesystem->exists($path)) {
            return realpath($path);
        }

        throw FilesystemException::invalidPath($path);
    }

    /**
     * Extract dates from files.
     *
     * @param  array  $files
     *
     * @return array
     */
    private function extractDates(array $files)
    {
        return array_map(function ($file) {
            return LogParser::extractDate(basename($file));
        }, $files);
    }
}
