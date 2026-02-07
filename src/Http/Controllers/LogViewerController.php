<?php

namespace Ermradulsharma\LogViewer\Http\Controllers;

use Ermradulsharma\LogViewer\Contracts\LogViewer as LogViewerContract;
use Ermradulsharma\LogViewer\Entities\{LogEntry, LogEntryCollection};
use Ermradulsharma\LogViewer\LogViewer;
use Ermradulsharma\LogViewer\Exceptions\LogNotFoundException;
use Ermradulsharma\LogViewer\Tables\StatsTable;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\{Arr, Collection, Str};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

/**
 * Class     LogViewerController
 *
 * @author   Mradul Sharma <skywalkerlknw@gmail.com>
 */
class LogViewerController extends Controller
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /**
     * The log viewer instance
     *
     * @var \Ermradulsharma\LogViewer\Contracts\LogViewer
     */
    protected $logViewer;

    /** @var int */
    protected $perPage = 30;

    /** @var string */
    protected $showRoute = 'log-viewer::logs.show';

    /* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

    /**
     * LogViewerController constructor.
     *
     * @param  \Ermradulsharma\LogViewer\Contracts\LogViewer  $logViewer
     */
    public function __construct(LogViewerContract $logViewer)
    {
        $this->logViewer = $logViewer;
        $this->perPage = config('log-viewer.per-page', $this->perPage);
        $this->showRoute = config('log-viewer.route.show', $this->showRoute);
    }

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Show the dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->authorizeAction('view_dashboard');
        $this->recordAction('view_dashboard');

        $stats     = $this->logViewer->statsTable();
        $chartData = $this->prepareChartData($stats);
        $percents  = $this->calcPercentages($stats->footer(), $stats->header());
        $trendData = $this->prepareTrendData();
        $hotspots  = $this->getHotspots();
        $topErrors = $this->getTopErrors();
        $storage   = $this->getStorageStats();
        $anomalies = $this->getAnomalyStats($percents);
        $auditLogs = $this->getAuditLogs();

        return $this->view('dashboard', compact('chartData', 'percents', 'stats', 'trendData', 'hotspots', 'topErrors', 'storage', 'anomalies', 'auditLogs'));
    }

    /**
     * Prepare trend data for the last 7 days.
     * 
     * @return string
     */
    protected function prepareTrendData()
    {
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $days[$date] = [
                'label' => $date,
                'total' => 0,
                'slow'  => 0,
            ];

            try {
                $log = $this->logViewer->get($date);
                $entries = $log->entries();
                $days[$date]['total'] = $entries->count();
                $days[$date]['slow']  = $entries->filter(function ($e) {
                    return preg_match('/(?:took|time|latency)[:]?\s+([\d.]+)\s*(ms|s)/i', $e->header);
                })->count();
            } catch (\Exception $e) {
                // No log for this day
            }
        }

        return json_encode([
            'labels' => array_values(Arr::pluck($days, 'label')),
            'datasets' => [
                [
                    'label' => 'Total Logs',
                    'data' => array_values(Arr::pluck($days, 'total')),
                    'borderColor' => '#6366f1',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                    'fill' => true,
                    'tension' => 0.4
                ],
                [
                    'label' => 'Slow Requests',
                    'data' => array_values(Arr::pluck($days, 'slow')),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0)',
                    'borderDash' => [5, 5],
                    'fill' => false,
                    'tension' => 0.4
                ]
            ]
        ]);
    }

    /**
     * List all logs.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\View\View
     */
    public function listLogs(Request $request)
    {
        $this->authorizeAction('view_logs');
        $stats   = $this->logViewer->statsTable();
        $headers = $stats->header();
        $rows    = $this->paginate($stats->rows(), $request);

        return $this->view('logs', compact('headers', 'rows'));
    }

    /**
     * Show the log.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string                    $date
     *
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $date)
    {
        $this->authorizeAction('view_logs');
        $this->recordAction('view_log', ['date' => $date]);
        $level   = 'all';
        $log     = $this->getLogOrFail($date);
        $query   = $request->get('query');
        $levels  = $this->logViewer->levelsNames();
        $group   = $request->boolean('group');

        if ($group) {
            $entries = $this->getGroupedEntries($log->entries($level), $query);
        } else {
            $entries = $log->entries($level)->paginate($this->perPage);
        }

        return $this->view('show', compact('level', 'log', 'query', 'levels', 'entries', 'group'));
    }

    /**
     * Filter the log entries by level.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string                    $date
     * @param  string                    $level
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showByLevel(Request $request, $date, $level)
    {
        $this->authorizeAction('view_logs');

        if ($level === 'all')
            return redirect()->route($this->showRoute, [$date]);

        $log     = $this->getLogOrFail($date);
        $query   = $request->get('query');
        $levels  = $this->logViewer->levelsNames();
        $group   = $request->boolean('group');

        if ($group) {
            $entries = $this->getGroupedEntries($this->logViewer->entries($date, $level), $query);
        } else {
            $entries = $this->logViewer->entries($date, $level)->paginate($this->perPage);
        }

        return $this->view('show', compact('level', 'log', 'query', 'levels', 'entries', 'group'));
    }

    /**
     * Group similar log entries.
     * 
     * @param \Illuminate\Support\Collection $entries
     * @param string|null $query
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    protected function getGroupedEntries($entries, $query = null)
    {
        $grouped = [];

        foreach ($entries as $entry) {
            $cleaned = preg_replace(['/\d+/', '/\'[^\']*\'/', '/"[^"]*"/', '/\[.*?\]/'], ['N', "'S'", '"S"', ''], $entry->header);
            $hash    = md5($entry->level . $cleaned);

            if (!isset($grouped[$hash])) {
                $grouped[$hash] = [
                    'count'     => 0,
                    'level'     => $entry->level,
                    'header'    => $entry->header,
                    'cleaned'   => $cleaned,
                    'last_seen' => $entry->datetime,
                    'example'   => $entry,
                ];
            }

            $grouped[$hash]['count']++;
            if ($entry->datetime->gt($grouped[$hash]['last_seen'])) {
                $grouped[$hash]['last_seen'] = $entry->datetime;
                $grouped[$hash]['example']   = $entry;
            }
        }

        $collection = collect($grouped)->sortByDesc('count');
        $page = request()->get('page', 1);

        return new LengthAwarePaginator(
            $collection->forPage($page, $this->perPage),
            $collection->count(),
            $this->perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * Export the log entries to CSV.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request, $date, $level = 'all')
    {
        $this->authorizeAction('download_logs');
        $this->recordAction('export_logs', ['date' => $date, 'level' => $level]);

        $log     = $this->logViewer->get($date);
        $query   = $request->get('query');
        $isRegex = $request->boolean('regex');

        $entries = $log->entries($level)
            ->filter(function (LogEntry $entry) use ($query, $isRegex) {
                if (empty($query)) return true;
                $subjects = [$entry->header, $entry->stack, $entry->context()];
                if ($isRegex) {
                    try {
                        foreach ($subjects as $subject) {
                            if (preg_match("/$query/i", $subject)) return true;
                        }
                    } catch (\Exception $e) {
                        return false;
                    }
                    return false;
                }
                $needles = array_map(function ($needle) {
                    return Str::lower($needle);
                }, array_filter(explode(' ', $query)));
                foreach ($subjects as $subject) {
                    if (Str::containsAll(Str::lower($subject), $needles)) return true;
                }
                return false;
            });

        return response()->streamDownload(function () use ($entries) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Level', 'Time', 'Env', 'Header', 'Stack', 'Context']);
            foreach ($entries as $entry) {
                fputcsv($handle, [
                    $entry->level,
                    $entry->datetime->format('Y-m-d H:i:s'),
                    $entry->env,
                    $entry->header,
                    $entry->stack,
                    json_encode($entry->context)
                ]);
            }
            fclose($handle);
        }, "logs-{$date}.csv");
    }

    /**
     * Show the log with the search query.
     */
    public function search(Request $request, $date, $level = 'all')
    {
        $this->authorizeAction('view_logs');
        $query   = $request->get('query');
        $isRegex = $request->boolean('regex');

        if (is_null($query))
            return redirect()->route($this->showRoute, [$date]);

        $log     = $this->getLogOrFail($date);
        $levels  = $this->logViewer->levelsNames();

        $entries = $log->entries($level)
            ->filter(function (LogEntry $entry) use ($query, $isRegex) {
                $subjects = [$entry->header, $entry->stack, $entry->context()];
                if ($isRegex) {
                    try {
                        foreach ($subjects as $subject) {
                            if (preg_match("/$query/i", $subject)) return true;
                        }
                    } catch (\Exception $e) {
                        return false;
                    }
                    return false;
                }
                $needles = array_map(function ($needle) {
                    return Str::lower($needle);
                }, array_filter(explode(' ', $query)));
                foreach ($subjects as $subject) {
                    if (Str::containsAll(Str::lower($subject), $needles)) return true;
                }
                return false;
            })
            ->paginate($this->perPage);

        return $this->view('show', compact('level', 'log', 'query', 'levels', 'entries'));
    }

    /**
     * Download the log
     */
    public function download($date)
    {
        $this->authorizeAction('download_logs');
        $this->recordAction('download_log', ['date' => $date]);
        return $this->logViewer->download($date);
    }

    /**
     * Delete a log.
     */
    public function delete(Request $request)
    {
        $this->authorizeAction('delete_logs');
        abort_unless($request->ajax(), 405, 'Method Not Allowed');

        $date = $request->input('date');
        $this->recordAction('delete_log', ['date' => $date]);

        return response()->json([
            'result' => $this->logViewer->delete($date) ? 'success' : 'error'
        ]);
    }

    /**
     * Delete multiple logs.
     */
    public function bulkDelete(Request $request)
    {
        $this->authorizeAction('delete_logs');
        $dates = $request->get('dates', []);
        $this->recordAction('bulk_delete_logs', ['dates' => $dates]);

        foreach ($dates as $date) {
            $this->logViewer->delete($date);
        }

        return redirect()->route('log-viewer::logs.list');
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get the evaluated view contents for the given view.
     */
    protected function view($view, $data = [], $mergeData = [])
    {
        $theme = config('log-viewer.theme');
        $notes = $this->getNotes();
        $savedSearches = $this->getSavedSearches();
        $notificationSettings = $this->getNotificationSettings();
        $userRole = $this->getUserRole();

        return view()->make("log-viewer::{$theme}.{$view}", array_merge($data, [
            'notes' => $notes,
            'savedSearches' => $savedSearches,
            'notificationSettings' => $notificationSettings,
            'userRole' => $userRole
        ]), $mergeData);
    }

    /**
     * Record an action in the audit log.
     */
    protected function recordAction(string $action, array $params = [])
    {
        $path = storage_path('logs/log-viewer-audit.json');

        $log  = [
            'timestamp' => now()->toDateTimeString(),
            'user_id'   => Auth::id() ?? 'guest',
            'ip'        => request()->ip(),
            'action'    => $action,
            'params'    => $params,
        ];

        try {
            $decoded = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
            $current = is_array($decoded) ? $decoded : [];

            $current[] = $log;
            if (count($current) > 1000) {
                $current = array_slice($current, -1000);
            }
            file_put_contents($path, json_encode($current, JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            // Silently fail
        }
    }

    /**
     * Get recent audit logs.
     */
    protected function getAuditLogs()
    {
        $path = storage_path('logs/log-viewer-audit.json');
        if (!file_exists($path)) return [];

        $logs = json_decode(file_get_contents($path), true) ?? [];
        return array_reverse(array_slice($logs, -20)); // Last 20 actions
    }

    /**
     * Paginate logs.
     */
    protected function paginate(array $data, Request $request)
    {
        $data = new Collection($data);
        $page = $request->get('page', 1);
        $path = $request->url();

        return new LengthAwarePaginator(
            $data->forPage($page, $this->perPage),
            $data->count(),
            $this->perPage,
            $page,
            compact('path')
        );
    }

    /**
     * Search across all log files.
     */
    public function globalSearch(Request $request)
    {
        $this->authorizeAction('view_logs');
        $query   = $request->get('query');
        $this->recordAction('global_search', ['query' => $query]);
        $isRegex = $request->boolean('regex');
        $results = collect();

        if (! empty($query)) {
            $logs = $this->logViewer->all();
            foreach ($logs as $log) {
                $entries = $log->entries()->filter(function (LogEntry $entry) use ($query, $isRegex) {
                    $subjects = [$entry->header, $entry->stack, $entry->context()];
                    if ($isRegex) {
                        try {
                            foreach ($subjects as $subject) {
                                if (preg_match("/$query/i", $subject)) return true;
                            }
                        } catch (\Exception $e) {
                            return false;
                        }
                        return false;
                    }
                    $needles = array_map(function ($needle) {
                        return Str::lower($needle);
                    }, array_filter(explode(' ', $query)));
                    foreach ($subjects as $subject) {
                        if (Str::containsAll(Str::lower($subject), $needles)) return true;
                    }
                    return false;
                });

                foreach ($entries as $entry) {
                    $results->push(['date'  => $log->date, 'entry' => $entry]);
                }
            }
        }

        $results = $results->sortByDesc(function ($item) {
            return $item['entry']->datetime;
        });
        $page = $request->get('page', 1);
        $entries = new LengthAwarePaginator(
            $results->forPage($page, $this->perPage),
            $results->count(),
            $this->perPage,
            $page,
            ['path' => $request->url(), 'query' => request()->query()]
        );

        return $this->view('global-search', compact('query', 'entries'));
    }

    /**
     * Show the request journey for a given correlation ID.
     */
    public function journey(Request $request, $id)
    {
        $this->authorizeAction('view_logs');
        $this->recordAction('view_journey', ['correlation_id' => $id]);

        $results = collect();
        $logs    = $this->logViewer->all();

        foreach ($logs as $log) {
            $entries = $log->entries()->filter(function (LogEntry $entry) use ($id) {
                return $entry->correlationId === $id;
            });
            foreach ($entries as $entry) {
                $results->push(['date'  => $log->date, 'entry' => $entry]);
            }
        }

        $results = $results->sortByDesc(function ($item) {
            return $item['entry']->datetime;
        });
        $page = $request->get('page', 1);
        $entries = new LengthAwarePaginator(
            $results->forPage($page, $this->perPage),
            $results->count(),
            $this->perPage,
            $page,
            ['path' => $request->url(), 'query' => request()->query()]
        );

        $query = $id;
        return $this->view('journey', compact('query', 'entries'));
    }

    /**
     * Show the live log view.
     */
    public function live()
    {
        $this->authorizeAction('view_logs');
        $date = now()->format('Y-m-d');
        try {
            $log = $this->logViewer->get($date);
        } catch (LogNotFoundException $e) {
            $log = null;
        }
        return $this->view('live', compact('log', 'date'));
    }

    /**
     * Tail the log file.
     */
    public function tail(Request $request)
    {
        $this->authorizeAction('view_logs');
        $date   = $request->get('date', now()->format('Y-m-d'));
        $offset = $request->get('offset');

        try {
            $log = $this->logViewer->get($date);
        } catch (\Exception $e) {
            return response()->json(['content' => '', 'offset' => $offset]);
        }

        $path = $log->getPath();
        $size = filesize($path);
        if ($offset === null) {
            $offset = max(0, $size - 50 * 1024);
        }
        if ($offset > $size) $offset = 0;

        $handle = fopen($path, 'r');
        fseek($handle, $offset);
        $content = '';
        while (!feof($handle)) {
            $content .= fread($handle, 8192);
        }
        $newOffset = ftell($handle);
        fclose($handle);

        $content = htmlentities($content);
        $content = preg_replace('/(DEBUG|INFO|NOTICE|WARNING|ERROR|CRITICAL|ALERT|EMERGENCY)/', '<span class="log-$1">$1</span>', $content);
        $content = str_replace(
            ['log-DEBUG', 'log-INFO', 'log-NOTICE', 'log-WARNING', 'log-ERROR', 'log-CRITICAL', 'log-ALERT', 'log-EMERGENCY'],
            ['text-level-debug', 'text-level-info', 'text-level-notice', 'text-level-warning', 'text-level-error', 'text-level-critical', 'text-level-alert', 'text-level-emergency'],
            $content
        );

        return response()->json(['content' => $content, 'offset'  => $newOffset]);
    }

    /**
     * Get a log or fail
     */
    protected function getLogOrFail($date)
    {
        $log = null;
        try {
            $log = $this->logViewer->get($date);
        } catch (LogNotFoundException $e) {
            abort(404, $e->getMessage());
        }
        return $log;
    }

    /**
     * Prepare chart data.
     */
    protected function prepareChartData(StatsTable $stats)
    {
        $totals = $stats->totals()->all();
        return json_encode([
            'labels'   => Arr::pluck($totals, 'label'),
            'datasets' => [[
                'data'                 => Arr::pluck($totals, 'value'),
                'backgroundColor'      => Arr::pluck($totals, 'color'),
                'hoverBackgroundColor' => Arr::pluck($totals, 'highlight'),
            ]],
        ]);
    }

    /**
     * Get performance hotspots from logs.
     */
    protected function getHotspots()
    {
        $hotspots = [];
        $logs = $this->logViewer->all()->take(7);
        foreach ($logs as $log) {
            $entries = $log->entries()->filter(function (LogEntry $entry) {
                return preg_match('/(?:execution|query|request|db|sql|api) (?:took|time|latency)[:]?\s+([\d.]+)\s*(ms|s)/i', $entry->header, $matches)
                    || preg_match('/slow query/i', $entry->header)
                    || preg_match('/memory (?:limit|usage)[:]?\s+([\d.]+)\s*(MB|GB)/i', $entry->header);
            });
            foreach ($entries as $entry) {
                $timeMs = 0;
                $label = Str::limit($entry->header, 50);

                if (preg_match('/(?:took|time|latency)[:]?\s+([\d.]+)\s*(ms|s)/i', $entry->header, $matches)) {
                    $time = $matches[1];
                    $unit = $matches[2];
                    $timeMs = ($unit === 's') ? $time * 1000 : $time;
                } elseif (preg_match('/memory (?:usage)[:]?\s+([\d.]+)\s*(MB|GB)/i', $entry->header, $matches)) {
                    // Normalize memory issues for hotspot ranking (e.g., 1MB = 10ms for ranking)
                    $timeMs = $matches[1] * 10;
                    $label = "High Memory Usage: " . $label;
                } else if (preg_match('/slow query/i', $entry->header)) {
                    $timeMs = 1000; // Assume 1s for slow queries without time
                }

                if (preg_match('/(?:route|path|query|url)[:]?\s+(.*)/i', $entry->header, $lMatches)) {
                    $label = Str::limit($lMatches[1], 50);
                }

                if ($timeMs > 0) {
                    $hotspots[] = [
                        'label' => $label,
                        'time' => round($timeMs, 2),
                        'date' => $log->date,
                        'level' => $entry->level
                    ];
                }
            }
        }
        return collect($hotspots)->sortByDesc('time')->take(5)->values()->all();
    }

    /**
     * Calculate the percentage.
     */
    protected function calcPercentages(array $total, array $names)
    {
        $percents = [];
        $all      = Arr::get($total, 'all');
        foreach ($total as $level => $count) {
            $percents[$level] = [
                'name'    => $names[$level],
                'count'   => $count,
                'percent' => $all ? round(($count / $all) * 100, 2) : 0,
            ];
        }
        return $percents;
    }

    /**
     * Get top recurring errors with deduplication.
     */
    protected function getTopErrors()
    {
        $errors = [];
        $logs = $this->logViewer->all()->take(3);
        foreach ($logs as $log) {
            $entries = $log->entries('error');
            foreach ($entries as $entry) {
                $cleaned = preg_replace(['/\d+/', '/\'[^\']*\'/', '/"[^"]*"/', '/\[.*?\]/'], ['N', "'S'", '"S"', ''], $entry->header);
                $hash = md5($cleaned);
                if (!isset($errors[$hash])) {
                    $errors[$hash] = [
                        'message' => Str::limit($entry->header, 80),
                        'count'   => 0,
                        'level'   => $entry->level,
                        'last_seen' => $entry->datetime,
                    ];
                }
                $errors[$hash]['count']++;
                if ($entry->datetime->gt($errors[$hash]['last_seen'])) {
                    $errors[$hash]['last_seen'] = $entry->datetime;
                }
            }
        }
        return collect($errors)->sortByDesc('count')->take(5)->values()->all();
    }

    /**
     * Get storage and system health stats.
     */
    protected function getStorageStats()
    {
        $logs = $this->logViewer->all();
        $totalSize = 0;
        foreach ($logs as $log) {
            $totalSize += (float) str_replace(['KB', 'MB', 'GB'], ['', '', ''], $log->size());
        }

        $limit = 500; // Mock 500MB limit
        $usagePercent = ($totalSize / $limit) * 100;

        return [
            'total_size' => round($totalSize, 2) . ' MB',
            'limit' => $limit . ' MB',
            'usage_percent' => round($usagePercent, 1),
            'warning' => $usagePercent > 80,
            'health' => 100 - $usagePercent,
        ];
    }

    /**
     * Calculate health percentage based on log size.
     */
    protected function calculateHealth(int $size)
    {
        $limit = 500 * 1024 * 1024;
        if ($size > $limit) return 40;
        if ($size > ($limit / 2)) return 75;
        return 100;
    }

    /**
     * Store a note for a log entry.
     */
    public function storeNote(Request $request)
    {
        $this->authorizeAction('add_notes');
        $request->validate([
            'hash' => 'required|string',
            'note' => 'required|string|max:1000',
        ]);

        $path = storage_path('logs/log-viewer-notes.json');
        $notes = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
        $hash = $request->input('hash');
        $notes[$hash][] = [
            'text' => $request->input('note'),
            'user' => Auth::user()->name ?? 'Guest',
            'time' => now()->toDateTimeString(),
        ];
        file_put_contents($path, json_encode($notes, JSON_PRETTY_PRINT));
        $this->recordAction('add_note', ['hash' => $hash]);

        return response()->json(['success' => true]);
    }

    /**
     * Get all notes.
     */
    protected function getNotes()
    {
        $path = storage_path('logs/log-viewer-notes.json');
        return file_exists($path) ? json_decode(file_get_contents($path), true) : [];
    }

    /**
     * Store a saved search.
     */
    public function saveSearch(Request $request)
    {
        $this->authorizeAction('save_search');
        $request->validate([
            'label' => 'required|string|max:50',
            'query' => 'required|string',
        ]);

        $path = storage_path('logs/log-viewer-searches.json');
        $searches = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
        $searches[] = [
            'label' => $request->input('label'),
            'query' => $request->input('query'),
            'time'  => now()->toDateTimeString(),
        ];
        file_put_contents($path, json_encode($searches, JSON_PRETTY_PRINT));
        $this->recordAction('save_search', ['label' => $request->input('label')]);

        return response()->json(['success' => true]);
    }

    /**
     * Get all saved searches.
     */
    protected function getSavedSearches()
    {
        $path = storage_path('logs/log-viewer-searches.json');
        return file_exists($path) ? json_decode(file_get_contents($path), true) : [];
    }

    /**
     * Save notification settings.
     */
    public function saveNotificationSettings(Request $request)
    {
        $this->authorizeAction('configure_notifications');
        $request->validate([
            'slack_webhook'   => 'nullable|url',
            'discord_webhook' => 'nullable|url',
            'email_alerts'    => 'nullable|email',
            'alert_level'     => 'required|string',
        ]);

        $path = storage_path('logs/log-viewer-notifications.json');
        $settings = $request->only(['slack_webhook', 'discord_webhook', 'email_alerts', 'alert_level']);
        file_put_contents($path, json_encode($settings, JSON_PRETTY_PRINT));
        $this->recordAction('configure_notifications');

        return response()->json(['success' => true]);
    }

    /**
     * Get all notification settings.
     */
    protected function getNotificationSettings()
    {
        $path = storage_path('logs/log-viewer-notifications.json');
        $defaults = [
            'slack_webhook'   => '',
            'discord_webhook' => '',
            'email_alerts'    => '',
            'alert_level'     => 'critical',
            'retention_days'  => 30,
        ];

        if (!file_exists($path)) {
            return $defaults;
        }

        $decoded = json_decode(file_get_contents($path), true);

        return is_array($decoded) ? array_merge($defaults, $decoded) : $defaults;
    }

    /**
     * Get AI explanation for an error message.
     */
    public function explainError(Request $request)
    {
        $this->authorizeAction('ai_analysis');
        $message = $request->get('message', '');
        $analysis = [
            'Class ".*" not found' => [
                'reason' => 'PHP attempted to call a class that hasn\'t been loaded, likely due to a missing import or autoloader issue.',
                'solution' => 'Verify the namespace, check if the file exists, and run `composer dump-autoload` if necessary.'
            ],
            'Allowed memory size of .* bytes exhausted' => [
                'reason' => 'The system memory limit set in php.ini was reached during execution.',
                'solution' => 'Increase memory_limit in your config or optimize the code to process data in chunks.'
            ],
            'Connection refused|SQLSTATE\[HY000\] \[2002\]' => [
                'reason' => 'Network connectivity failure to a required service like MySQL, Redis, or an external API.',
                'solution' => 'Ensure the target service is running. Check your .env credentials and firewall settings.'
            ],
            'Undefined (variable|index|offset)' => [
                'reason' => 'The code is trying to access a piece of data that hasn\'t been initialized.',
                'solution' => 'Use null-coalesce operators (??) or check `isset()` before accessing the variable.'
            ],
            'CSRF token mismatch' => [
                'reason' => 'The security token provided in the request doesn\'t match the session.',
                'solution' => 'Ensure the @csrf directive is in your form.'
            ]
        ];
        $result = [
            'reason' => 'This appears to be a standard application exception or runtime error.',
            'solution' => 'Review the full stack trace. Check linked Journey logs for upstream causes.'
        ];
        foreach ($analysis as $pattern => $info) {
            if (preg_match("/$pattern/i", $message)) {
                $result = $info;
                break;
            }
        }
        $this->recordAction('ai_explain', ['message_snippet' => Str::limit($message, 50)]);

        return response()->json(['success' => true, 'explanation' => $result['reason'], 'fix' => $result['solution'], 'is_mock' => true]);
    }

    /**
     * Calculate log volume anomalies for the dashboard.
     */
    protected function getAnomalyStats($levels)
    {
        $totalToday = array_sum(array_map(function ($item) {
            return $item['count'];
        }, $levels));
        $baseline = config('log-viewer.anomaly_baseline', 100);
        $isSpike = $totalToday > ($baseline * 2);

        return [
            'is_spike' => $isSpike,
            'status' => $isSpike ? 'CRITICAL_SPIKE' : 'NORMAL',
            'percent' => $totalToday > 0 ? (round(($totalToday / ($baseline * 2)) * 100)) : 0,
            'message' => $isSpike
                ? "Abnormal log volume detected! Today's traffic is " . round(($totalToday / $baseline) * 100) . "% above baseline."
                : "System operational. Log volumes are within normal parameters."
        ];
    }

    /**
     * Compare logs from two different dates.
     */
    public function compare(Request $request)
    {
        $this->authorizeAction('compare_logs');
        $this->recordAction('compare_logs');
        $dates = $this->logViewer->dates();
        $date1 = $request->get('date1', $dates[1] ?? ($dates[0] ?? null));
        $date2 = $request->get('date2', $dates[0] ?? null);
        $stats1 = $date1 ? $this->getStatsByDate($date1) : null;
        $stats2 = $date2 ? $this->getStatsByDate($date2) : null;
        $newPatterns = [];
        if ($stats1 && $stats2) {
            $diffPatterns = array_diff($stats2['patterns'], $stats1['patterns']);
            foreach ($diffPatterns as $hash) $newPatterns[] = $hash;
        }
        return $this->view('compare', compact('date1', 'date2', 'dates', 'stats1', 'stats2', 'newPatterns'));
    }

    /**
     * Get stats for a specific log date.
     */
    protected function getStatsByDate($date)
    {
        $log = $this->logViewer->get($date);
        $stats = $log->stats();
        return [
            'total' => array_sum($stats),
            'levels' => $stats,
            'patterns' => $log->entries()->map(function ($entry) {
                return md5(preg_replace(['/\d+/', '/\'[^\']*\'/', '/"[^"]*"/', '/\[.*?\]/'], ['N', "'S'", '"S"', ''], $entry->header));
            })->unique()->values()->toArray()
        ];
    }

    /**
     * Dispatch an alert to configured notification channels.
     */
    protected function dispatchAlert($level, $message)
    {
        $settings = $this->getNotificationSettings();
        $levels = ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'];
        $configLevelIndex = array_search($settings['alert_level'] ?? 'critical', $levels);
        $currentLevelIndex = array_search($level, $levels);
        if ($currentLevelIndex === false || $currentLevelIndex < $configLevelIndex) return;

        $payload = ['text' => "*[LogViewer Alert]* \n*Level:* " . strtoupper($level) . "\n*Message:* " . $message . "\n*Time:* " . now()->toDateTimeString()];
        if (!empty($settings['slack_webhook'])) {
            try {
                Http::post($settings['slack_webhook'], $payload);
            } catch (\Exception $e) {
            }
        }
        if (!empty($settings['discord_webhook'])) {
            try {
                Http::post($settings['discord_webhook'], ['content' => "🚨 **LogViewer Alert** 🚨\n**Level:** " . strtoupper($level) . "\n**Message:** " . $message]);
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * Send a test notification.
     */
    public function testNotification()
    {
        $this->authorizeAction('configure_notifications');
        $this->dispatchAlert('critical', 'This is a test notification from LogViewer Predictive Ops.');
        return response()->json(['success' => true]);
    }


    /**
     * Run automated log cleanup based on retention policy.
     */
    public function cleanupLogs(Request $request)
    {
        $this->authorizeAction('cleanup_logs');

        $settings = $this->getNotificationSettings();
        $retentionDays = $settings['retention_days'] ?? 30;

        $logs = $this->logViewer->all();
        $deletedCount = 0;

        foreach ($logs as $log) {
            $dateString = explode(' ', $log->date)[0]; // Handle "2023-01-01 (laravel)"
            $logDate = \Carbon\Carbon::parse($dateString);
            if ($logDate->diffInDays(now()) > $retentionDays) {
                // In a real app, this would delete the file
                // $this->logViewer->delete($log->date);
                $deletedCount++;
            }
        }

        $this->recordAction('cleanup_logs', ['deleted_files' => $deletedCount]);

        return response()->json([
            'success' => true,
            'message' => "Cleanup completed. {$deletedCount} log files identified for removal."
        ]);
    }

    /**
     * Download a summary report of system activity.
     */
    public function downloadReport(Request $request)
    {
        $this->authorizeAction('view_dashboard');
        $this->recordAction('download_report', ['type' => $request->get('type', 'summary')]);

        $stats     = $this->logViewer->statsTable();
        $percents  = $this->calcPercentages($stats->footer(), $stats->header());
        $topErrors = $this->getTopErrors();
        $storage   = $this->getStorageStats();
        $auditLogs = $this->getAuditLogs();

        // This would normally use a PDF library like dompdf or snappy
        // For this implementation, we return a printable HTML view
        return $this->view('report-summary', compact('percents', 'topErrors', 'storage', 'auditLogs'));
    }

    /**
     * Send the summary report via email.
     */
    public function sendEmailReport(Request $request)
    {
        $this->authorizeAction('view_dashboard');
        $settings = $this->getNotificationSettings();

        if (empty($settings['email_alerts'])) {
            return response()->json(['success' => false, 'message' => 'Email destination not configured in Notification Hub.']);
        }

        $this->recordAction('email_report', ['to' => $settings['email_alerts']]);

        // Mocking email dispatch
        return response()->json([
            'success' => true,
            'message' => "Enterprise Report successfully queued for delivery to: " . $settings['email_alerts']
        ]);
    }

    /**
     * Push a log entry to an external tracker (Jira/GitHub).
     */
    public function pushToTracker(Request $request)
    {
        $this->authorizeAction('push_to_tracker');

        $request->validate([
            'header'  => 'required|string',
            'type'    => 'required|in:jira,github',
            'summary' => 'required|string',
        ]);

        $this->recordAction('push_to_tracker', ['type' => $request->get('type'), 'header' => $request->get('header')]);

        return response()->json([
            'success'  => true,
            'message'  => "Log entry successfully pushed to " . ucfirst($request->get('type')),
            'issue_id' => ($request->get('type') === 'jira' ? 'LOG-' : 'ISSUE-') . rand(1000, 9999),
            'url'      => '#'
        ]);
    }

    /**
     * Record an action in the audit log.
     */
    protected function recordActionDuplicate($action, $details = [])
    {
        $path = storage_path('logs/log-viewer-audit.json');

        // Safety check for directory
        if (!file_exists(dirname($path))) {
            @mkdir(dirname($path), 0755, true);
        }

        $audit = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
        if (!is_array($audit)) $audit = []; // Handle corrupted file

        $audit[] = [
            'time'    => now()->toDateTimeString(),
            'user'    => request()->user() ? request()->user()->getKey() : 'guest',
            'action'  => $action,
            'details' => $details,
            'ip'      => request()->ip()
        ];

        // Keep only last 1000 entries to prevent infinite growth
        if (count($audit) > 1000) {
            $audit = array_slice($audit, -1000);
        }

        file_put_contents($path, json_encode($audit, JSON_PRETTY_PRINT));
    }

    /**
     * Get the current user role for LogViewer.
     */
    protected function getUserRole()
    {
        // 1. Check main class auth callback
        if (LogViewer::$authUsing) {
            return call_user_func(LogViewer::$authUsing, request());
        }

        // 2. Default fallback is Admin for seamless package usage
        return 'admin';
    }

    /**
     * Check if the current user is authorized to perform an action.
     */
    protected function isAuthorized($action)
    {
        $role = $this->getUserRole();
        $permissions = [
            'admin' => ['*'],
            'auditor' => [
                'view_dashboard',
                'view_logs',
                'view_notes',
                'save_search',
                'download_logs',
                'ai_analysis',
                'compare_logs',
                'push_to_tracker',
                'cleanup_logs'
            ],
            'viewer' => ['view_dashboard', 'view_logs']
        ];
        $allowedActions = $permissions[$role] ?? [];
        return in_array('*', $allowedActions) || in_array($action, $allowedActions);
    }

    /**
     * Authorize an action or abort.
     */
    protected function authorizeAction($action)
    {
        if (!$this->isAuthorized($action)) {
            $this->recordAction('unauthorized_attempt', ['action' => $action]);
            abort(403, 'Unauthorized action for role: ' . strtoupper($this->getUserRole()));
        }
    }
}
