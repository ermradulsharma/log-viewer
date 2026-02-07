<?php



namespace Ermradulsharma\LogViewer\Http\Routes;

use Ermradulsharma\LogViewer\Http\Controllers\LogViewerController;
use Skywalker\Support\Routing\RouteRegistrar;

/**
 * Class     LogViewerRoute
 *
 * @author   Mradul Sharma <skywalkerlknw@gmail.com>
 */
class LogViewerRoute extends RouteRegistrar
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Map all routes.
     */
    public function map(): void
    {
        $attributes = (array) config('log-viewer.route.attributes');

        $this->group($attributes, function () {
            $this->name('log-viewer::')->group(function () {
                $this->get('/', [LogViewerController::class, 'index'])->name('dashboard'); // log-viewer::dashboard
                $this->get('search', [LogViewerController::class, 'globalSearch'])->name('global-search'); // log-viewer::global-search
                $this->get('journey/{id}', [LogViewerController::class, 'journey'])->name('journey'); // log-viewer::journey
                $this->post('notes', [LogViewerController::class, 'storeNote'])->name('notes.store'); // log-viewer::notes.store
                $this->post('searches', [LogViewerController::class, 'saveSearch'])->name('searches.save'); // log-viewer::searches.save
                $this->post('notifications', [LogViewerController::class, 'saveNotificationSettings'])->name('notifications.save'); // log-viewer::notifications.save
                $this->get('ai-explain', [LogViewerController::class, 'explainError'])->name('ai-explain'); // log-viewer::ai-explain
                $this->post('push-to-tracker', [LogViewerController::class, 'pushToTracker'])->name('push-to-tracker'); // log-viewer::push-to-tracker
                $this->post('cleanup-logs', [LogViewerController::class, 'cleanupLogs'])->name('cleanup-logs'); // log-viewer::cleanup-logs
                $this->get('reports/download', [LogViewerController::class, 'downloadReport'])->name('reports.download'); // log-viewer::reports.download
                $this->post('reports/email', [LogViewerController::class, 'sendEmailReport'])->name('reports.email'); // log-viewer::reports.email
                $this->get('compare', [LogViewerController::class, 'compare'])->name('compare'); // log-viewer::compare
                $this->mapLogsRoutes();
            });
        });
    }

    /**
     * Map the logs routes.
     */
    private function mapLogsRoutes(): void
    {
        $this->prefix('logs')->name('logs.')->group(function () {
            $this->get('/', [LogViewerController::class, 'listLogs'])->name('list'); // log-viewer::logs.list
            $this->delete('delete', [LogViewerController::class, 'delete'])->name('delete'); // log-viewer::logs.delete
            $this->post('bulk-delete', [LogViewerController::class, 'bulkDelete'])->name('bulk-delete'); // log-viewer::logs.bulk-delete
            $this->get('live', [LogViewerController::class, 'live'])->name('live'); // log-viewer::logs.live
            $this->get('tail', [LogViewerController::class, 'tail'])->name('tail'); // log-viewer::logs.tail

            $this->prefix('{date}')->group(function () {
                $this->get('/', [LogViewerController::class, 'show'])->name('show'); // log-viewer::logs.show
                $this->get('download', [LogViewerController::class, 'download'])->name('download'); // log-viewer::logs.download
                $this->get('export', [LogViewerController::class, 'export'])->name('export'); // log-viewer::logs.export
                $this->get('{level}', [LogViewerController::class, 'showByLevel'])->name('filter'); // log-viewer::logs.filter
                $this->get('{level}/search', [LogViewerController::class, 'search'])->name('search'); // log-viewer::logs.search
            });
        });
    }
}
