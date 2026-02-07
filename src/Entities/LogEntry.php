<?php



namespace Ermradulsharma\LogViewer\Entities;

use Ermradulsharma\LogViewer\Helpers\LogParser;
use Carbon\Carbon;
use Illuminate\Contracts\Support\{Arrayable, Jsonable};
use JsonSerializable;

/**
 * Class     LogEntry
 *
 * @author   Mradul Sharma <skywalkerlknw@gmail.com>
 */
class LogEntry implements Arrayable, Jsonable, JsonSerializable
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var string */
    public $env;

    /** @var string */
    public $level;

    /** @var \Carbon\Carbon */
    public $datetime;

    /** @var string|null */
    public $ip;

    /** @var string|null */
    public $correlationId;

    /** @var string */
    public $header;

    /** @var string */
    public $stack;

    /** @var array */
    public $context = [];

    /* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

    /**
     * Construct the log entry instance.
     *
     * @param  string       $level
     * @param  string       $header
     * @param  string|null  $stack
     * @param  array        $data
     */
    public function __construct($level, $header, $stack = null, array $data = [])
    {
        $this->setLevel($level);
        $this->setHeader($header);
        $this->setStack($stack);

        $this->env = $data['env'] ?? 'local';
        $this->ip = $data['ip'] ?? $this->extractIp();
        $this->correlationId = $data['correlationId'] ?? $data['cid'] ?? $this->extractCorrelationId();

        if (isset($data['datetime'])) {
            try {
                $this->datetime = Carbon::parse($data['datetime']);
            } catch (\Exception $e) {
                // Keep default datetime from setHeader
            }
        }
    }

    /**
     * Extract IP address from header or context.
     * 
     * @return string|null
     */
    protected function extractIp()
    {
        $pattern = '/\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/';

        if (preg_match($pattern, $this->header, $matches)) {
            return $matches[0];
        }

        if (isset($this->context['ip'])) {
            return $this->context['ip'];
        }

        return null;
    }

    /**
     * Extract Correlation ID from header or context.
     * 
     * @return string|null
     */
    protected function extractCorrelationId()
    {
        // Common patterns for CID: [req-abc...], CID: abc..., trace_id=abc...
        $patterns = [
            '/(?:req|trace|cid|request)[_-]id[:=\s]+([a-zA-Z0-9_-]{8,})/i',
            '/\[(req-[a-zA-Z0-9_-]{8,})\]/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $this->header, $matches)) {
                return $matches[1];
            }
        }

        // Check context for common keys
        $cidKeys = ['request_id', 'correlation_id', 'trace_id', 'cid'];
        foreach ($cidKeys as $key) {
            if (isset($this->context[$key])) {
                return $this->context[$key];
            }
        }

        return null;
    }

    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Set the entry level.
     *
     * @param  string  $level
     *
     * @return self
     */
    private function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Set the entry header.
     *
     * @param  string  $header
     *
     * @return self
     */
    private function setHeader($header)
    {
        $this->setDatetime($this->extractDatetime($header));

        $header = $this->cleanHeader($header);

        $this->header = trim($header);

        return $this;
    }

    /**
     * Set the context.
     *
     * @param  array  $context
     *
     * @return $this
     */
    private function setContext(array $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Set entry environment.
     *
     * @param  string  $env
     *
     * @return self
     */
    private function setEnv($env)
    {
        $this->env = head(explode('.', $env));

        return $this;
    }

    /**
     * Set the entry date time.
     *
     * @param  string  $datetime
     *
     * @return \Ermradulsharma\LogViewer\Entities\LogEntry
     */
    private function setDatetime($datetime)
    {
        try {
            $this->datetime = Carbon::parse($datetime);
        } catch (\Exception $e) {
            $this->datetime = now();
        }

        return $this;
    }

    /**
     * Set the entry stack.
     *
     * @param  string  $stack
     *
     * @return self
     */
    private function setStack($stack)
    {
        $this->stack = $stack;

        return $this;
    }

    /**
     * Get translated level name with icon.
     *
     * @return string
     */
    public function level()
    {
        return $this->icon()->toHtml() . ' ' . $this->name();
    }

    /**
     * Get the masked header.
     *
     * @return string
     */
    public function header()
    {
        return $this->applyMasking($this->header);
    }

    /**
     * Get translated level name.
     *
     * @return string
     */
    public function name()
    {
        return \log_levels()->get($this->level);
    }

    /**
     * Get level icon.
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function icon()
    {
        return \log_styler()->icon($this->level);
    }

    /**
     * Get the entry stack.
     *
     * @return string
     */
    public function stack()
    {
        $stack = $this->applyMasking(trim(htmlentities($this->stack)));

        if (! $ide = config('log-viewer.ide')) {
            return $stack;
        }

        $schemes = [
            'vscode'   => 'vscode://file/%f:%l',
            'phpstorm' => 'phpstorm://open?file=%f&line=%l',
            'sublime'  => 'subl://open?url=file://%f&line=%l',
            'atom'     => 'atom://core/open/file?filename=%f&line=%l',
        ];

        $scheme = $schemes[$ide] ?? $ide; // Allow custom schemes if someone manually sets it

        // Pattern to match file paths with line numbers (e.g. /app/User.php(32))
        // This is a naive pattern but covers standard PHP stack traces.
        return preg_replace_callback('/(#\d+\s+)?(\/[a-zA-Z0-9_\-\.\/]+)\((\d+)\)/', function ($matches) use ($scheme) {
            $fullMatch = $matches[0];
            $prefix    = $matches[1]; // "#0 "
            $file      = $matches[2]; // "/path/to/file.php"
            $line      = $matches[3]; // "123"

            $url = str_replace(['%f', '%l'], [$file, $line], $scheme);

            return $prefix . '<a href="' . $url . '" class="hover:underline hover:text-primary-600" title="Open in IDE">' . $file . ' (' . $line . ')</a>';
        }, $stack);
    }

    /**
     * Get the entry context as json pretty print.
     */
    public function context(int $options = JSON_PRETTY_PRINT): string
    {
        return $this->applyMasking(json_encode($this->context, $options));
    }

    /**
     * Apply masking to the content.
     *
     * @param  string  $content
     *
     * @return string
     */
    protected function applyMasking($content)
    {
        if (! config('log-viewer.masking.enabled', false)) {
            return $content;
        }

        foreach (config('log-viewer.masking.patterns', []) as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }

        return $content;
    }

    /* -----------------------------------------------------------------
     |  Check Methods
     | -----------------------------------------------------------------
     */

    /**
     * Check if same log level.
     *
     * @param  string  $level
     *
     * @return bool
     */
    public function isSameLevel($level)
    {
        return $this->level === $level;
    }

    /* -----------------------------------------------------------------
     |  Convert Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get the log entry as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'level'    => $this->level,
            'datetime' => $this->datetime->format('Y-m-d H:i:s'),
            'header'   => $this->header,
            'stack'    => $this->stack
        ];
    }

    /**
     * Convert the log entry to its JSON representation.
     *
     * @param  int  $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Serialize the log entry object to json data.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /* -----------------------------------------------------------------
     |  Check Methods
     | -----------------------------------------------------------------
     */

    /**
     * Check if the entry has a stack.
     *
     * @return bool
     */
    public function hasStack()
    {
        return $this->stack !== "\n";
    }

    /**
     * Check if the entry has a context.
     *
     * @return bool
     */
    public function hasContext()
    {
        return ! empty($this->context);
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Clean the entry header.
     *
     * @param  string  $header
     *
     * @return string
     */
    private function cleanHeader($header)
    {
        // REMOVE THE DATE
        $header = preg_replace('/\[' . LogParser::REGEX_DATETIME_PATTERN . '\][ ]/', '', $header);

        // EXTRACT ENV
        if (preg_match('/^[a-z]+.[A-Z]+:/', $header, $out)) {
            $this->setEnv($out[0]);
            $header = trim(str_replace($out[0], '', $header));
        }

        // EXTRACT CONTEXT (Regex from https://stackoverflow.com/a/21995025)
        preg_match_all('/{(?:[^{}]|(?R))*}/x', $header, $out);
        if (isset($out[0][0]) && ! is_null($context = json_decode($out[0][0], true))) {
            $header = str_replace($out[0][0], '', $header);
            $this->setContext($context);
        }

        return $header;
    }

    /**
     * Extract datetime from the header.
     *
     * @param  string  $header
     *
     * @return string
     */
    private function extractDatetime($header)
    {
        return preg_replace('/^\[(' . LogParser::REGEX_DATETIME_PATTERN . ')\].*/', '$1', $header);
    }
}
